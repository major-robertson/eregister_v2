<?php

namespace App\Domains\Lien\Esign\Actions;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Domains\Lien\Documents\PdfSignatureStamper;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Esign\Actions\Concerns\SendsFilingForSignature;
use App\Domains\Lien\Esign\UploadedDocumentSignable;
use App\Domains\Lien\Models\LienFiling;
use App\Models\User;

/**
 * Admin "Send for E-Sign" for a filing that is not a demand letter (EREG-141):
 * the newest PDF in the filing's drafts is frozen (media id, version, file
 * name, SHA-256), locked as it is, and the filing creator is emailed a signing
 * link; the filing moves to Awaiting E-Signature. Same steps as the demand
 * letter (SendsFilingForSignature). Refused when e-sign is not enabled for
 * the document and state (UploadedDocumentSignable::isAllowedFor) and when
 * the PDF can't be signed as it is (PdfSignatureStamper::pageCount), before
 * anything is stored.
 */
class SendUploadedDocumentForSignature
{
    use SendsFilingForSignature;

    public function __construct(
        private readonly SignableResolver $resolver,
        private readonly AppendSignatureEvent $events,
        private readonly PdfSignatureStamper $stamper,
    ) {}

    public function execute(LienFiling $filing, User $admin): SignatureRequest
    {
        $policy = DocumentSigningPolicy::for(UploadedDocumentSignable::DOCUMENT_TYPE);

        if ($filing->isDemandLetter()) {
            throw new EsignException('This filing is a demand letter. Send it with the demand letter parties.');
        }

        if ($filing->trashed()) {
            throw new EsignException('This filing has been deleted.');
        }

        if (! UploadedDocumentSignable::isAllowedFor($filing)) {
            throw new EsignException(UploadedDocumentSignable::notAllowedReason($filing) ?? 'E-sign is not enabled for this document.');
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
            throw new EsignException("This filing can't be sent for e-sign from the “{$filing->status->label()}” status.");
        }

        $signable = $this->resolver->for($filing);

        $signer = $signable->signer();
        if ($signer === null) {
            throw new EsignException('This filing has no creator on record to sign it. Assign a creator first.');
        }

        $descriptors = $signable->documents();
        if ($descriptors === []) {
            throw new EsignException('Upload the document as a draft first. The newest draft is the one sent to sign.');
        }

        // Check the PDF can be signed as it is before anything is stored, so
        // a format FPDI can't carry over is the admin's message now, not the
        // customer's error after they sign.
        $this->stamper->pageCount($signable->renderUnsigned($descriptors[0]));

        $request = $this->createRequest($filing, $signer, $signable->documentTypeKey(), $descriptors, $signable->snapshotMeta(), $policy);

        $this->lockDocuments($request, $admin);

        $draft = $descriptors[0]->renderPayload;

        return $this->finalize($request, $filing, $signer, $admin, [
            'media_id' => $draft['media_id'],
            'version' => $draft['version'],
            'file_name' => $draft['file_name'],
        ]);
    }
}
