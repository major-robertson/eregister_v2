<?php

namespace App\Domains\Lien\Esign\Actions;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Contracts\SignableDocument;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Esign\Actions\Concerns\SendsFilingForSignature;
use App\Domains\Lien\Esign\DemandLetterSignable;
use App\Domains\Lien\Models\LienFiling;
use App\Models\User;

/**
 * Admin "Send for E-Sign". Generates + locks + hashes each recipient letter,
 * emails the signer a link, and moves the filing to AwaitingEsign.
 *
 * Slow PDF renders + S3 uploads are deliberately NOT wrapped in one DB
 * transaction (long-held locks + partial-state risk). Instead the request walks
 * a status state machine and the per-letter lock loop is idempotent (skips
 * already-locked letters), so a failure can be retried by re-running execute().
 * The steps are shared with SendUploadedDocumentForSignature
 * (SendsFilingForSignature).
 */
class SendDemandLetterForSignature
{
    use SendsFilingForSignature;

    public function __construct(
        private readonly SignableResolver $resolver,
        private readonly AppendSignatureEvent $events,
    ) {}

    /**
     * @param  list<int>|null  $partyIds  Recipient parties to address a letter to; null sends to all of them.
     */
    public function execute(LienFiling $filing, User $admin, ?array $partyIds = null): SignatureRequest
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

        if ($partyIds !== null) {
            $picked = array_map('strval', $partyIds);
            $descriptors = array_values(array_filter(
                $descriptors,
                fn (SignableDocument $descriptor): bool => in_array($descriptor->recipientRef, $picked, true),
            ));

            if ($descriptors === []) {
                throw new EsignException('Pick at least one party to send a demand letter to.');
            }
        }

        $request = $this->createRequest($filing, $signer, $signable->documentTypeKey(), $descriptors, $signable->snapshotMeta(), $policy);

        $this->lockDocuments($request, $admin);

        return $this->finalize($request, $filing, $signer, $admin);
    }
}
