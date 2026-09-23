<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Livewire\StateSelector;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\SalesTax\Livewire\RegistrationCheckout;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
use App\Enums\PaymentStatus;
use App\Mail\PaymentReceipt;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Stripe\PaymentIntent;

/**
 * A sales tax draft that has not started the questions: what the state
 * selector creates. Overrides let a test mark it paid, rushed or locked.
 *
 * @param  array<int, string>  $states
 * @param  array<string, mixed>  $overrides
 */
function orderFirstDraft(Business $business, User $user, array $states, array $overrides = []): FormApplication
{
    $application = FormApplication::create(array_merge([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $user->id,
    ], $overrides));

    foreach ($states as $code) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $code,
            'status' => 'pending',
            'data' => [],
        ]);
    }

    return $application;
}

beforeEach(function () {
    // No Stripe key: the checkout takes its keyless stub path instead of
    // creating real test-mode customers and PaymentIntents.
    config(['cashier.secret' => null]);

    $this->user = User::factory()->create();
    // Profile complete, so the portal middleware lets HTTP requests through.
    $this->business = Business::factory()->create(['onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->perState = Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'rush', 'billing_type' => 'one_time'],
        ['amount_cents' => 9900, 'currency' => 'usd', 'active' => true],
    );

    $this->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

describe('order before the questions', function () {
    it('sends a new draft to the order screen instead of the wizard', function () {
        $component = Livewire::test(StateSelector::class, ['formType' => 'sales_tax_permit'])
            ->call('toggleState', 'CA')
            ->call('proceed');

        $application = FormApplication::where('business_id', $this->business->id)->latest('id')->first();

        $component->assertRedirect(route('sales-tax.registrations.checkout', $application));
    });

    it('keeps the questions closed until the registration is paid', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA']);

        $this->get(route('sales-tax.registrations.show', $application))
            ->assertRedirect(route('sales-tax.registrations.checkout', $application));

        $application->update(['paid_at' => now()]);

        $this->get(route('sales-tax.registrations.show', $application))->assertOk();
    });

    it('resumes an unpaid draft at the order screen and a paid one in the wizard', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA']);

        expect($application->dashboard_action_label)->toBe('Continue')
            ->and($application->dashboard_action_url)->toBe(route('sales-tax.registrations.checkout', $application));

        $application->update(['paid_at' => now()]);

        expect($application->fresh()->dashboard_action_url)->toBe(route('sales-tax.registrations.show', $application));
    });

    it('lets an application with unanswered questions into checkout, but not a submitted one', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA']);

        expect($this->user->can('checkout', $application))->toBeTrue();

        $application->update(['status' => 'submitted', 'locked_at' => now()]);

        expect($this->user->can('checkout', $application->fresh()))->toBeFalse();
    });
});

describe('order screen', function () {
    it('lists the states with the per-state fee and adds rush processing on request', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA', 'TX']);

        Livewire::test(RegistrationCheckout::class, ['application' => $application])
            ->assertSet('step', 'order')
            ->assertSet('amountCents', 39800)
            ->assertSee('California registration')
            ->assertSee('Texas registration')
            ->assertSee('Rush')
            ->assertSee('$398.00')
            ->set('processing', 'rush')
            ->assertSet('amountCents', 49700)
            ->assertSee('$497.00')
            ->call('continueToPayment')
            ->assertRedirect(route('sales-tax.registrations.checkout', ['application' => $application, 'step' => 'pay']));

        expect($application->fresh()->rush_requested_at)->not->toBeNull();
    });

    it('clears a rush choice when the customer goes back to standard', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA'], ['rush_requested_at' => now()]);

        Livewire::test(RegistrationCheckout::class, ['application' => $application])
            ->assertSet('processing', 'rush')
            ->assertSet('amountCents', 29800)
            ->set('processing', 'standard')
            ->assertSet('amountCents', 19900)
            ->call('continueToPayment');

        expect($application->fresh()->rush_requested_at)->toBeNull();
    });

    it('skips the order screen for a draft that already answered everything', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA'], ['current_phase' => 'review']);
        $application->states()->update(['status' => 'complete', 'completed_at' => now()]);

        // Keyless test environment: the pay step runs the stub checkout,
        // which pays and, because nothing is left to answer, locks.
        Livewire::test(RegistrationCheckout::class, ['application' => $application])
            ->assertRedirect(route('sales-tax.registrations.payment-confirmation', $application));

        expect($application->fresh()->isLocked())->toBeTrue();
    });
});

