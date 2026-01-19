<?php

namespace App\Domains\Portal\Http\Middleware;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\FormTypeConfig;
use App\Domains\Forms\Models\FormApplication;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $application = $request->route('application');
        // Business comes from request attributes (set by ResolveCurrentBusiness middleware)
        $business = $request->attributes->get('business');

        if (! $application instanceof FormApplication) {
            abort(404);
        }

        if (! $business instanceof Business) {
            return redirect()->route('portal.select-business');
        }

        // Verify application belongs to business
        if ($application->business_id !== $business->id) {
            abort(403, 'Application does not belong to this business.');
        }

        // Verify user is member of business
        Gate::authorize('view', $business);

        // Check access based on billing type
        if (! $this->hasAccess($business, $application)) {
            return redirect()->route('portal.checkout', $application);
        }

        return $next($request);
    }

    protected function hasAccess(Business $business, FormApplication $application): bool
    {
        $config = FormTypeConfig::get($application->form_type);

        return match ($config['billing_type']) {
            'subscription' => $business->subscribed($config['subscription_name']),
            'one_time_per_state', 'one_time' => $application->paid_at !== null,
            default => false,
        };
    }
}
