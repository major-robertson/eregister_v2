<?php

namespace App\Domains\Lien\Esign\Actions\Concerns;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Contracts\SignableDocument;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Domains\Esign\Support\PdfBytes;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Mail\SignerInvitation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The three steps of sending a lien filing for e-signature, shared by the
 * demand letter (SendDemandLetterForSignature) and the uploaded document
 * (SendUploadedDocumentForSignature): create the session with one row per
 * document (short transaction), render + hash + store each locked PDF
 * (idempotent, outside a transaction), then invite the signer and move the
 * filing to Awaiting E-Signature.
 *
 * The using class provides $resolver (SignableResolver) and $events
 * (AppendSignatureEvent).
 *
 * @property-read SignableResolver $resolver
 * @property-read AppendSignatureEvent $events
 */
trait SendsFilingForSignature
{
    /**
     * Create the session + one document row per descriptor (short transaction).
     *
     * @param  list<SignableDocument>  $descriptors
     * @param  array<string, mixed>  $snapshotMeta
     */
    private function createRequest(
        LienFiling $filing,
        User $signer,
        string $policyKey,
        array $descriptors,
        array $snapshotMeta,
        DocumentSigningPolicy $policy,
    ): SignatureRequest {
        return DB::transaction(function () use ($filing, $signer, $policyKey, $descriptors, $snapshotMeta, $policy): SignatureRequest {
            $request = SignatureRequest::create([
                'signable_type' => $filing->getMorphClass(),
                'signable_id' => $filing->getKey(),
                'business_id' => $filing->business_id,
                'signer_user_id' => $signer->id,
                'document_signing_policy_key' => $policyKey,
                'status' => SignatureRequestStatus::Pending,
                'signer_name_snapshot' => $signer->name,
                'signer_email_snapshot' => $signer->email,
                'signer_phone_snapshot' => $filing->project?->claimantParty()?->phone,
                // The per-type intent, falling back to the global one
                // (demand letters).
                'intent_statement' => $policy->intentStatement(),
                'signature_method' => $policy->signatureMethod(),
                'created_by_user_id' => auth()->id(),
            ]);

            foreach ($descriptors as $index => $descriptor) {
                $identifier = $policy->documentIdPrefix().'-'.(1001 + $index);

                SignatureDocument::create([
                    'signature_request_id' => $request->id,
                    'document_identifier' => $identifier,
                    'label' => $descriptor->label,
                    'recipient_ref' => $descriptor->recipientRef,
                    'document_snapshot_json' => [
                        'render' => $descriptor->renderPayload,
                        'meta' => array_merge($snapshotMeta, [
                            'document_identifier' => $identifier,
                            'created_at' => Carbon::now()->toIso8601String(),
                        ]),
                    ],
                    'sort_order' => $descriptor->sortOrder,
                ]);
            }

            $request->update(['status' => SignatureRequestStatus::LockingDocuments]);

            return $request;
        });
    }

    /**
     * Render + hash + store each locked document. Idempotent: a re-run skips
     * already-locked documents so a partial failure can resume.
     */
    private function lockDocuments(SignatureRequest $request, User $admin): void
    {
        $signable = $this->resolver->for($request->signable);
        $ip = request()?->ip();
        $ua = request()?->userAgent();

        try {
            foreach ($request->documents()->get() as $document) {
                if ($document->isLocked()) {
                    continue;
                }

                $this->events->execute($request, SignatureEventType::DocumentCreated,
                    document: $document, actorType: 'admin', actorUserId: $admin->id, ip: $ip, userAgent: $ua,
                    metadata: ['document_identifier' => $document->document_identifier, 'label' => $document->label],
                );

                $bytes = $signable->renderUnsigned(SignableDocument::fromModel($document));
                $hash = PdfBytes::sha256($bytes);
                $document->storeLocked($bytes, $hash);

                // Record the unsigned hash inside the immutable render snapshot too.
                $snapshot = $document->document_snapshot_json;
                $snapshot['meta']['unsigned_pdf_hash'] = $hash;
                $document->forceFill(['document_snapshot_json' => $snapshot])->save();

                $this->events->execute($request, SignatureEventType::DocumentLocked,
                    document: $document, actorType: 'admin', actorUserId: $admin->id, ip: $ip, userAgent: $ua,
                    metadata: ['sha256' => $hash, 'document_identifier' => $document->document_identifier],
                );
            }
        } catch (Throwable $e) {
            $request->update([
                'status' => SignatureRequestStatus::Failed,
                'failure_reason' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Invite the signer and move the filing to AwaitingEsign.
     *
     * @param  array<string, mixed>  $eventPayload  Extra facts for the esign_sent event.
     */
    private function finalize(SignatureRequest $request, LienFiling $filing, User $signer, User $admin, array $eventPayload = []): SignatureRequest
    {
        $ttlDays = (int) config('esign.signing.invitation_link_ttl_days', 14);

        $request->update([
            'status' => SignatureRequestStatus::AwaitingSignature,
            'invited_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays($ttlDays),
        ]);

        $this->events->execute($request, SignatureEventType::SignerInvited,
            actorType: 'admin', actorUserId: $admin->id, ip: request()?->ip(), userAgent: request()?->userAgent(),
            metadata: ['email' => $signer->email],
        );

        Mail::to($signer->email)->queue(new SignerInvitation($request));

        $filing->transitionTo(FilingStatus::AwaitingEsign, ['signature_request_id' => $request->public_id]);

        // Lightweight mirror for the admin activity timeline.
        $filing->events()->create([
            'business_id' => $filing->business_id,
            'event_type' => 'esign_sent',
            'payload_json' => array_merge([
                'signature_request_id' => $request->public_id,
                'documents' => $request->documents()->count(),
                'signer_email' => $signer->email,
            ], $eventPayload),
            'created_by' => $admin->id,
        ]);

        return $request->refresh();
    }
}