describe('payment', function () {
    it('marks the registration paid and open for the questions, and remembers rush (stub checkout)', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA'], ['rush_requested_at' => now()]);

        Livewire::withQueryParams(['step' => 'pay'])
            ->test(RegistrationCheckout::class, ['application' => $application])
            ->assertRedirect(route('sales-tax.registrations.payment-confirmation', $application));

        $application->refresh();

        expect($application->paid_at)->not->toBeNull()
            ->and($application->locked_at)->toBeNull()
            ->and($application->status)->toBe('draft')
            ->and($application->rush_requested_at)->not->toBeNull();

        $payment = $application->payment;

        expect($payment->status)->toBe(PaymentStatus::Succeeded)
            ->and($payment->amount_cents)->toBe(29800)
            ->and($payment->meta['rush'])->toBeTrue()
            ->and($payment->meta['state_count'])->toBe(1);
    });

    it('marks a Stripe payment paid without locking a registration that still has questions', function () {
        Mail::fake();

        $application = orderFirstDraft($this->business, $this->user, ['CA', 'TX']);

        $payment = Payment::create([
            'purchasable_type' => $application->getMorphClass(),
            'purchasable_id' => $application->id,
            'business_id' => $this->business->id,
            'price_id' => $this->perState->id,
            'stripe_payment_intent_id' => 'pi_order_first',
            'amount_cents' => 49700,
            'currency' => 'usd',
            'status' => PaymentStatus::Initiated,
            'provider' => 'stripe',
            'livemode' => false,
            'meta' => ['state_count' => 2, 'per_state_cents' => 19900, 'rush' => true, 'rush_cents' => 9900],
        ]);

        $pi = PaymentIntent::constructFrom([
            'amount_received' => 49700,
            'currency' => 'usd',
            'latest_charge' => 'ch_order_first',
        ]);

        app(RegistrationPaymentService::class)->markSucceeded($payment, $pi);

        $application->refresh();

        expect($application->paid_at)->not->toBeNull()
            ->and($application->rush_requested_at)->not->toBeNull()
            ->and($application->locked_at)->toBeNull()
            ->and($application->status)->toBe('draft')
            ->and($application->stripe_payment_intent_id)->toBe('pi_order_first');
    });

    it('sends an already-paid application to the questions instead of charging again', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA'], ['paid_at' => now()]);

        Livewire::test(RegistrationCheckout::class, ['application' => $application])
            ->assertRedirect(route('sales-tax.registrations.show', $application));
    });

    it('itemizes the states and the rush fee on the receipt', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA', 'TX'], ['paid_at' => now()]);

        $payment = Payment::create([
            'purchasable_type' => $application->getMorphClass(),
            'purchasable_id' => $application->id,
            'business_id' => $this->business->id,
            'price_id' => $this->perState->id,
            'amount_cents' => 49700,
            'currency' => 'usd',
            'status' => PaymentStatus::Succeeded,
            'provider' => 'stripe',
            'livemode' => false,
            'paid_at' => now(),
            'meta' => ['state_count' => 2, 'per_state_cents' => 19900, 'rush' => true, 'rush_cents' => 9900],
        ]);

        $rendered = (new PaymentReceipt($payment))->render();

        expect($rendered)->toContain('2 states x $199.00')
            ->and($rendered)->toContain('$398.00')
            ->and($rendered)->toContain('Rush processing')
            ->and($rendered)->toContain('$99.00')
            ->and($rendered)->toContain('$497.00');
    });
});

describe('after payment', function () {
    it('tells a paid customer to start the questions', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA'], [
            'paid_at' => now(),
            'rush_requested_at' => now(),
        ]);

        $this->get(route('sales-tax.registrations.payment-confirmation', $application))
            ->assertOk()
            ->assertSee('Payment received. Now the questions.')
            ->assertSee('Start the questions')
            ->assertSee('Rush: filed within 2 business days')
            ->assertSee(route('sales-tax.registrations.show', $application));
    });

    it('explains what happens next once the application is submitted', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA'], [
            'paid_at' => now(),
            'status' => 'submitted',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        $this->get(route('sales-tax.registrations.payment-confirmation', $application))
            ->assertOk()
            ->assertSee('Your application is in.')
            ->assertSee('We file within 5 business days')
            ->assertDontSee('Start the questions');
    });

    it('labels the review step as a submission, not a payment, once paid', function () {
        $application = orderFirstDraft($this->business, $this->user, ['CA'], [
            'paid_at' => now(),
            'current_phase' => 'review',
        ]);

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->assertSee('Submit my application')
            ->assertDontSee('Proceed to Payment');
    });

    it('flags paid applications that still need answers on the admin board, with rush', function () {
        $admin = User::factory()->create();
        $admin->givePermissionTo('tax.view');

        orderFirstDraft($this->business, $this->user, ['CA'], [
            'paid_at' => now(),
            'rush_requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.sales-tax.board'))
            ->assertOk()
            ->assertSee('Awaiting customer answers')
            ->assertSee('Rush');
    });
});
