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
    it('lets an owner confirm then assign and release seats from the page', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        // Assign is confirmed through the pricing modal.
        Livewire::test(WaiverSeatManager::class)
            ->call('confirmAssign', $this->member->id)
            ->assertSet('assignUserId', $this->member->id)
            ->call('assign')
            ->assertSet('assignUserId', null);

        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();
        expect(WaiverEntitlements::seatLimit($this->business->refresh()))->toBe(2);

        Livewire::test(WaiverSeatManager::class)
            ->call('confirmRelease', $this->member->id)
            ->assertSet('releaseUserId', $this->member->id)
            ->call('release');

        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeFalse();
        expect(WaiverEntitlements::seatLimit($this->business->refresh()))->toBe(1);
    });

    it('keeps the last seat when a release is attempted from the page', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        Livewire::test(WaiverSeatManager::class)
            ->call('confirmRelease', $this->owner->id)
            ->call('release');

        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeTrue();
    });

    it('lets a plain member manage only their own seat', function () {
        waiverSeatsSubscribe($this->business, $this->owner);
        $this->actingAs($this->member);

        // The page loads for a member (they can self-serve).
        $this->get(route('lien.waivers.seats'))->assertOk();

        // A member can add their own seat...
        Livewire::test(WaiverSeatManager::class)
            ->call('confirmAssign', $this->member->id)
            ->assertSet('assignUserId', $this->member->id)
            ->call('assign');
        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();

        // ...and remove it...
        Livewire::test(WaiverSeatManager::class)
            ->call('confirmRelease', $this->member->id)
            ->call('release');
        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeFalse();

        // ...but cannot touch the owner's seat.
        Livewire::test(WaiverSeatManager::class)
            ->call('confirmRelease', $this->owner->id)
            ->assertSet('releaseUserId', null)
            ->call('confirmAssign', $this->owner->id)
            ->assertSet('assignUserId', null);
        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeTrue();
    });

    it('forbids a member from reassigning or cancelling', function () {
        waiverSeatsSubscribe($this->business, $this->owner);
        $this->actingAs($this->member);

        Livewire::test(WaiverSeatManager::class)
            ->call('reassign', $this->owner->id, $this->member->id)
            ->assertStatus(403);

        Livewire::test(WaiverSeatManager::class)
            ->call('cancelSubscription')
            ->assertStatus(403);

        expect($this->business->refresh()->subscription(config('lien_waivers.subscription_type'))->onGracePeriod())->toBeFalse();
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

    it('confirming the selection reloads with ?seats=, and that mount activates them', function () {
        // Phase 1: pick seats; continue is a FULL redirect (the Stripe element
        // must initialize at first paint, so payment is set up during mount).
        Livewire::test(WaiverSubscriptionCheckout::class)
            ->assertSet('canPickSeats', true)
            ->set('seatUserIds', [$this->owner->id, $this->member->id])
            ->call('proceedToPayment')
            ->assertRedirect(route('lien.waivers.subscribe', [
                'interval' => 'monthly',
                'seats' => $this->owner->id.','.$this->member->id,
            ]));

        // Phase 2: arriving with ?seats= initializes payment in mount (stub
        // path completes immediately).
        Livewire::withQueryParams(['interval' => 'monthly', 'seats' => $this->owner->id.','.$this->member->id])
            ->test(WaiverSubscriptionCheckout::class)
            ->assertRedirect(route('lien.waivers.payment-confirmation'));

        $subscription = WaiverEntitlements::subscription($this->business->refresh());
        expect($subscription)->not->toBeNull();
        expect((int) $subscription->quantity)->toBe(2);
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->owner))->toBeTrue();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->member))->toBeTrue();
    });

    it('lets a plain member buy exactly their own seat, whatever ?seats= claims', function () {
        $this->actingAs($this->member);

        Livewire::withQueryParams(['interval' => 'monthly', 'seats' => $this->owner->id.','.$this->member->id])
            ->test(WaiverSubscriptionCheckout::class)
            ->assertSet('canPickSeats', false)
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

    it('lets an admin manage seats and cancel, but a plain member neither', function () {
        waiverSeatsSubscribe($this->business, $this->owner);
        $admin = User::factory()->create();
        $this->business->users()->attach($admin, ['role' => 'admin']);

        // Admins can manage the whole team's seats and the subscription.
        expect(WaiverEntitlements::canManageSeats($this->business, $admin))->toBeTrue();
        expect(WaiverEntitlements::canManageBilling($this->business, $admin))->toBeTrue();
        // A plain member can do neither at the team level.
        expect(WaiverEntitlements::canManageSeats($this->business, $this->member))->toBeFalse();
        expect(WaiverEntitlements::canManageBilling($this->business, $this->member))->toBeFalse();
        // But a member may manage their own seat.
        expect(WaiverEntitlements::canManageSeatFor($this->business, $this->member, $this->member))->toBeTrue();
        expect(WaiverEntitlements::canManageSeatFor($this->business, $this->member, $this->owner))->toBeFalse();
        expect(WaiverEntitlements::canManageSeatFor($this->business, $admin, $this->member))->toBeTrue();

        $this->actingAs($admin);

        Livewire::test(WaiverSeatManager::class)->call('cancelSubscription');

        expect($this->business->refresh()->subscription(config('lien_waivers.subscription_type'))->onGracePeriod())->toBeTrue();
    });

    it('exposes the per-seat price for the confirm dialog', function () {
        waiverSeatsSubscribe($this->business, $this->owner);

        $price = WaiverEntitlements::perSeatPrice($this->business);

        // Stub subscriptions fall back to the monthly catalog price.
        expect($price['amount_cents'])->toBe(9900);
        expect($price['interval'])->toBe('month');
        expect($price['formatted'])->toBe('$99');
        expect($price['per_label'])->toBe('mo');
    });
});

