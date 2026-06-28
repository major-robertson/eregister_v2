<?php

namespace App\Domains\Lien\Esign\Actions;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Contracts\SignableDocument;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Domains\Esign\Support\PdfBytes;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Esign\DemandLetterSignable;
use App\Domains\Lien\Models\LienFiling;
use App\Mail\SignerInvitation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Admin "Send for E-Sign". Generates + locks + hashes each recipient letter,
 * emails the signer a link, and moves the filing to AwaitingEsign.
 *
 * Slow PDF renders + S3 uploads are deliberately NOT wrapped in one DB
 * transaction (long-held locks + partial-state risk). Instead the request walks
 * a status state machine and the per-letter lock loop is idempotent (skips
 * already-locked letters), so a failure can be retried by re-running execute().
 */
class SendDemandLetterForSignature
{
    public function __construct(
        private readonly SignableResolver $resolver,
        private readonly AppendSignatureEvent $events,
    ) {}

    public function execute(LienFiling $filing, User $admin): SignatureRequest
    {
        $policy = DocumentSigningPolicy::for(DemandLetterSignable::DOCUMENT_TYPE);

        if (! $filing->isDemandLetter()) {
            throw new EsignException('This filing is not a demand letter.');
        }

        if (! $policy->supportsEsign()) {
            throw new EsignException('E-signing is not enabled for this document type.');
        }

        if ($policy->requiresNotary()) {
            throw new EsignException('This document requires notarization, which is not supported yet.');
        }

        if ($filing->activeSignatureRequest() !== null) {
            throw new EsignException('This filing already has an active signature request. Void it before sending again.');
        }

        if (! $filing->canTransitionTo(FilingStatus::AwaitingEsign)) {
            throw new EsignException("A demand letter can't be sent for e-sign from the “{$filing->status->label()}” status.");
        }

        $signable = $this->resolver->for($filing);

        $signer = $signable->signer();
        if ($signer === null) {
            throw new EsignException('This filing has no creator on record to sign it. Assign a creator first.');
        }

        $descriptors = $signable->documents();
        if ($descriptors === []) {
            throw new EsignException('This filing has no recipient parties to address a demand letter to.');
        }

        $request = $this->createRequest($filing, $signer, $signable->documentTypeKey(), $descriptors, $signable->snapshotMeta(), $policy);

        $this->lockDocuments($request, $admin);

        return $this->finalize($request, $filing, $signer, $admin);
    }

    /**
     * Create the session + one document row per recipient (short transaction).
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
                'intent_statement' => config('esign.signing.intent'),
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
     * Render + hash + store each locked letter. Idempotent: a re-run skips
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
     * Invite the signer and move the filing to AwaitingEsign (short transaction).
     */
    private function finalize(SignatureRequest $request, LienFiling $filing, User $signer, User $admin): SignatureRequest
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
            'payload_json' => [
                'signature_request_id' => $request->public_id,
                'documents' => $request->documents()->count(),
                'signer_email' => $signer->email,
            ],
            'created_by' => $admin->id,
        ]);

        return $request->refresh();
    }
}
