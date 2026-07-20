<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\Waivers\WaiverSeatManager;
use App\Domains\Lien\Livewire\Waivers\WaiverSubscriptionCheckout;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Domains\Lien\Waivers\WaiverSeats;
use App\Models\User;
use Livewire\Livewire;

if (! function_exists('waiverSeatsSubscribe')) {
    /** Active stub subscription with seats for the given members. */
    function waiverSeatsSubscribe(Business $business, User ...$seatHolders): void
    {
        $business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'stub_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'stub_price',
            'quantity' => max(1, count($seatHolders)),
        ]);

        foreach ($seatHolders as $seatHolder) {
            $business->users()->updateExistingPivot($seatHolder->id, ['lien_waiver_seat_at' => now()]);
        }
    }
}

beforeEach(function () {
    $this->travelTo(now()->startOfMonth()->addDays(14)->setTime(12, 0));

    $this->owner = User::factory()->create(['first_name' => 'Olivia', 'last_name' => 'Owner']);
    $this->member = User::factory()->create(['first_name' => 'Manny', 'last_name' => 'Member']);
    $this->business = Business::factory()->create([
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->owner, ['role' => 'owner']);
    $this->business->users()->attach($this->member, ['role' => 'member']);

    $this->actingAs($this->owner);
    session(['current_business_id' => $this->business->id]);
});

describe('seats service', function () {
    it('assigns and releases seats, keeping the stub quantity in sync with assignments', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        app(WaiverSeats::class)->assign($this->business, $this->member);

        expect(WaiverEntitlements::assignedSeats($this->business))->toBe(2);
        expect(WaiverEntitlements::seatLimit($this->business->refresh()))->toBe(2);
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->member))->toBeTrue();

        app(WaiverSeats::class)->release($this->business, $this->member);

        expect(WaiverEntitlements::assignedSeats($this->business))->toBe(1);
        expect(WaiverEntitlements::seatLimit($this->business->refresh()))->toBe(1);
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->member))->toBeFalse();
    });

    it('refuses to release the last seat while subscribed', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        expect(fn () => app(WaiverSeats::class)->release($this->business, $this->owner))
            ->toThrow(RuntimeException::class);

        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeTrue();
    });

    it('refuses to assign a seat to a non-member', function () {
        waiverSeatsSubscribe($this->business, $this->owner);
        $outsider = User::factory()->create();

        expect(fn () => app(WaiverSeats::class)->assign($this->business, $outsider))
            ->toThrow(InvalidArgumentException::class);
    });

    it('assignPurchased assigns only current members and skips strangers', function () {
        waiverSeatsSubscribe($this->business);
        $outsider = User::factory()->create();

        app(WaiverSeats::class)->assignPurchased($this->business, [
            $this->owner->id, $this->member->id, $outsider->id,
        ]);

        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeTrue();
        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();
        expect(WaiverEntitlements::assignedSeats($this->business))->toBe(2);
    });
});

describe('seat manager', function () {
    it('lets an owner assign and release seats from the page', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        Livewire::test(WaiverSeatManager::class)
            ->call('assign', $this->member->id);

        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();
        expect(WaiverEntitlements::seatLimit($this->business->refresh()))->toBe(2);

        Livewire::test(WaiverSeatManager::class)
            ->call('release', $this->member->id);

        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeFalse();
        expect(WaiverEntitlements::seatLimit($this->business->refresh()))->toBe(1);
    });

    it('keeps the last seat when a release is attempted from the page', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        Livewire::test(WaiverSeatManager::class)
            ->call('release', $this->owner->id);

        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeTrue();
    });

    it('is off-limits to plain members', function () {
        waiverSeatsSubscribe($this->business, $this->owner);
        $this->actingAs($this->member);

        $this->get(route('lien.waivers.seats'))->assertForbidden();
    });

    it('sends an unsubscribed owner to checkout instead', function () {
        $this->get(route('lien.waivers.seats'))
            ->assertRedirect(route('lien.waivers.subscribe'));
    });
});

