<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\User;
use Laravel\Cashier\Subscription;

/**
 * Free-vs-paid gate for the lien waiver product — per seat.
 *
 * The business holds ONE Cashier subscription (type 'lien_waiver', $99/mo or
 * $990/yr per seat) whose quantity is the number of seats; seats are assigned
 * to members via business_user.lien_waiver_seat_at. A member with a seat has
 * unlimited waivers; members without one share the business's free tier —
 * the full product (download, e-sign send/collect, reminders, signed-copy
 * storage) for config('lien_waivers.free_saved_waivers_per_month') waivers
 * per calendar month across the business.
 *
 * Owners and admins manage seats (assign, release, add) from the seat
 * manager; each change syncs the Stripe quantity with proration.
 *
 * Static so Blade views and Livewire components can gate features without
 * injecting a service; methods take the Business (and User, where access is
 * per-seat) explicitly.
 */
class WaiverEntitlements
{
    /** The business's active lien_waiver subscription, if any. */
    public static function subscription(Business $business): ?Subscription
    {
        $subscription = $business->subscription(config('lien_waivers.subscription_type'));

        return $subscription?->valid() ? $subscription : null;
    }

    public static function isSubscribed(Business $business): bool
    {
        return static::subscription($business) !== null;
    }

    /** Seats purchased = the subscription quantity (0 when unsubscribed). */
    public static function seatLimit(Business $business): int
    {
        return (int) (static::subscription($business)?->quantity ?? 0);
    }

    /** Members currently holding a seat. */
    public static function assignedSeats(Business $business): int
    {
        return $business->users()->wherePivotNotNull('lien_waiver_seat_at')->count();
    }

    public static function hasSeat(Business $business, User $user): bool
    {
        return $business->users()
            ->wherePivot('user_id', $user->id)
            ->wherePivotNotNull('lien_waiver_seat_at')
            ->exists();
    }

    /** Paid (unlimited) access: active subscription AND a seat for this user. */
    public static function hasPaidAccess(Business $business, User $user): bool
    {
        return static::isSubscribed($business) && static::hasSeat($business, $user);
    }

    /**
     * Owners and admins manage the whole team's seats (assign/remove anyone,
     * reassign). Members can only manage their own seat — see canManageSeatFor.
     * Mirrors BusinessPolicy::manageMembers.
     */
    public static function canManageSeats(Business $business, User $user): bool
    {
        $role = $user->businesses()->find($business->id)?->pivot->role;

        return in_array($role, ['owner', 'admin'], true);
    }

    /**
     * Whether $user may add/remove the seat of $target: owners and admins for
     * anyone, everyone else only for themselves.
     */
    public static function canManageSeatFor(Business $business, User $user, User $target): bool
    {
        return $user->id === $target->id || static::canManageSeats($business, $user);
    }

    /** Cancelling/resuming the whole subscription is an owner/admin action. */
    public static function canManageBilling(Business $business, User $user): bool
    {
        return static::canManageSeats($business, $user);
    }

    /**
     * The per-seat price of the active subscription (for confirm dialogs),
     * resolved from the subscription's Stripe price; falls back to the monthly
     * catalog price for stubs or an unmatched price.
     *
     * @return array{amount_cents: int, interval: string, per_label: string, formatted: string}
     */
    public static function perSeatPrice(Business $business): array
    {
        $priceId = static::subscription($business)?->stripe_price;

        $prices = \App\Models\Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->get();

        $match = $prices->first(fn ($price) => $price->stripePriceId() === $priceId)
            ?? $prices->firstWhere('interval', 'month');

        $cents = (int) ($match->amount_cents ?? config('lien_waivers.prices.monthly.amount_cents'));
        $interval = $match->interval ?? 'month';

        return [
            'amount_cents' => $cents,
            'interval' => $interval,
            'per_label' => $interval === 'year' ? 'yr' : 'mo',
            'formatted' => '$'.number_format($cents / 100),
        ];
    }

    /**
     * Saved waivers this calendar month (voided ones still count: the save
     * consumed the slot; see LienWaiver::savedThisMonthFor). Business-wide —
     * the free allowance is shared by members without seats.
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

    public static function canSaveWaiver(Business $business, User $user): bool
    {
        return static::hasPaidAccess($business, $user)
            || static::remainingFreeSaves($business) > 0;
    }

    /**
     * E-signature (send, collect, reminders, signed storage) is available on
     * every tier: the only limit is the monthly save allowance, enforced when
     * the waiver is saved — a waiver that exists may be sent.
     */
    public static function canUseEsign(Business $business): bool
    {
        return true;
    }
}
