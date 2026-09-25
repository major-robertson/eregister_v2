<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Enums\PaymentStatus;
use App\Mail\RegistrationUnfinishedReminder;
use App\Models\EmailSequence;
use App\Models\Payment;
use App\Models\User;
use App\Services\StripeRefundRecorder;
use Illuminate\Support\Facades\Mail;

/*
| EREG-21: refunds made in Stripe (dashboard or admin button) reach our
| payments through the charge.refunded webhook, and payments:sync-refunds
| catches up older ones.
*/

if (! function_exists('postRefundWebhook')) {
    /** Post a signed charge.refunded event for a charge. */
    function postRefundWebhook(\Illuminate\Foundation\Testing\TestCase $test, array $charge): \Illuminate\Testing\TestResponse
    {
        $payload = [
            'id' => 'evt_'.uniqid(),
            'object' => 'event',
            'type' => 'charge.refunded',
            'data' => ['object' => $charge + ['object' => 'charge', 'metadata' => []]],
        ];

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.json_encode($payload), config('cashier.webhook.secret'));

        return $test->postJson(route('webhooks.stripe'), $payload, ['Stripe-Signature' => "t={$timestamp},v1={$signature}"]);
    }
}

beforeEach(function () {
    config(['cashier.webhook.secret' => 'whsec_test_secret']);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create();
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $project = LienProject::factory()->forBusiness($this->business)->create();
    $this->filing = LienFiling::factory()->forProject($project)->paid()->create([
        'document_type_id' => LienDocumentType::first()->id,
    ]);

    $this->payment = Payment::factory()->forPurchasable($this->filing)->succeeded()->create([
        'stripe_payment_intent_id' => 'pi_refund_test',
        'stripe_charge_id' => 'ch_refund_test',
        'amount_cents' => 29900,
    ]);
});

describe('the charge.refunded webhook', function () {
    it('marks a fully refunded lien payment and its filing refunded', function () {
        postRefundWebhook($this, [
            'id' => 'ch_refund_test',
            'payment_intent' => 'pi_refund_test',
            'amount' => 29900,
            'amount_refunded' => 29900,
            'refunded' => true,
            'refunds' => ['object' => 'list', 'data' => [['id' => 're_full', 'object' => 'refund', 'created' => now()->subMinute()->timestamp]]],
        ])->assertOk();

        $payment = $this->payment->fresh();

        expect($payment->status)->toBe(PaymentStatus::Refunded)
            ->and($payment->stripe_refund_id)->toBe('re_full')
            ->and($payment->refunded_at)->not->toBeNull()
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::Refunded)
            ->and($this->filing->events()->where('event_type', 'payment_refunded')->count())->toBe(1);
    });

    it('leaves a payment the admin Refund button already recorded alone', function () {
        $admin = User::factory()->create();
        $this->payment->update(['status' => PaymentStatus::Refunded, 'stripe_refund_id' => 're_admin', 'refunded_at' => now(), 'refunded_by' => $admin->id]);

        postRefundWebhook($this, [
            'id' => 'ch_refund_test',
            'payment_intent' => 'pi_refund_test',
            'amount' => 29900,
            'amount_refunded' => 29900,
            'refunded' => true,
        ])->assertOk();

        $payment = $this->payment->fresh();

        expect($payment->stripe_refund_id)->toBe('re_admin')
            ->and($payment->refunded_by)->toBe($admin->id)
            ->and($this->filing->events()->where('event_type', 'payment_refunded')->count())->toBe(0);
    });

    it('records a partial refund without marking the payment refunded', function () {
        postRefundWebhook($this, [
            'id' => 'ch_refund_test',
            'payment_intent' => 'pi_refund_test',
            'amount' => 29900,
            'amount_refunded' => 10000,
            'refunded' => false,
        ])->assertOk();

        $payment = $this->payment->fresh();

        expect($payment->status)->toBe(PaymentStatus::Succeeded)
            ->and($payment->meta['refunded_cents'])->toBe(10000)
            ->and($this->filing->fresh()->status)->toBe(FilingStatus::Paid);
    });

    it('finds the payment by charge id when there is no payment intent', function () {
        postRefundWebhook($this, [
            'id' => 'ch_refund_test',
            'payment_intent' => null,
            'amount' => 29900,
            'amount_refunded' => 29900,
            'refunded' => true,
        ])->assertOk();

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Refunded);
    });

    it('ignores refunds on charges that are not ours', function () {
        postRefundWebhook($this, [
            'id' => 'ch_old_app',
            'payment_intent' => 'pi_old_app',
            'amount' => 29700,
            'amount_refunded' => 29700,
            'refunded' => true,
        ])->assertOk()->assertSee('Charge not ours');

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
    });

    it('stops the finish-the-questions emails for a refunded sales tax registration', function () {
        Mail::fake();

        $application = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
            'paid_at' => now(),
        ]);
        Payment::factory()->forPurchasable($application)->succeeded()->create([
            'stripe_payment_intent_id' => 'pi_sales_tax',
            'amount_cents' => 19900,
        ]);
        $sequence = EmailSequence::create([
            'user_id' => $this->user->id,
            'business_id' => $this->business->id,
            'sequence_type' => 'registration_unfinished',
            'sequenceable_type' => $application->getMorphClass(),
            'sequenceable_id' => $application->id,
            'customer_type' => 'new',
            'resume_url' => 'https://example.test/resume',
            'next_send_at' => now()->subMinute(),
        ]);

        postRefundWebhook($this, [
            'id' => 'ch_sales_tax',
            'payment_intent' => 'pi_sales_tax',
            'amount' => 19900,
            'amount_refunded' => 19900,
            'refunded' => true,
        ])->assertOk();

        $this->artisan('email:process-sequences')->assertSuccessful();

        expect($sequence->fresh()->suppression_reason)->toBe('refunded');
        Mail::assertNotQueued(RegistrationUnfinishedReminder::class);
    });
});

