<?php

namespace App\Http\Responses;

use App\Services\OpenAiConversionsApi;
use App\Services\RedditConversionsApi;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): Response
    {
        // Signup-conversion marker consumed by the onboarding wizard. A
        // flash would age out on the /portal -> select-business redirect
        // before the wizard ever renders, so persist until pulled.
        session()->put('just_registered', true);

        // Server-side counterpart of the wizard's pixel signup events; same
        // conversion/event id per platform, so each dedupes when both arrive.
        app(RedditConversionsApi::class)->queueSignUp($request->user(), $request);
        app(OpenAiConversionsApi::class)->queueRegistration($request->user(), $request);

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(config('fortify.home'));
    }
}
