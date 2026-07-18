<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienWaiver;

/**
 * Free-vs-paid gate for the lien waiver product.
 *
 * Free tier: the full product — download, e-signature send/collect,
 * reminders, signed-copy storage — for up to
 * config('lien_waivers.free_saved_waivers_per_month') waivers per calendar
 * month. Every waiver auto-saves when the wizard reaches review, and that
 * save is what consumes a slot. Paid ($99/mo or $990/yr, Cashier
 * subscription type 'lien_waiver' on the Business) removes the monthly cap.
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
     * E-signature (send, collect, reminders, signed storage) is available on
     * every tier: the free tier's only limit is the monthly save allowance,
     * enforced when the waiver is saved — a waiver that exists may be sent.
     */
    public static function canUseEsign(Business $business): bool
    {
        return true;
    }
}