describe('catching up older refunds', function () {
    it('sums refunds per charge and skips charges that are not ours', function () {
        $refunds = [
            (object) ['id' => 're_a', 'amount' => 20000, 'charge' => 'ch_refund_test', 'payment_intent' => 'pi_refund_test', 'status' => 'succeeded', 'created' => now()->subDays(3)->timestamp],
            (object) ['id' => 're_b', 'amount' => 9900, 'charge' => 'ch_refund_test', 'payment_intent' => 'pi_refund_test', 'status' => 'succeeded', 'created' => now()->subDays(2)->timestamp],
            (object) ['id' => 're_failed', 'amount' => 5000, 'charge' => 'ch_refund_test', 'payment_intent' => 'pi_refund_test', 'status' => 'failed', 'created' => now()->subDay()->timestamp],
            (object) ['id' => 're_old', 'amount' => 29700, 'charge' => 'ch_old_app', 'payment_intent' => 'pi_old_app', 'status' => 'succeeded', 'created' => now()->subDay()->timestamp],
        ];

        $result = app(StripeRefundRecorder::class)->sync($refunds);

        expect($result['not_ours'])->toBe(1)
            ->and($result['rows'])->toHaveCount(1)
            ->and($result['rows'][0]['outcome'])->toBe(StripeRefundRecorder::FULL)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Refunded)
            ->and($this->payment->fresh()->stripe_refund_id)->toBe('re_b');
    });

    it('changes nothing on a dry run', function () {
        $refunds = [
            (object) ['id' => 're_a', 'amount' => 29900, 'charge' => 'ch_refund_test', 'payment_intent' => 'pi_refund_test', 'status' => 'succeeded', 'created' => now()->timestamp],
        ];

        $result = app(StripeRefundRecorder::class)->sync($refunds, dryRun: true);

        expect($result['rows'][0]['outcome'])->toBe(StripeRefundRecorder::FULL)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
    });
});

describe('refunded applications', function () {
    it('counts an application as refunded only when no payment still stands', function () {
        $application = FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'submitted',
            'current_phase' => 'review',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
            'paid_at' => now(),
        ]);

        // Paid with no payment rows (older data): not refunded.
        expect($application->isRefunded())->toBeFalse();

        Payment::factory()->forPurchasable($application)->create(['status' => PaymentStatus::Refunded]);
        expect($application->isRefunded())->toBeTrue()
            ->and(FormApplication::query()->notRefunded()->whereKey($application->id)->exists())->toBeFalse();

        // Another payment still stands (an LLC renewal refunded on its own, say).
        Payment::factory()->forPurchasable($application)->succeeded()->create();
        expect($application->isRefunded())->toBeFalse()
            ->and(FormApplication::query()->notRefunded()->whereKey($application->id)->exists())->toBeTrue();
    });
});
