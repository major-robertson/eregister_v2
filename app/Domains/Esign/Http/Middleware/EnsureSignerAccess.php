<?php

namespace App\Domains\Esign\Http\Middleware;

use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\GuestSignerSession;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mode-aware gate for the signer-facing esign routes.
 *
 * Account requests (signer_user_id set, demand letters) require login + a
 * verified email, exactly as before. Guest requests (lien waiver
 * counterparties) instead require a one-time-code email verification: until
 * the session carries the proof, every page funnels to the verify screen.
 * The landing route additionally keeps its signed-URL middleware.
 */
class EnsureSignerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $signatureRequest = $request->route('request');

        if (! $signatureRequest instanceof SignatureRequest) {
            abort(404);
        }

        if ($signatureRequest->signer_user_id !== null) {
            return $this->handleAccountSigner($request, $next, $signatureRequest);
        }

        return $this->handleGuestSigner($request, $next, $signatureRequest);
    }

    private function handleAccountSigner(Request $request, Closure $next, SignatureRequest $signatureRequest): Response
    {
        $user = $request->user();

        if ($user === null) {
            $request->session()->put('url.intended', $request->fullUrl());

            return new RedirectResponse(route('login'));
        }

        if ($user->email_verified_at === null) {
            $request->session()->put('url.intended', $request->fullUrl());

            return new RedirectResponse(route('verification.notice'));
        }

        // The email-code challenge is the guest identity proof; account
        // signers already proved theirs at login, so the verify screen is
        // never part of their flow.
        if ($request->route()?->getName() === 'esign.sign.verify') {
            return new RedirectResponse(route('esign.sign.review', ['request' => $signatureRequest->public_id]));
        }

        return $next($request);
    }

    private function handleGuestSigner(Request $request, Closure $next, SignatureRequest $signatureRequest): Response
    {
        if (GuestSignerSession::isVerified($signatureRequest)) {
            return $next($request);
        }

        // The landing (signed URL) and the verify screen itself are reachable
        // pre-verification; everything else funnels to the challenge.
        $route = $request->route()?->getName();

        if (in_array($route, ['esign.sign', 'esign.sign.verify'], true)) {
            return $next($request);
        }

        return new RedirectResponse(route('esign.sign.verify', ['request' => $signatureRequest->public_id]));
    }
}
