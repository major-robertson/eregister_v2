<?php

namespace App\Domains\Esign\Contracts;

use App\Domains\Esign\Models\SignatureRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Implemented by each domain's adapter (e.g. DemandLetterSignable) to make a
 * record signable. The generic Esign actions depend ONLY on this contract and
 * the Esign models — never on a concrete domain model. Future document types
 * (liens, prelim notices, resale certs) plug in by implementing this and
 * registering a factory with the SignableResolver.
 */
interface Signable
{
    /** The morph target the signature attaches to (e.g. a LienFiling). */
    public function model(): Model;

    /** The user who must sign (null if unresolved — surfaced as an error). */
    public function signer(): ?User;

    /** Tenancy hint stored on the request (nullable for non-tenant signables). */
    public function businessId(): ?int;

    /** Config key into esign.document_types.<key> for the signing policy. */
    public function documentTypeKey(): string;

    /** Human title of the bundle, e.g. "Payment Demand Letters". */
    public function title(): string;

    /**
     * One descriptor per document to sign (one per recipient). Identifiers are
     * left null here — the send action assigns and freezes them.
     *
     * @return list<SignableDocument>
     */
    public function documents(): array;

    /** Common snapshot metadata frozen onto each document at lock time. */
    public function snapshotMeta(): array;

    /** Render the UNSIGNED (locked) PDF for a document; returns raw bytes. */
    public function renderUnsigned(SignableDocument $document): string;

    /** Render the final SIGNED PDF (signature block + certificate); raw bytes. */
    public function renderSigned(SignableDocument $document, SignatureContext $context): string;

    /** Route name/URL to send the signer to after completing. */
    public function postSignRedirectRoute(SignatureRequest $request): string;

    /**
     * Domain hook fired once all documents are signed — e.g. advance the source
     * record's status. Runs after the session is marked completed.
     */
    public function onCompleted(SignatureRequest $request): void;
}
