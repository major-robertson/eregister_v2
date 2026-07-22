<?php

namespace App\Domains\Lien\Livewire\Waivers;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Domains\Lien\Waivers\WaiverSeats;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Seat management for the per-seat lien waiver subscription.
 *
 * Owners and admins manage the whole team's seats (assign/remove anyone,
 * reassign) and the subscription itself (cancel/resume). Any other member can
 * add or remove only their OWN seat. Every quantity change syncs the Stripe
 * subscription with proration; quantity always equals assigned seats.
 *
 * Assign, remove, and cancel are confirmed through modals (the assign modal
 * spells out the prorated per-seat charge) rather than native prompts.
 */
class WaiverSeatManager extends Component
{
    public Business $business;

    /** Pending confirmations (the modal targets), null when closed. */
    public ?int $assignUserId = null;

    public ?int $releaseUserId = null;

    public bool $showCancelModal = false;

    public function mount(): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        abort_unless($business->users()->wherePivot('user_id', Auth::id())->exists(), 403);

        // No subscription yet: buying a seat is the checkout's job. Members
        // land there too — the checkout lets them buy their own seat.
        if (! WaiverEntitlements::isSubscribed($business)) {
            $this->redirect(route('lien.waivers.subscribe'));

            return;
        }

        $this->business = $business;
    }

    private function member(int $userId): ?User
    {
        return $this->business->users()->wherePivot('user_id', $userId)->first();
    }

    // ------------------------------------------------------------------
    // Assign (confirmed, with pricing)
    // ------------------------------------------------------------------

    public function confirmAssign(int $userId): void
    {
        $member = $this->member($userId);

        if ($member && WaiverEntitlements::canManageSeatFor($this->business, Auth::user(), $member)) {
            $this->assignUserId = $userId;
        }
    }

    public function assign(): void
    {
        $member = $this->assignUserId ? $this->member($this->assignUserId) : null;
        $this->assignUserId = null;

        if ($member === null || ! WaiverEntitlements::canManageSeatFor($this->business, Auth::user(), $member)) {
            abort_unless($member === null, 403);

            return;
        }

        app(WaiverSeats::class)->assign($this->business, $member);

        Flux::toast(text: "Seat added for {$member->name} — prorated on your next invoice.", variant: 'success');
    }

    // ------------------------------------------------------------------
    // Remove (confirmed)
    // ------------------------------------------------------------------

    public function confirmRelease(int $userId): void
    {
        $member = $this->member($userId);

        if ($member && WaiverEntitlements::canManageSeatFor($this->business, Auth::user(), $member)) {
            $this->releaseUserId = $userId;
        }
    }

    public function release(): void
    {
        $member = $this->releaseUserId ? $this->member($this->releaseUserId) : null;
        $this->releaseUserId = null;

        if ($member === null || ! WaiverEntitlements::canManageSeatFor($this->business, Auth::user(), $member)) {
            abort_unless($member === null, 403);

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

    // ------------------------------------------------------------------
    // Reassign (admin/owner only — moves a seat, nothing billed)
    // ------------------------------------------------------------------

    public function reassign(int $fromUserId, int $toUserId): void
    {
        abort_unless(WaiverEntitlements::canManageSeats($this->business, Auth::user()), 403);

        $from = $this->member($fromUserId);
        $to = $this->member($toUserId);

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

    // ------------------------------------------------------------------
    // Cancel / resume (admin/owner only)
    // ------------------------------------------------------------------

    public function cancelSubscription(): void
    {
        $this->showCancelModal = false;

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
        $canManageSeats = WaiverEntitlements::canManageSeats($this->business, Auth::user());

        return view('livewire.lien.waivers.waiver-seat-manager', [
            'members' => $members,
            'assignedSeats' => WaiverEntitlements::assignedSeats($this->business),
            'seatlessMembers' => $members->filter(fn ($member) => $member->pivot->lien_waiver_seat_at === null),
            'onGracePeriod' => $subscription?->onGracePeriod() ?? false,
            'endsAt' => $subscription?->ends_at,
            'canManageSeats' => $canManageSeats,
            'canManageBilling' => WaiverEntitlements::canManageBilling($this->business, Auth::user()),
            'perSeatPrice' => WaiverEntitlements::perSeatPrice($this->business),
            'assignTarget' => $this->assignUserId ? $members->firstWhere('id', $this->assignUserId) : null,
            'releaseTarget' => $this->releaseUserId ? $members->firstWhere('id', $this->releaseUserId) : null,
        ])->layout('components.layouts.portal', ['title' => 'Lien Waiver Seats']);
    }
}
