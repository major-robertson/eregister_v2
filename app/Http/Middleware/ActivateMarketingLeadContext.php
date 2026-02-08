<?php

namespace App\Http\Middleware;

use App\Domains\Marketing\Models\MarketingLead;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivateMarketingLeadContext
{
    /**
     * TTL for the active lead context in days.
     */
    private const TTL_DAYS = 14;

    /**
     * Activate marketing lead context from ?lead= query param or lead_ref cookie.
     *
     * This middleware should only be applied to onboarding routes
     * (/register, /portal/select-business, /portal/onboarding, etc.)
     * to prevent accidental re-activation on random internal pages.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $existingId = session('active_marketing_lead_id');
        $setAt = session('active_marketing_lead_set_at');

        // If session exists, check TTL
        if ($existingId) {
            $isExpired = ! $setAt || Carbon::parse($setAt)->addDays(self::TTL_DAYS)->isPast();

            if ($isExpired) {
                // Clear expired context and fall through to re-resolve
                session()->forget(['active_marketing_lead_id', 'active_marketing_lead_set_at']);
            } else {
                // Still valid, skip resolution
                return $next($request);
            }
        }

        // If onboarding pre-fill was already completed (first project created),
        // don't re-activate from cookie -- attribution is on the user record.
        if (session('marketing_lead_prefill_completed')) {
            return $next($request);
        }

        // Resolve lead ref: query param first, then cookie
        $ref = $request->query('lead') ?? $request->cookie('lead_ref');

        if (! $ref) {
            return $next($request);
        }

        $lead = MarketingLead::where('public_id', $ref)->first();

        if ($lead) {
            session([
                'active_marketing_lead_id' => $lead->id,
                'active_marketing_lead_set_at' => now()->toISOString(),
            ]);
        }

        return $next($request);
    }
}
