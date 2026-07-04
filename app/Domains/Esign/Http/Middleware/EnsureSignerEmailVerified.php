<?php

namespace App\Domains\Esign\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requires the signer's email to be verified before signing. The User model
 * does not implement MustVerifyEmail, so Laravel's built-in `verified`
 * middleware is a no-op here — we check email_verified_at explicitly.
 */
class EnsureSignerEmailVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->email_verified_at === null) {
            // Remember where they were headed so the post-verification
            // response (VerifyEmailResponse) can send them straight back.
            $request->session()->put('url.intended', $request->fullUrl());

            // Build the response explicitly: on a Livewire full-page route the
            // redirect() helper resolves to Livewire's Redirector, which is not
            // a Symfony Response.
            return new RedirectResponse(route('verification.notice'));
        }

        return $next($request);
    }
}
