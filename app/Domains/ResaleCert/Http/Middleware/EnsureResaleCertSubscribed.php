<?php

namespace App\Domains\ResaleCert\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Gates the certificate-generation area on an active resale-cert
 * subscription. The dashboard stays outside this gate so it can render the
 * subscribe prompt.
 */
class EnsureResaleCertSubscribed
{
    public function handle(Request $request, Closure $next)
    {
        $business = $request->user()?->currentBusiness();

        if (! $business || ! $business->subscribed(config('resale_cert.subscription_type'))) {
            // The dashboard renders the pricing card for unsubscribed
            // businesses, one click from checkout.
            return redirect()
                ->route('resale-cert.dashboard')
                ->with('info', 'Subscribe to access the Resale Certificate Generator.');
        }

        return $next($request);
    }
}
