<?php

namespace App\Domains\Portal\Http\Middleware;

use App\Domains\Business\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessProfileComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        // Business comes from request attributes (set by ResolveCurrentBusiness middleware)
        $business = $request->attributes->get('business');

        if (! $business instanceof Business) {
            return redirect()->route('portal.select-business');
        }

        if (! $business->isOnboardingComplete()) {
            return redirect()->route('portal.onboarding');
        }

        return $next($request);
    }
}
