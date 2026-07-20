<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Domains\Lien\Waivers\WaiverSeats;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Seat management for the per-seat lien waiver subscription. Owners and
 * admins toggle seats for team members; every change syncs the Stripe
 * subscription quantity with proration (a new seat is charged pro rata
 * immediately, a released seat credits the next invoice). Quantity always
 * equals assigned seats — there is no spare-seat pool.
 */
class WaiverSeatManager extends Component
{
    public Business $business;

    public function mount(): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        abort_unless(WaiverEntitlements::canManageSeats($business, Auth::user()), 403);

        if (! WaiverEntitlements::isSubscribed($business)) {
            $this->redirect(route('lien.waivers.subscribe'));

            return;
        }

        $this->business = $business;
    }

    public function assign(int $userId): void
    {
        abort_unless(WaiverEntitlements::canManageSeats($this->business, Auth::user()), 403);

        $member = $this->business->users()->wherePivot('user_id', $userId)->first();

        if ($member === null) {
            return;
        }

        app(WaiverSeats::class)->assign($this->business, $member);

        Flux::toast(text: "Seat assigned to {$member->name} — the added seat is prorated on your next invoice.", variant: 'success');
    }

    public function release(int $userId): void
    {
        abort_unless(WaiverEntitlements::canManageSeats($this->business, Auth::user()), 403);

        $member = $this->business->users()->wherePivot('user_id', $userId)->first();

        if ($member === null) {
            return;
        }

        try {
            app(WaiverSeats::class)->release($this->business, $member);
        } catch (\RuntimeException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'warning');

            return;
        }

        Flux::toast(text: "Seat removed from {$member->name} — the unused time credits your next invoice.", variant: 'success');
    }

    /** Move a seat between members: nothing billed, the seat changes hands. */
    public function reassign(int $fromUserId, int $toUserId): void
    {
        abort_unless(WaiverEntitlements::canManageSeats($this->business, Auth::user()), 403);

        $from = $this->business->users()->wherePivot('user_id', $fromUserId)->first();
        $to = $this->business->users()->wherePivot('user_id', $toUserId)->first();

        if ($from === null || $to === null) {
            return;
        }

        try {
            app(WaiverSeats::class)->reassign($this->business, $from, $to);
        } catch (\InvalidArgumentException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'warning');

            return;
        }

        Flux::toast(text: "Seat moved from {$from->name} to {$to->name} — nothing extra is billed.", variant: 'success');
    }

    /** Cancel at period end: seats keep working until the paid time runs out. */
    public function cancelSubscription(): void
    {
        abort_unless(WaiverEntitlements::canManageBilling($this->business, Auth::user()), 403);

        app(WaiverSeats::class)->cancel($this->business);

        Flux::toast(text: 'Subscription cancelled — seats keep working until the end of the period you already paid for.', variant: 'success');
    }

    public function resumeSubscription(): void
    {
        abort_unless(WaiverEntitlements::canManageBilling($this->business, Auth::user()), 403);

        app(WaiverSeats::class)->resume($this->business);

        Flux::toast(text: 'Subscription resumed — it will renew as usual.', variant: 'success');
    }

    public function render(): View
    {
        $members = $this->business->users()->orderBy('first_name')->orderBy('last_name')->get();
        $subscription = WaiverEntitlements::subscription($this->business);

        return view('livewire.lien.waivers.waiver-seat-manager', [
            'members' => $members,
            'seatLimit' => WaiverEntitlements::seatLimit($this->business),
            'assignedSeats' => WaiverEntitlements::assignedSeats($this->business),
            'seatlessMembers' => $members->filter(fn ($member) => $member->pivot->lien_waiver_seat_at === null),
            'onGracePeriod' => $subscription?->onGracePeriod() ?? false,
            'endsAt' => $subscription?->ends_at,
            'canManageBilling' => WaiverEntitlements::canManageBilling($this->business, Auth::user()),
        ])->layout('components.layouts.portal', ['title' => 'Lien Waiver Seats']);
    }
}
