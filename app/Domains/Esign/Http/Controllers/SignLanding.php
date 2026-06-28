<?php

namespace App\Domains\Esign\Http\Controllers;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * The signed-URL landing for a signing session. Verifies the signer, records the
 * first open, then routes to consent (first time) or straight to the review/sign
 * screen.
 */
class SignLanding
{
    public function __construct(private readonly AppendSignatureEvent $events) {}

    public function __invoke(Request $http, SignatureRequest $request): RedirectResponse
    {
        abort_unless(auth()->id() === $request->signer_user_id, 403, 'This signing link is for a different account.');
        abort_if($request->signable === null, 404);

        if ($request->isCompleted()) {
            return redirect()->route('esign.sign.done', $request->public_id);
        }

        abort_if($request->status === SignatureRequestStatus::Voided, 410, 'This signing request has been voided.');
        abort_if($request->isExpired(), 410, 'This signing link has expired.');

        if ($request->first_opened_at === null) {
            $request->update(['first_opened_at' => Carbon::now()]);

            $this->events->execute($request, SignatureEventType::SignerOpened,
                actorType: 'signer', actorUserId: auth()->id(), ip: $http->ip(), userAgent: $http->userAgent());
        }

        $policy = DocumentSigningPolicy::for($request->document_signing_policy_key);
        $consent = EsignConsent::currentFor(auth()->user(), $policy->consentScope(), config('esign.consent.version'));

        return $consent === null
            ? redirect()->route('esign.sign.consent', $request->public_id)
            : redirect()->route('esign.sign.review', $request->public_id);
    }
}
