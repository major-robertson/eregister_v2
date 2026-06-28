<?php

namespace App\Domains\Esign\Livewire\Concerns;

use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\Esign\Models\SignatureRequest;

/**
 * Shared guards for the signer-facing Livewire pages: the logged-in user must be
 * the designated signer, and the session must still be live and its source
 * record present.
 */
trait GuardsSigningSession
{
    protected function guardSigner(SignatureRequest $request): void
    {
        abort_unless(auth()->id() === $request->signer_user_id, 403, 'This signing link is for a different account.');
        abort_if($request->signable === null, 404);

        // A completed session stays viewable (download) even past the link TTL.
        if ($request->isCompleted()) {
            return;
        }

        abort_if($request->status === SignatureRequestStatus::Voided, 410, 'This signing request has been voided.');
        abort_if($request->isExpired(), 410, 'This signing link has expired.');
    }

    protected function currentConsent(SignatureRequest $request): ?EsignConsent
    {
        $policy = DocumentSigningPolicy::for($request->document_signing_policy_key);

        return EsignConsent::currentFor(
            auth()->user(),
            $policy->consentScope(),
            config('esign.consent.version'),
        );
    }
}
