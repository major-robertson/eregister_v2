<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Business\Models\Business;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Cashier\Subscription;

/**
 * Seat assignment for the per-seat lien waiver subscription.
 *
 * The invariant is donuts-simple: subscription quantity == assigned seats.
 * Assigning a member bumps the Stripe quantity (prorated onto the saved
 * payment method); releasing one credits it back. There is no spare-seat
 * pool to manage. Stub subscriptions (keyless local dev, stripe_id
 * "stub_...") update the local quantity only.
 */
class WaiverSeats
{
    /** Assign a seat to a member, growing the subscription if needed. */
    public function assign(Business $business, User $user): void
    {
        if (! $business->users()->wherePivot('user_id', $user->id)->exists()) {
            throw new \InvalidArgumentException('User is not a member of this business.');
        }

        if (WaiverEntitlements::hasSeat($business, $user)) {
            return;
        }

        $business->users()->updateExistingPivot($user->id, ['lien_waiver_seat_at' => now()]);

        $this->syncQuantity($business);
    }

    /**
     * Release a member's seat and shrink the subscription. The last seat
     * can't be released here — Stripe subscriptions need quantity >= 1, so
     * the way to drop to zero is cancelling the subscription.
     */
    public function release(Business $business, User $user): void
    {
        if (! WaiverEntitlements::hasSeat($business, $user)) {
            return;
        }

        if (WaiverEntitlements::assignedSeats($business) <= 1 && WaiverEntitlements::isSubscribed($business)) {
            throw new \RuntimeException(
                'The last seat cannot be released — cancel the subscription instead.'
            );
        }

        $business->users()->updateExistingPivot($user->id, ['lien_waiver_seat_at' => null]);

        $this->syncQuantity($business);
    }

    /**
     * Assign seats to many members at once (checkout success path). The
     * subscription quantity was already set at purchase, so no sync here —
     * stale selections (users since removed from the business) are skipped.
     *
     * @param  list<int>  $userIds
     */
    public function assignPurchased(Business $business, array $userIds): void
    {
        $memberIds = $business->users()->pluck('users.id')->all();

        foreach (array_intersect($userIds, $memberIds) as $userId) {
            $business->users()->updateExistingPivot($userId, ['lien_waiver_seat_at' => now()]);
        }
    }

    /** Push assigned-seat count to Stripe (no-op when unsubscribed or stubbed). */
    private function syncQuantity(Business $business): void
    {
        $subscription = WaiverEntitlements::subscription($business);

        if ($subscription === null) {
            return;
        }

        $quantity = max(1, WaiverEntitlements::assignedSeats($business));

        if ($this->isStub($subscription)) {
            $subscription->update(['quantity' => $quantity]);

            return;
        }

        if ((int) $subscription->quantity !== $quantity) {
            // Cashier prorates by default and syncs the local quantity column.
            $subscription->updateQuantity($quantity);
        }
    }

    private function isStub(Subscription $subscription): bool
    {
        return Str::startsWith($subscription->stripe_id, 'stub_');
    }
}
