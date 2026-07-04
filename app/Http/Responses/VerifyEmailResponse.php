<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

/**
 * After clicking the verification link, return the user to whatever they
 * were trying to do (an esign signing session, resale profile setup, etc.)
 * instead of dumping them on the portal hub. The intended URL is stashed by
 * EnsureSignerEmailVerified and the in-page resend actions.
 */
class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request)
    {
        return redirect()->intended(Fortify::redirects('email-verification', config('fortify.home').'?verified=1'));
    }
}
