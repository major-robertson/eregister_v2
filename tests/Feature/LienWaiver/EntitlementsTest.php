<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverEntitlements;

if (! function_exists('waiverEntSubscribe')) {
    /** Give a business an active lien-waiver subscription (stub row, no Stripe). */
    function waiverEntSubscribe(Business $business): void
    {
        $business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'stub_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'stub_price',
            'quantity' => 1,
        ]);
    }
}

beforeEach(function () {
    // Pin mid-month so calendar-month metering can't straddle a boundary.
    $this->travelTo(now()->startOfMonth()->addDays(14)->setTime(12, 0));

    $this->business = Business::factory()->create();
    $this->project = LienProject::factory()->forBusiness($this->business)->inState('TX')->create();
});

describe('free tier metering', function () {
    it('exposes the configured monthly free-save limit', function () {
        expect(WaiverEntitlements::freeSavesLimit())
            ->toBe(4)
            ->toBe((int) config('lien_waivers.free_saved_waivers_per_month'));
    });

    it('allows saving below the cap and does the remaining math', function () {
        LienWaiver::factory()->count(3)->forProject($this->project)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(3);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(1);
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeTrue();
    });

    it('blocks the free tier at the cap and never reports negative remaining saves', function () {
        LienWaiver::factory()->count(4)->forProject($this->project)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(4);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(0);
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeFalse();

        // Over the cap (e.g. saves that predate a downgrade) still floors at 0.
        LienWaiver::factory()->forProject($this->project)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(5);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(0);
    });

    it('counts voided waivers against the cap: the save consumed the slot', function () {
        LienWaiver::factory()->count(4)->forProject($this->project)->create([
            'status' => WaiverStatus::Voided,
            'voided_at' => now(),
        ]);

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(4);
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeFalse();
    });

    it('counts soft-deleted waivers against the cap', function () {
        LienWaiver::factory()->count(4)->forProject($this->project)->create()
            ->each(fn (LienWaiver $waiver) => $waiver->delete());

        // Gone from normal queries, but the slots stay consumed.
        expect(LienWaiver::where('business_id', $this->business->id)->count())->toBe(0);
        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(4);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(0);
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeFalse();
    });

    it('meters only the current calendar month', function () {
        LienWaiver::factory()->count(4)->forProject($this->project)->create([
            'created_at' => now()->subMonth(),
        ]);

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(0);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(4);
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeTrue();
    });

    it("ignores another business's waivers", function () {
        $otherBusiness = Business::factory()->create();
        $otherProject = LienProject::factory()->forBusiness($otherBusiness)->inState('TX')->create();
        LienWaiver::factory()->count(4)->forProject($otherProject)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(0);
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeTrue();
        expect(WaiverEntitlements::canSaveWaiver($otherBusiness))->toBeFalse();
    });
});

describe('paid access', function () {
    it('has no paid access without a lien_waiver subscription', function () {
        expect(WaiverEntitlements::hasPaidAccess($this->business))->toBeFalse();

        // A subscription of a different type does not bleed over.
        subscribeToResaleCerts($this->business);

        expect($this->business->refresh()->subscribed(config('resale_cert.subscription_type')))->toBeTrue();
        expect(WaiverEntitlements::hasPaidAccess($this->business))->toBeFalse();
    });

    it('grants paid access with an active lien_waiver subscription and lifts the save cap', function () {
        waiverEntSubscribe($this->business);

        expect(WaiverEntitlements::hasPaidAccess($this->business))->toBeTrue();

        // Way past the free cap; paid saves are unlimited.
        LienWaiver::factory()->count(6)->forProject($this->project)->create();

        expect(WaiverEntitlements::savedThisMonth($this->business))->toBe(6);
        expect(WaiverEntitlements::remainingFreeSaves($this->business))->toBe(0);
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeTrue();
    });

    it('canUseEsign mirrors hasPaidAccess: there is no free e-sign allowance', function () {
        // Free business, plenty of free saves left: can save, cannot e-sign.
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeTrue();
        expect(WaiverEntitlements::canUseEsign($this->business))->toBeFalse();

        waiverEntSubscribe($this->business);

        // hasPaidAccess above cached the (then-empty) subscriptions relation.
        expect(WaiverEntitlements::hasPaidAccess($this->business->refresh()))->toBeTrue();
        expect(WaiverEntitlements::canUseEsign($this->business))->toBeTrue();
    });
});
