<?php

namespace App\Domains\Portal\Http\Middleware;

use App\Domains\Business\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        // This middleware is deprecated - use ResolveCurrentBusiness instead
        // Kept for backward compatibility
        $business = $request->attributes->get('business');

        if (! $business instanceof Business) {
            // No business in request, check session
            if ($id = session('current_business_id')) {
                $business = $request->user()->businesses()->find($id);
                if ($business) {
                    session(['current_business_id' => $business->id]);
                    $request->attributes->set('business', $business);

                    return $next($request);
                }
            }

            return redirect()->route('portal.select-business');
        }

        // Validate membership via policy
        Gate::authorize('view', $business);

        return $next($request);
    }
}