describe('stripe quantity sync (non-stub)', function () {
    it('syncs a real subscription quantity via the Stripe API, not Cashier updateQuantity', function () {
        // A real (non-stub) subscription, as the checkout creates it: quantity
        // starts at 2, both members seated.
        $this->business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'sub_fake123',
            'stripe_status' => 'active',
            'stripe_price' => 'price_fake',
            'quantity' => 2,
        ]);
        $this->business->users()->updateExistingPivot($this->owner->id, ['lien_waiver_seat_at' => now()]);
        $this->business->users()->updateExistingPivot($this->member->id, ['lien_waiver_seat_at' => now()]);

        // Spy on the Stripe seam: releasing a seat must push quantity 1
        // through pushStripeQuantity (the raw API), never Cashier's broken
        // updateQuantity(). Binding a partial mock guards the regression.
        $seats = Mockery::mock(WaiverSeats::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $seats->shouldReceive('pushStripeQuantity')
            ->once()
            ->with(Mockery::type(Laravel\Cashier\Subscription::class), 1);

        $seats->release($this->business, $this->member);

        // Local column mirrors the new quantity and the seat is gone.
        expect(WaiverEntitlements::assignedSeats($this->business))->toBe(1);
        expect((int) WaiverEntitlements::subscription($this->business->refresh())->quantity)->toBe(1);
        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeFalse();
    });

    it('does not touch Stripe when the quantity is unchanged (reassign)', function () {
        $this->business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'sub_fake456',
            'stripe_status' => 'active',
            'stripe_price' => 'price_fake',
            'quantity' => 1,
        ]);
        $this->business->users()->updateExistingPivot($this->owner->id, ['lien_waiver_seat_at' => now()]);

        $seats = Mockery::mock(WaiverSeats::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $seats->shouldNotReceive('pushStripeQuantity');

        // Reassign moves the seat: count stays 1, so no Stripe call at all.
        $seats->reassign($this->business, $this->owner, $this->member);

        expect(WaiverEntitlements::hasSeat($this->business, $this->member))->toBeTrue();
        expect(WaiverEntitlements::hasSeat($this->business, $this->owner))->toBeFalse();
    });
});

describe('cancel/resume (non-stub)', function () {
    it('cancels a real subscription via the Stripe cancel flag, not Cashier cancel()', function () {
        $this->business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'sub_fakeCancel',
            'stripe_status' => 'active',
            'stripe_price' => 'price_fake',
            'quantity' => 1,
        ]);
        $this->business->users()->updateExistingPivot($this->owner->id, ['lien_waiver_seat_at' => now()]);

        $endsAt = now()->addDays(20)->getTimestamp();
        $stripeSub = Stripe\Subscription::constructFrom(['cancel_at' => $endsAt]);

        $seats = Mockery::mock(WaiverSeats::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $seats->shouldReceive('pushStripeCancelFlag')->once()
            ->with(Mockery::type(Laravel\Cashier\Subscription::class), true)
            ->andReturn($stripeSub);

        $seats->cancel($this->business);

        $sub = WaiverEntitlements::subscription($this->business->refresh());
        expect($sub->onGracePeriod())->toBeTrue();
        expect($sub->ends_at->getTimestamp())->toBe($endsAt);
        // Access continues through the grace period.
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->owner))->toBeTrue();
    });

    it('resumes a grace-period subscription via the Stripe flag and clears ends_at', function () {
        $this->business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'sub_fakeResume',
            'stripe_status' => 'active',
            'stripe_price' => 'price_fake',
            'quantity' => 1,
            'ends_at' => now()->addDays(10),
        ]);
        $this->business->users()->updateExistingPivot($this->owner->id, ['lien_waiver_seat_at' => now()]);

        $seats = Mockery::mock(WaiverSeats::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $seats->shouldReceive('pushStripeCancelFlag')->once()
            ->with(Mockery::type(Laravel\Cashier\Subscription::class), false)
            ->andReturn(Stripe\Subscription::constructFrom([]));

        $seats->resume($this->business);

        $sub = $this->business->refresh()->subscription(config('lien_waivers.subscription_type'));
        expect($sub->ends_at)->toBeNull();
        expect($sub->onGracePeriod())->toBeFalse();
    });
});
