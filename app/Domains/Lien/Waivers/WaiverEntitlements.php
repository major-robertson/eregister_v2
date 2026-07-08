<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienWaiver;

/**
 * Free-vs-paid gate for the lien waiver product.
 *
 * Free tier: unlimited generate + download (nothing is saved), up to
 * config('lien_waivers.free_saved_waivers_per_month') saved waivers per
 * calendar month, and no e-signature. Paid ($99/mo or $990/yr, Cashier
 * subscription type 'lien_waiver' on the Business): unlimited saves plus
 * e-signature send/collect, reminders, and signed-copy storage.
 *
 * Static so Blade views and Livewire components can gate features without
 * injecting a service; every method takes the Business explicitly because
 * entitlements attach to the business, not the user.
 */
class WaiverEntitlements
{
    public static function hasPaidAccess(Business $business): bool
    {
        return $business->subscribed(config('lien_waivers.subscription_type'));
    }

    /**
     * Saved waivers this calendar month (voided ones still count: the save
     * consumed the slot; see LienWaiver::savedThisMonthFor).
     */
    public static function savedThisMonth(Business $business): int
    {
        return LienWaiver::savedThisMonthFor($business);
    }

    public static function freeSavesLimit(): int
    {
        return (int) config('lien_waivers.free_saved_waivers_per_month');
    }

    public static function remainingFreeSaves(Business $business): int
    {
        return max(0, static::freeSavesLimit() - static::savedThisMonth($business));
    }

    public static function canSaveWaiver(Business $business): bool
    {
        return static::hasPaidAccess($business)
            || static::remainingFreeSaves($business) > 0;
    }

    /**
     * E-signature (send, collect, reminders, signed storage) is
     * subscription-only; there is no free-tier allowance.
     */
    public static function canUseEsign(Business $business): bool
    {
        return static::hasPaidAccess($business);
    }
}