describe('per-seat checkout (stub, keyless)', function () {
    beforeEach(function () {
        config(['cashier.secret' => '']);
    });

    it('activates the selected seats and sets the subscription quantity', function () {
        Livewire::test(WaiverSubscriptionCheckout::class)
            ->assertSet('canPickSeats', true)
            ->set('seatUserIds', [$this->owner->id, $this->member->id])
            ->call('proceedToPayment')
            ->assertRedirect(route('lien.waivers.payment-confirmation'));

        $subscription = WaiverEntitlements::subscription($this->business->refresh());
        expect($subscription)->not->toBeNull();
        expect((int) $subscription->quantity)->toBe(2);
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->owner))->toBeTrue();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->member))->toBeTrue();
    });

    it('lets a plain member buy exactly their own seat, whatever they select', function () {
        $this->actingAs($this->member);

        Livewire::test(WaiverSubscriptionCheckout::class)
            ->assertSet('canPickSeats', false)
            ->set('seatUserIds', [$this->owner->id, $this->member->id])
            ->call('proceedToPayment')
            ->assertRedirect(route('lien.waivers.payment-confirmation'));

        $subscription = WaiverEntitlements::subscription($this->business->refresh());
        expect((int) $subscription->quantity)->toBe(1);
        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();
        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeFalse();
    });

    it('requires at least one selected member', function () {
        Livewire::test(WaiverSubscriptionCheckout::class)
            ->set('seatUserIds', [])
            ->call('proceedToPayment')
            ->assertHasErrors('seatUserIds');

        expect(WaiverEntitlements::isSubscribed($this->business->refresh()))->toBeFalse();
    });

    it('sends an already-subscribed owner to the seat manager', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        Livewire::test(WaiverSubscriptionCheckout::class)
            ->assertRedirect(route('lien.waivers.seats'));
    });
});

describe('reassign, cancel, resume', function () {
    it('reassigns a seat to another member without changing quantity or billing', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        app(WaiverSeats::class)->reassign($this->business, $this->owner, $this->member);

        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeFalse();
        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();
        expect(WaiverEntitlements::seatLimit($this->business->refresh()))->toBe(1);
        expect(WaiverEntitlements::assignedSeats($this->business))->toBe(1);
    });

    it('refuses to reassign to a member who already holds a seat', function () {
        waiverSeatsSubscribe($this->business, $this->owner, $this->member);

        expect(fn () => app(WaiverSeats::class)->reassign($this->business, $this->owner, $this->member))
            ->toThrow(InvalidArgumentException::class);
    });

    it('reassigns from the seat manager page', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        Livewire::test(WaiverSeatManager::class)
            ->call('reassign', $this->owner->id, $this->member->id);

        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();
        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeFalse();
    });

    it('cancelling enters a grace period where seats keep working; resume undoes it', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        Livewire::test(WaiverSeatManager::class)->call('cancelSubscription');

        $subscription = $this->business->refresh()->subscription(config('lien_waivers.subscription_type'));
        expect($subscription->onGracePeriod())->toBeTrue();
        // Paid access continues through the grace period.
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->owner))->toBeTrue();

        Livewire::test(WaiverSeatManager::class)->call('resumeSubscription');

        expect($this->business->refresh()->subscription(config('lien_waivers.subscription_type'))->onGracePeriod())->toBeFalse();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->owner))->toBeTrue();
    });

    it('drops everyone to the free tier once the grace period lapses, keeping seat flags for a resubscribe', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        app(WaiverSeats::class)->cancel($this->business);
        $this->travelTo(now()->addMonths(2));

        expect(WaiverEntitlements::isSubscribed($this->business->refresh()))->toBeFalse();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->owner))->toBeFalse();
        // The assignment survives, so a resubscribe restores the same people.
        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeTrue();
    });

    it('lets admins manage seats but reserves cancel/resume for owners', function () {
        waiverSeatsSubscribe($this->business, $this->owner);
        $admin = User::factory()->create();
        $this->business->users()->attach($admin, ['role' => 'admin']);

        expect(WaiverEntitlements::canManageSeats($this->business, $admin))->toBeTrue();
        expect(WaiverEntitlements::canManageBilling($this->business, $admin))->toBeFalse();
        expect(WaiverEntitlements::canManageBilling($this->business, $this->owner))->toBeTrue();

        $this->actingAs($admin);

        Livewire::test(WaiverSeatManager::class)
            ->call('cancelSubscription')
            ->assertStatus(403);

        expect($this->business->refresh()->subscription(config('lien_waivers.subscription_type'))->onGracePeriod())->toBeFalse();
    });
});
