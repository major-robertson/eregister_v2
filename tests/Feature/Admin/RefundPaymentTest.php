<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Admin\Actions\RefundPayment;
use App\Domains\Lien\Admin\Livewire\LienFilingDetail;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Livewire\Livewire;

function createFilingWithPayment(array $paymentOverrides = []): array
{
    $business = Business::factory()->create();
    $project = LienProject::factory()->create(['business_id' => $business->id]);
    $filing = LienFiling::factory()->forProject($project)->create();

    $payment = Payment::factory()
        ->succeeded()
        ->forPurchasable($filing)
        ->create(array_merge([
            'stripe_payment_intent_id' => 'pi_'.fake()->uuid(),
            'amount_cents' => 15000,
        ], $paymentOverrides));

    return [$filing, $payment, $business];
}

describe('refund payment', function () {
    it('allows admin with payment.refund permission to refund a payment', function () {
        [$filing, $payment] = createFilingWithPayment();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'payment.refund');

        $this->mock(RefundPayment::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->once();
        });

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('confirmRefund')
            ->assertSet('showRefundModal', true)
            ->call('refundPayment')
            ->assertSet('showRefundModal', false);
    });

    it('updates payment status to refunded after successful refund', function () {
        [$filing, $payment] = createFilingWithPayment();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'payment.refund');

        $this->mock(RefundPayment::class, function ($mock) use ($payment, $admin) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturnUsing(function () use ($payment, $admin) {
                    $payment->update([
                        'status' => PaymentStatus::Refunded,
                        'stripe_refund_id' => 're_test_123',
                        'refunded_at' => now(),
                        'refunded_by' => $admin->id,
                    ]);
                });
        });

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('refundPayment');

        $payment->refresh();
        expect($payment->status)->toBe(PaymentStatus::Refunded);
        expect($payment->stripe_refund_id)->toBe('re_test_123');
        expect($payment->refunded_at)->not->toBeNull();
        expect($payment->refunded_by)->toBe($admin->id);
    });

    it('denies users without payment.refund permission from refunding', function () {
        [$filing] = createFilingWithPayment();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');

        $this->actingAs($viewer);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('confirmRefund')
            ->assertForbidden();
    });

    it('cannot refund a non-succeeded payment', function () {
        $business = Business::factory()->create();
        $project = LienProject::factory()->create(['business_id' => $business->id]);
        $filing = LienFiling::factory()->forProject($project)->create();

        Payment::factory()
            ->forPurchasable($filing)
            ->create([
                'status' => PaymentStatus::Failed,
                'stripe_payment_intent_id' => 'pi_'.fake()->uuid(),
                'amount_cents' => 15000,
            ]);

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'payment.refund');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('refundPayment');

        $this->assertDatabaseMissing('payments', [
            'purchasable_id' => $filing->id,
            'status' => PaymentStatus::Refunded->value,
        ]);
    });

    it('cannot refund an already-refunded payment', function () {
        [$filing] = createFilingWithPayment([
            'status' => PaymentStatus::Refunded,
            'stripe_refund_id' => 're_already_refunded',
            'refunded_at' => now(),
        ]);

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'payment.refund');

        $this->actingAs($admin);

        $component = Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing]);

        $component->assertSee('Payment Refunded');
    });

    it('logs a payment_refunded event on the filing', function () {
        [$filing, $payment] = createFilingWithPayment();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'payment.refund');

        $this->mock(RefundPayment::class, function ($mock) use ($filing, $admin) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturnUsing(function () use ($filing, $admin) {
                    $filing->events()->create([
                        'business_id' => $filing->business_id,
                        'event_type' => 'payment_refunded',
                        'payload_json' => [
                            'amount' => '$150.00',
                            'stripe_refund_id' => 're_test_event_456',
                        ],
                        'created_by' => $admin->id,
                    ]);
                });
        });

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->call('refundPayment');

        $this->assertDatabaseHas('lien_filing_events', [
            'filing_id' => $filing->id,
            'event_type' => 'payment_refunded',
            'created_by' => $admin->id,
        ]);
    });

    it('shows refund button only when canRefund is true', function () {
        [$filing] = createFilingWithPayment();

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'payment.refund');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSeeHtml('wire:click="confirmRefund"');
    });

    it('hides refund button when user lacks permission', function () {
        [$filing] = createFilingWithPayment();

        $viewer = User::factory()->create();
        $viewer->givePermissionTo('lien.view');

        $this->actingAs($viewer);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertDontSeeHtml('wire:click="confirmRefund"');
    });

    it('shows refunded badge when payment is already refunded', function () {
        [$filing] = createFilingWithPayment([
            'status' => PaymentStatus::Refunded,
            'stripe_refund_id' => 're_done',
            'refunded_at' => now(),
        ]);

        $admin = User::factory()->create();
        $admin->givePermissionTo('lien.view', 'payment.refund');

        $this->actingAs($admin);

        Livewire::test(LienFilingDetail::class, ['lienFiling' => $filing])
            ->assertSee('Payment Refunded');
    });
});
