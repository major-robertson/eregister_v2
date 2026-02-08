<?php

namespace App\Concerns;

use App\Domains\Marketing\Models\MarketingLead;

/**
 * Resolves the active marketing lead for onboarding pre-fill.
 *
 * Uses session only -- no cookie or user column fallback.
 * The ActivateMarketingLeadContext middleware handles
 * cookie-to-session activation on the correct routes.
 */
trait ResolvesMarketingLead
{
    /**
     * Resolve the active marketing lead for pre-filling form fields.
     *
     * Returns null if no active lead context exists in the session.
     * This is intentionally session-only to prevent accidental
     * re-activation from cookies on non-onboarding pages.
     */
    protected function resolveLeadForPrefill(): ?MarketingLead
    {
        if ($id = session('active_marketing_lead_id')) {
            return MarketingLead::find($id);
        }

        return null;
    }
}
