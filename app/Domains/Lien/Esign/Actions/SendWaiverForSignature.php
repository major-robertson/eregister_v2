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
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\LienWaiverSignable;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use App\Mail\WaiverSignatureInvitation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * "Send for signature" on a lien waiver. Locks + hashes the waiver PDF from
 * its frozen snapshot, emails the signer a signed link, and moves the waiver
 * to AwaitingSignature. Mirrors SendDemandLetterForSignature's idempotent
 * state-machine shape (PDF renders + S3 uploads outside a wrapping txn).
 */
class SendWaiverForSignature
{
    public function __construct(
        private readonly SignableResolver $resolver,
        private readonly AppendSignatureEvent $events,
        private readonly WaiverFormResolver $forms,
    ) {}

    public function execute(LienWaiver $waiver, User $sender): SignatureRequest
    {
        $policy = DocumentSigningPolicy::for(LienWaiverSignable::DOCUMENT_TYPE);

        if (! $policy->supportsEsign()) {
            throw new EsignException('E-signing is not enabled for lien waivers.');
        }

        $form = $this->forms->resolve($waiver->state, $waiver->kind, $waiver->project?->property_class);

        if (! $form->esignAllowed) {
            throw new EsignException($form->esignDisabledReason
                ?? 'This state requires in-person execution (notary or witness), so e-signing is unavailable. Download the waiver, sign it on paper, then upload the signed copy.');
        }

        if ($waiver->status !== WaiverStatus::Generated || $waiver->render_snapshot_json === null) {
            throw new EsignException('Generate the waiver PDF before sending it for signature.');
        }

        if ($waiver->activeSignatureRequest() !== null) {
            throw new EsignException('This waiver already has an active signature request. Void it before sending again.');
        }

        $signerEmail = $waiver->direction === WaiverDirection::Provide
            ? $waiver->createdBy?->email
            : $waiver->signer_email;

        if (blank($signerEmail)) {
            throw new EsignException('Add the signer\'s email address before sending.');
        }

        $signable = $this->resolver->for($waiver);
        $request = $this->createRequest($waiver, $signable->signer(), $signerEmail, $signable->documents()[0], $signable->snapshotMeta(), $policy);

        $this->lockDocument($request, $sender);

        return $this->finalize($request, $waiver, $signerEmail, $sender);
    }

    /**
     * @param  array<string, mixed>  $snapshotMeta
     */
    private function createRequest(
        LienWaiver $waiver,
        ?User $signer,
        string $signerEmail,
        SignableDocument $descriptor,
        array $snapshotMeta,
        DocumentSigningPolicy $policy,
    ): SignatureRequest {
        return DB::transaction(function () use ($waiver, $signer, $signerEmail, $descriptor, $snapshotMeta, $policy): SignatureRequest {
            $request = SignatureRequest::create([
                'signable_type' => $waiver->getMorphClass(),
                'signable_id' => $waiver->getKey(),
                'business_id' => $waiver->business_id,
                // Null => guest signer (collect direction): identity is the
                // invited email, proven by one-time code.
                'signer_user_id' => $signer?->id,
                'document_signing_policy_key' => $policy->key,
                'status' => SignatureRequestStatus::Pending,
                'signer_name_snapshot' => $signer?->name ?? $waiver->signer_name ?? $waiver->counterparty_name,
                'signer_email_snapshot' => $signerEmail,
                // The counterparty's phone only identifies the signer on
                // collect waivers; provide waivers are signed by our own user.
                'signer_phone_snapshot' => $waiver->direction === WaiverDirection::Collect
                    ? $waiver->counterparty_phone
                    : null,
                'intent_statement' => $policy->intentStatement(),
                'signature_method' => $policy->signatureMethod(),
                'created_by_user_id' => auth()->id(),
            ]);

            $identifier = $policy->documentIdPrefix().'-1001';

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
                'sort_order' => 0,
            ]);

            $request->update(['status' => SignatureRequestStatus::LockingDocuments]);

            return $request;
        });
    }

    private function lockDocument(SignatureRequest $request, User $sender): void
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
                    document: $document, actorType: 'sender', actorUserId: $sender->id, ip: $ip, userAgent: $ua,
                    metadata: ['document_identifier' => $document->document_identifier, 'label' => $document->label],
                );

                $bytes = $signable->renderUnsigned(SignableDocument::fromModel($document));
                $hash = PdfBytes::sha256($bytes);
                $document->storeLocked($bytes, $hash);

                $snapshot = $document->document_snapshot_json;
                $snapshot['meta']['unsigned_pdf_hash'] = $hash;
                $document->forceFill(['document_snapshot_json' => $snapshot])->save();

                $this->events->execute($request, SignatureEventType::DocumentLocked,
                    document: $document, actorType: 'sender', actorUserId: $sender->id, ip: $ip, userAgent: $ua,
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

    private function finalize(SignatureRequest $request, LienWaiver $waiver, string $signerEmail, User $sender): SignatureRequest
    {
        $ttlDays = (int) config('esign.signing.invitation_link_ttl_days', 14);

        $request->update([
            'status' => SignatureRequestStatus::AwaitingSignature,
            'invited_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addDays($ttlDays),
        ]);

        $this->events->execute($request, SignatureEventType::SignerInvited,
            actorType: 'sender', actorUserId: $sender->id, ip: request()?->ip(), userAgent: request()?->userAgent(),
            metadata: ['email' => $signerEmail],
        );

        Mail::to($signerEmail)->queue(new WaiverSignatureInvitation($request, $waiver));

        // A fresh invitation starts a fresh reminder cycle: drop the dedup
        // rows from any earlier (voided) request so this signer still gets
        // the 3/7/12-day nudges.
        $waiver->notificationLogs()
            ->withoutGlobalScope('business')
            ->where('type', 'signature_reminder')
            ->delete();

        $waiver->update([
            'status' => WaiverStatus::AwaitingSignature,
            'sent_at' => Carbon::now(),
        ]);

        return $request->refresh();
    }
}
