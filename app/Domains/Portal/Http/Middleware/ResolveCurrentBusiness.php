<?php

namespace App\Domains\Portal\Http\Middleware;

use App\Domains\Business\Models\Business;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentBusiness
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $business = $user->currentBusiness();

        if (! $business) {
            // Auto-select if user has exactly one business
            $businesses = $user->businesses;

            if ($businesses->count() === 1) {
                $business = $businesses->first();
                session(['current_business_id' => $business->id]);
            } elseif ($businesses->count() === 0) {
                // No businesses - go to create first one
                return redirect()->route('portal.select-business');
            } else {
                // Multiple businesses - user must choose
                return redirect()->route('portal.select-business');
            }
        }

        // Validate membership via policy
        Gate::authorize('view', $business);

        // Bind business to request attributes for views/controllers
        $request->attributes->set('business', $business);

        // Also share with views
        view()->share('business', $business);

        return $next($request);
    }
}
