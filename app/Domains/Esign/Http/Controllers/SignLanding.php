<?php

namespace App\Domains\Esign\Http\Controllers;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\DocumentSigningPolicy;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\EsignConsent;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\GuestSignerSession;
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
        abort_if($request->signable === null, 404);

        // Guest sessions route through the email challenge before anything
        // else; the signed URL got them here, the one-time code proves who
        // they are.
        if ($request->isGuest() && ! GuestSignerSession::isVerified($request)) {
            if (! $request->isCompleted()) {
                abort_if($request->status === SignatureRequestStatus::Voided, 410, 'This signing request has been voided.');
                abort_if($request->isExpired(), 410, 'This signing link has expired.');
            }

            // Arriving via the signed URL is what entitles this session to
            // trigger code emails on the verify screen.
            GuestSignerSession::markChallenged($request);

            return redirect()->route('esign.sign.verify', $request->public_id);
        }

        if (! $request->isGuest()) {
            abort_unless(auth()->id() === $request->signer_user_id, 403, 'This signing link is for a different account.');
        }

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

        $consent = $request->isGuest()
            ? $this->guestConsent($request, $policy)
            : EsignConsent::currentFor(auth()->user(), $policy->consentScope(), config('esign.consent.version'));

        return $consent === null
            ? redirect()->route('esign.sign.consent', $request->public_id)
            : redirect()->route('esign.sign.review', $request->public_id);
    }

    private function guestConsent(SignatureRequest $request, DocumentSigningPolicy $policy): ?EsignConsent
    {
        $consent = $request->consent;

        return ($consent !== null
            && $consent->consent_scope === $policy->consentScope()
            && $consent->version === config('esign.consent.version')
            && $consent->withdrawn_at === null) ? $consent : null;
    }
}
