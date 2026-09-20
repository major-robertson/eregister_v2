<?php

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Livewire\SignDone;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Esign\LienWaiverSignable;
use App\Domains\Lien\Livewire\Waivers\WaiverShow;
use App\Domains\Lien\Livewire\Waivers\WaiverSubscriptionCheckout;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\Services\WaiverPaymentService;
use App\Mail\WaiverSignatureInvitation;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Stripe\StripeObject;

/*
| Free to create, pay to sign: creating, saving, downloading, and uploading a
| paper-signed copy are free; e-signature (signing your own waiver, sending one
| for signature) takes a Lien Waiver Pro seat at $49/mo or $490/yr.
*/

if (! function_exists('payToSignSubscribe')) {
    /** Active stub Pro subscription (no Stripe) with seats for the given members. */
    function payToSignSubscribe(Business $business, User ...$seatHolders): void
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

if (! function_exists('payToSignWaiver')) {
    /**
     * A generated waiver with its frozen render snapshot, ready to send. CO
     * uses the generic house forms with e-sign allowed.
     */
    function payToSignWaiver(Business $business, User $creator, bool $collect = false, string $state = 'CO', array $overrides = []): LienWaiver
    {
        $project = LienProject::factory()->forBusiness($business)->inState($state)->create([
            'wizard_completed_at' => now(),
        ]);

        $factory = LienWaiver::factory()->forProject($project);

        if ($collect) {
            $factory = $factory->collect();
        }

        $waiver = $factory->generated()->create(array_merge([
            'created_by_user_id' => $creator->id,
        ], $overrides));

        $waiver->update(['render_snapshot_json' => app(WaiverGenerator::class)->data($waiver)]);

        // The unsigned PDF the download button serves (faked S3).
        $waiver->addMediaFromString('%PDF-1.4 fake unsigned')
            ->usingFileName('waiver.pdf')
            ->toMediaCollection('generated');

        return $waiver->fresh();
    }
}

if (! function_exists('payToSignRequest')) {
    /** Fabricate a signature request on a waiver without rendering any PDF. */
    function payToSignRequest(LienWaiver $waiver, array $overrides = []): SignatureRequest
    {
        return SignatureRequest::create(array_merge([
            'signable_type' => 'lien_waiver',
            'signable_id' => $waiver->id,
            'business_id' => $waiver->business_id,
            'signer_user_id' => null,
            'document_signing_policy_key' => LienWaiverSignable::DOCUMENT_TYPE,
            'status' => SignatureRequestStatus::AwaitingSignature,
            'signer_name_snapshot' => $waiver->signer_name ?? $waiver->counterparty_name ?? 'Signer',
            'signer_email_snapshot' => $waiver->signer_email ?? $waiver->counterparty_email ?? 'signer@example.com',
            'invited_at' => now(),
            'expires_at' => now()->addDays(14),
        ], $overrides));
    }
}

beforeEach(function () {
    $this->travelTo(now()->startOfMonth()->addDays(14)->setTime(12, 0));

    Storage::fake('s3');
    Mail::fake();

    $this->user = User::factory()->create(['email_verified_at' => now()]);
    $this->business = Business::factory()->create([
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

describe('waiver page: e-sign is the Pro step', function () {
    it('pitches Pro to a free user instead of sending, and leaves the waiver untouched', function () {
        $waiver = payToSignWaiver($this->business, $this->user);

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->assertSee('Download unsigned PDF')
            ->assertSee('Sign & send')
            ->assertSee('$49/month per seat')
            ->call('sendForSignature')
            ->assertSet('showUpsellModal', true)
            ->assertNoRedirect();

        expect($waiver->fresh()->status)->toBe(WaiverStatus::Generated);
        expect($waiver->latestSignatureRequest())->toBeNull();
        Mail::assertNothingQueued();
    });

    it('carries the waiver into checkout from the upsell', function () {
        $waiver = payToSignWaiver($this->business, $this->user);

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->assertSee(route('lien.waivers.subscribe', ['waiver' => $waiver->public_id]), false);
    });

    it('takes a Pro user straight into signing their own waiver', function () {
        payToSignSubscribe($this->business, $this->user);
        $waiver = payToSignWaiver($this->business, $this->user);

        $component = Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->assertDontSee('$49/month per seat')
            ->assertSee('Download PDF')
            ->call('sendForSignature');

        $request = $waiver->fresh()->latestSignatureRequest();

        expect($request)->not->toBeNull();
        expect($request->signer_user_id)->toBe($this->user->id);
        expect($waiver->fresh()->status)->toBe(WaiverStatus::AwaitingSignature);

        // Signed URL into the ceremony, not a trip to the inbox.
        $component->assertRedirectContains('/esign/'.$request->public_id)
            ->assertRedirectContains('signature=');

        // The invitation still goes out as a way back in.
        Mail::assertQueued(WaiverSignatureInvitation::class, fn ($mail) => $mail->hasTo($this->user->email));
    });

    it('sends a Pro collect waiver to the counterparty and stays on the page', function () {
        payToSignSubscribe($this->business, $this->user);
        $waiver = payToSignWaiver($this->business, $this->user, collect: true, overrides: [
            'signer_name' => 'Vera Vendor',
            'signer_email' => 'vera@vendor.test',
        ]);

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->assertSee('Send for signature')
            ->call('sendForSignature')
            ->assertNoRedirect();

        expect($waiver->fresh()->status)->toBe(WaiverStatus::AwaitingSignature);
        expect($waiver->fresh()->latestSignatureRequest()->signer_user_id)->toBeNull();
        Mail::assertQueued(WaiverSignatureInvitation::class, fn ($mail) => $mail->hasTo('vera@vendor.test'));
    });

    it("doesn't give a teammate without a seat e-sign on a subscribed business", function () {
        $seatless = User::factory()->create(['email_verified_at' => now()]);
        $this->business->users()->attach($seatless, ['role' => 'member']);
        payToSignSubscribe($this->business, $this->user);

        $waiver = payToSignWaiver($this->business, $seatless);

        $this->actingAs($seatless);

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->call('sendForSignature')
            ->assertSet('showUpsellModal', true);

        expect($waiver->fresh()->status)->toBe(WaiverStatus::Generated);
    });

    it('offers "Sign now" while the request waits on the viewer, not on a counterparty', function () {
        $own = payToSignWaiver($this->business, $this->user, overrides: [
            'status' => WaiverStatus::AwaitingSignature,
            'sent_at' => now(),
        ]);
        payToSignRequest($own, ['signer_user_id' => $this->user->id]);

        $component = Livewire::test(WaiverShow::class, ['waiver' => $own])
            ->assertSee('Waiting on your signature')
            ->assertSee('Sign now');

        expect($component->viewData('signNowUrl'))->toContain('/esign/')->toContain('signature=');

        $collected = payToSignWaiver($this->business, $this->user, collect: true, overrides: [
            'status' => WaiverStatus::AwaitingSignature,
            'sent_at' => now(),
            'signer_email' => 'vera@vendor.test',
        ]);
        payToSignRequest($collected);

        $component = Livewire::test(WaiverShow::class, ['waiver' => $collected])
            ->assertSee('Waiting on the signer')
            ->assertDontSee('Sign now');

        expect($component->viewData('signNowUrl'))->toBeNull();
    });

    it("doesn't pitch e-sign where the state requires signing on paper", function () {
        $waiver = payToSignWaiver($this->business, $this->user, state: 'GA');

        Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->assertSee('Sign on paper, then upload')
            ->assertDontSee('Sign & send')
            ->assertDontSee('$49/month per seat');
    });
});

describe('checkout returns to the waiver being signed', function () {
    beforeEach(function () {
        config(['cashier.secret' => '']);
    });

    it('remembers the waiver from the upsell and offers it on the success page', function () {
        $waiver = payToSignWaiver($this->business, $this->user);

        // Phase 1 remembers it; the seat confirmation reload drops the query string.
        Livewire::withQueryParams(['waiver' => $waiver->public_id])
            ->test(WaiverSubscriptionCheckout::class)
            ->assertSet('waiver', $waiver->public_id);

        expect(session(WaiverSubscriptionCheckout::RETURN_WAIVER_SESSION_KEY))->toBe($waiver->public_id);

        // Phase 2 (stub path) activates the subscription.
        Livewire::withQueryParams(['interval' => 'monthly', 'seats' => (string) $this->user->id])
            ->test(WaiverSubscriptionCheckout::class)
            ->assertRedirect(route('lien.waivers.payment-confirmation'));

        $this->get(route('lien.waivers.payment-confirmation'))
            ->assertSuccessful()
            ->assertSee('Back to your waiver')
            ->assertSee(route('lien.waivers.show', $waiver), false);

        // Pulled: a later visit is a plain receipt.
        expect(session(WaiverSubscriptionCheckout::RETURN_WAIVER_SESSION_KEY))->toBeNull();

        $this->get(route('lien.waivers.payment-confirmation'))
            ->assertSuccessful()
            ->assertDontSee('Back to your waiver')
            ->assertSee('Go to Lien Waivers');
    });

    it("ignores another business's waiver id", function () {
        $otherBusiness = Business::factory()->create();
        $otherUser = User::factory()->create();
        $otherBusiness->users()->attach($otherUser, ['role' => 'owner']);
        $foreign = payToSignWaiver($otherBusiness, $otherUser);

        Livewire::withQueryParams(['waiver' => $foreign->public_id])
            ->test(WaiverSubscriptionCheckout::class);

        expect(session(WaiverSubscriptionCheckout::RETURN_WAIVER_SESSION_KEY))->toBeNull();
    });

    it('charges the current $49 monthly and $490 yearly per-seat prices', function () {
        Livewire::test(WaiverSubscriptionCheckout::class)
            ->assertSet('unitAmountCents', 4900);

        Livewire::withQueryParams(['interval' => 'yearly'])
            ->test(WaiverSubscriptionCheckout::class)
            ->assertSet('unitAmountCents', 49000);
    });
});

describe('after signing your own waiver', function () {
    it('says where the signed copy went and links back to the waiver', function () {
        $waiver = payToSignWaiver($this->business, $this->user, overrides: [
            'status' => WaiverStatus::Signed,
            'signed_at' => now(),
            'counterparty_email' => 'gc@builder.test',
        ]);
        $request = payToSignRequest($waiver, [
            'signer_user_id' => $this->user->id,
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
        ]);

        Livewire::test(SignDone::class, ['request' => $request])
            ->assertSee('stored on the waiver')
            ->assertSee('gc@builder.test')
            ->assertSee('Back to the waiver')
            ->assertSee(route('lien.waivers.show', $waiver), false)
            ->assertDontSee('our team has been notified');
    });

    it('tells them to forward it when the other party has no email on file', function () {
        $waiver = payToSignWaiver($this->business, $this->user, overrides: [
            'status' => WaiverStatus::Signed,
            'signed_at' => now(),
            'counterparty_email' => null,
        ]);
        $request = payToSignRequest($waiver, [
            'signer_user_id' => $this->user->id,
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => now(),
        ]);

        Livewire::test(SignDone::class, ['request' => $request])
            ->assertSee('send it on to them')
            ->assertSee('Back to the waiver');
    });
});

describe('repricing keeps launch-price history intact', function () {
    it('ships the $49 / $490 prices active and the $99 / $990 launch prices inactive', function () {
        $rows = Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->get()
            ->keyBy('variant_key');

        expect($rows->keys()->sort()->values()->all())
            ->toBe(['monthly', 'monthly_launch_99', 'yearly', 'yearly_launch_990']);

        expect($rows['monthly']->amount_cents)->toBe(4900)
            ->and($rows['monthly']->active)->toBeTrue()
            ->and($rows['monthly']->stripe_price_id_live)->toBe('price_1UHkwpCWSBPRiUNwlTmD7jdG')
            ->and($rows['monthly']->stripe_price_id_test)->toBe('price_1UHkvVCWSBPRiUNwx4OvfQi4');

        expect($rows['yearly']->amount_cents)->toBe(49000)
            ->and($rows['yearly']->active)->toBeTrue()
            ->and($rows['yearly']->stripe_price_id_live)->toBe('price_1UHkxLCWSBPRiUNwDz8OF8SM')
            ->and($rows['yearly']->stripe_price_id_test)->toBe('price_1UHkvjCWSBPRiUNwBbqttnxf');

        expect($rows['monthly_launch_99']->amount_cents)->toBe(9900)
            ->and($rows['monthly_launch_99']->active)->toBeFalse();
        expect($rows['yearly_launch_990']->amount_cents)->toBe(99000)
            ->and($rows['yearly_launch_990']->active)->toBeFalse();

        // Checkout resolves only the active rows.
        expect(app(WaiverPaymentService::class)->price('monthly')->id)->toBe($rows['monthly']->id);
        expect(app(WaiverPaymentService::class)->price('yearly')->id)->toBe($rows['yearly']->id);
    });

    it('migrates a launch-era database: old rows keep their ids (and payments), new rows are added', function () {
        $waiverPrices = fn () => Price::query()
            ->where('product_family', 'lien')
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription');

        // Rebuild the table as production has it today: only monthly/yearly,
        // at the launch amounts and Stripe ids.
        $waiverPrices()->delete();

        $launch = [
            'monthly' => [9900, 'month', 'price_1TuzJ9CWSBPRiUNwflSmXKDP', 'price_1TuzJeCWSBPRiUNwRqbKd1VI'],
            'yearly' => [99000, 'year', 'price_1TuzJ9CWSBPRiUNwSkcStRZX', 'price_1TuzJeCWSBPRiUNwppS4jfV2'],
        ];

        foreach ($launch as $variant => [$cents, $interval, $test, $live]) {
            Price::create([
                'product_family' => 'lien',
                'product_key' => 'lien_waiver',
                'variant_key' => $variant,
                'billing_type' => 'subscription',
                'amount_cents' => $cents,
                'currency' => 'usd',
                'interval' => $interval,
                'interval_count' => 1,
                'stripe_price_id_test' => $test,
                'stripe_price_id_live' => $live,
                'active' => true,
            ]);
        }

        $oldMonthlyId = $waiverPrices()->where('variant_key', 'monthly')->value('id');
        $oldYearlyId = $waiverPrices()->where('variant_key', 'yearly')->value('id');

        $paidAtLaunch = Payment::factory()->succeeded()->create([
            'business_id' => $this->business->id,
            'price_id' => $oldMonthlyId,
            'amount_cents' => 9900,
        ]);

        $migration = require database_path('migrations/2026_09_20_000001_reprice_lien_waiver_subscription.php');
        $migration->up();
        // Safe to run again (a redeploy, or a fresh database the seeder already filled).
        $migration->up();

        $rows = $waiverPrices()->get()->keyBy('variant_key');

        expect($rows)->toHaveCount(4);

        // The launch rows are the ORIGINAL rows, retired: history still points at $99.
        expect($rows['monthly_launch_99']->id)->toBe($oldMonthlyId)
            ->and($rows['monthly_launch_99']->amount_cents)->toBe(9900)
            ->and($rows['monthly_launch_99']->active)->toBeFalse();
        expect($rows['yearly_launch_990']->id)->toBe($oldYearlyId)
            ->and($rows['yearly_launch_990']->active)->toBeFalse();
        expect($paidAtLaunch->fresh()->price->amount_cents)->toBe(9900);

        // The current prices are new rows.
        expect($rows['monthly']->id)->not->toBe($oldMonthlyId)
            ->and($rows['monthly']->amount_cents)->toBe(4900)
            ->and($rows['monthly']->active)->toBeTrue();
        expect($rows['yearly']->id)->not->toBe($oldYearlyId)
            ->and($rows['yearly']->amount_cents)->toBe(49000)
            ->and($rows['yearly']->active)->toBeTrue();
    });

    it('books a renewal against the price the subscription is actually on', function () {
        $rows = Price::query()
            ->where('product_key', 'lien_waiver')
            ->where('billing_type', 'subscription')
            ->get()
            ->keyBy('variant_key');

        // A launch subscriber renewing two seats at $99: no row is $198, so the
        // amount alone can't place it; the subscription's Stripe price does.
        payToSignSubscribe($this->business, $this->user);
        $this->business->subscription(config('lien_waivers.subscription_type'))
            ->update(['stripe_price' => $rows['monthly_launch_99']->stripePriceId(), 'quantity' => 2]);

        app(WaiverPaymentService::class)->recordRenewalPayment(StripeObject::constructFrom([
            'id' => 'in_launch_renewal',
            'amount_paid' => 19800,
            'currency' => 'usd',
            'subscription' => 'sub_launch',
        ]), $this->business->refresh());

        expect(Payment::where('stripe_invoice_id', 'in_launch_renewal')->firstOrFail()->price_id)
            ->toBe($rows['monthly_launch_99']->id);

        // A new subscriber renewing ten seats at $49 is $490: the same total as
        // one yearly seat, and still booked as monthly.
        $newBusiness = Business::factory()->create();
        $newOwner = User::factory()->create();
        $newBusiness->users()->attach($newOwner, ['role' => 'owner']);
        payToSignSubscribe($newBusiness, $newOwner);
        $newBusiness->subscription(config('lien_waivers.subscription_type'))
            ->update(['stripe_price' => $rows['monthly']->stripePriceId(), 'quantity' => 10]);

        app(WaiverPaymentService::class)->recordRenewalPayment(StripeObject::constructFrom([
            'id' => 'in_current_renewal',
            'amount_paid' => 49000,
            'currency' => 'usd',
            'subscription' => 'sub_current',
        ]), $newBusiness->refresh());

        expect(Payment::where('stripe_invoice_id', 'in_current_renewal')->firstOrFail()->price_id)
            ->toBe($rows['monthly']->id);
    });
});
