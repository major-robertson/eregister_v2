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

    public function render(): View
    {
        $members = $this->business->users()->orderBy('first_name')->orderBy('last_name')->get();

        return view('livewire.lien.waivers.waiver-seat-manager', [
            'members' => $members,
            'seatLimit' => WaiverEntitlements::seatLimit($this->business),
            'assignedSeats' => WaiverEntitlements::assignedSeats($this->business),
        ])->layout('components.layouts.portal', ['title' => 'Lien Waiver Seats']);
    }
}
