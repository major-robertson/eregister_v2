<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Models\User;

if (! function_exists('waiverEntSubscribe')) {
    /**
     * Give a business an active lien-waiver subscription (stub row, no
     * Stripe) with seats assigned to the given members.
     */
    function waiverEntSubscribe(Business $business, User ...$seatHolders): void
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
    // Pin mid-month so calendar-month metering can't straddle a boundary.
    $this->travelTo(now()->startOfMonth()->addDays(14)->setTime(12, 0));

    $this->business = Business::factory()->create();
    $this->project = LienProject::factory()->forBusiness($this->business)->inState('TX')->create();

    $this->user = User::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);
});

describe('free tier metering', function () {
    it('exposes the configured monthly free-save limit', function () {
        expect(WaiverEntitlements::freeSavesLimit())
            ->toBe(3)
            ->toBe((int) config('lien_waivers.free_saved_waivers_per_month'));
    });

    it('allows saving below the cap and does the remaining math', function () {
        LienWaiver::factory()->count(2)->forProject($this->project)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(2);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(1);
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeTrue();
    });

    it('blocks the free tier at the cap and never reports negative remaining saves', function () {
        LienWaiver::factory()->count(3)->forProject($this->project)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(3);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(0);
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeFalse();

        // Over the cap (e.g. saves that predate a downgrade) still floors at 0.
        LienWaiver::factory()->forProject($this->project)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(4);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(0);
    });

    it('counts voided waivers against the cap: the save consumed the slot', function () {
        LienWaiver::factory()->count(3)->forProject($this->project)->create([
            'status' => WaiverStatus::Voided,
            'voided_at' => now(),
        ]);

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(3);
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeFalse();
    });

    it('counts soft-deleted waivers against the cap', function () {
        LienWaiver::factory()->count(3)->forProject($this->project)->create()
            ->each(fn (LienWaiver $waiver) => $waiver->delete());

        // Gone from normal queries, but the slots stay consumed.
        expect(LienWaiver::where('business_id', $this->business->id)->count())->toBe(0);
        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(3);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(0);
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeFalse();
    });

    it('meters only the current calendar month', function () {
        LienWaiver::factory()->count(3)->forProject($this->project)->create([
            'created_at' => now()->subMonth(),
        ]);

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(0);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(3);
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeTrue();
    });

    it("ignores another business's waivers", function () {
        $otherBusiness = Business::factory()->create();
        $otherUser = User::factory()->create();
        $otherBusiness->users()->attach($otherUser, ['role' => 'owner']);
        $otherProject = LienProject::factory()->forBusiness($otherBusiness)->inState('TX')->create();
        LienWaiver::factory()->count(3)->forProject($otherProject)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(0);
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeTrue();
        expect(WaiverEntitlements::canSaveWaiver($otherBusiness, $otherUser))->toBeFalse();
    });
});

describe('per-seat paid access', function () {
    it('has no paid access without a lien_waiver subscription', function () {
        expect(WaiverEntitlements::isSubscribed($this->business))->toBeFalse();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->user))->toBeFalse();
        expect(WaiverEntitlements::seatLimit($this->business))->toBe(0);

        // A subscription of a different type does not bleed over.
        subscribeToResaleCerts($this->business);

        expect($this->business->refresh()->subscribed(config('resale_cert.subscription_type')))->toBeTrue();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->user))->toBeFalse();
    });

    it('grants seat holders unlimited saves; a subscription without a seat grants nothing', function () {
        $seatless = User::factory()->create();
        $this->business->users()->attach($seatless, ['role' => 'member']);

        waiverEntSubscribe($this->business, $this->user);

        expect(WaiverEntitlements::isSubscribed($this->business->refresh()))->toBeTrue();
        expect(WaiverEntitlements::seatLimit($this->business))->toBe(1);
        expect(WaiverEntitlements::assignedSeats($this->business))->toBe(1);

        expect(WaiverEntitlements::hasSeat($this->business, $this->user))->toBeTrue();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->user))->toBeTrue();

        expect(WaiverEntitlements::hasSeat($this->business, $seatless))->toBeFalse();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $seatless))->toBeFalse();

        // Way past the free cap: the seat holder is unlimited, the seatless
        // member is back on the (exhausted) free tier.
        LienWaiver::factory()->count(6)->forProject($this->project)->create();

        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeTrue();
        expect(WaiverEntitlements::canSaveWaiver($this->business, $seatless))->toBeFalse();
    });

    it('a seat without a live subscription grants nothing (lapsed plan)', function () {
        // Seat flag set but no subscription row at all.
        $this->business->users()->updateExistingPivot($this->user->id, ['lien_waiver_seat_at' => now()]);

        expect(WaiverEntitlements::hasSeat($this->business, $this->user))->toBeTrue();
        expect(WaiverEntitlements::hasPaidAccess($this->business, $this->user))->toBeFalse();
    });

    it('canManageSeats mirrors the owner/admin membership roles', function () {
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();
        $this->business->users()->attach($admin, ['role' => 'admin']);
        $this->business->users()->attach($member, ['role' => 'member']);

        expect(WaiverEntitlements::canManageSeats($this->business, $this->user))->toBeTrue();
        expect(WaiverEntitlements::canManageSeats($this->business, $admin))->toBeTrue();
        expect(WaiverEntitlements::canManageSeats($this->business, $member))->toBeFalse();
        expect(WaiverEntitlements::canManageSeats($this->business, $outsider))->toBeFalse();
    });

    it('canUseEsign takes a Pro seat: free is create and download, Pro is getting it signed', function () {
        $seatless = User::factory()->create();
        $this->business->users()->attach($seatless, ['role' => 'member']);

        // Free business: saving is allowed, e-sign is not.
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeTrue();
        expect(WaiverEntitlements::canUseEsign($this->business, $this->user))->toBeFalse();

        waiverEntSubscribe($this->business, $this->user);
        $this->business->refresh();

        // The seat holder can e-sign; a teammate without a seat still cannot.
        expect(WaiverEntitlements::canUseEsign($this->business, $this->user))->toBeTrue();
        expect(WaiverEntitlements::canUseEsign($this->business, $seatless))->toBeFalse();
    });
});
