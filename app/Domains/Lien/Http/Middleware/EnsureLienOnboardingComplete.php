<?php

namespace App\Domains\Lien\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureLienOnboardingComplete
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        $business = $user->currentBusiness();

        if (! $business) {
            return redirect()->route('portal.select-business');
        }

        // Check if lien onboarding is complete
        if (! $business->isLienOnboardingComplete()) {
            return redirect()->route('lien.onboarding');
        }

        return $next($request);
    }
}
