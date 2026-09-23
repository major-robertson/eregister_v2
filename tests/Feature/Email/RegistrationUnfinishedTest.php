<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\SalesTax\Livewire\RegistrationCheckout;
use App\Domains\SalesTax\Services\RegistrationPaymentService;
use App\Mail\RegistrationUnfinishedReminder;
use App\Models\EmailSequence;
use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * A sales tax draft the way the state selector creates it (unpaid, no
 * answers yet). Overrides move it along.
 *
 * @param  array<int, string>  $states
 * @param  array<string, mixed>  $overrides
 */
function unfinishedRegistration(Business $business, User $user, array $states, array $overrides = []): FormApplication
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
    config(['cashier.secret' => null]);

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'per_state', 'billing_type' => 'one_time'],
        ['amount_cents' => 19900, 'currency' => 'usd', 'active' => true],
    );
    Price::updateOrCreate(
        ['product_family' => 'tax', 'product_key' => 'sales_tax_permit', 'variant_key' => 'rush', 'billing_type' => 'one_time'],
        ['amount_cents' => 9900, 'currency' => 'usd', 'active' => true],
    );

    $this->actingAs($this->user)->withSession(['current_business_id' => $this->business->id]);
});

it('starts the finish-the-questions series when a registration is paid and still open', function () {
    $application = unfinishedRegistration($this->business, $this->user, ['CA', 'TX']);

    app(RegistrationPaymentService::class)->applyPayment($application, 'pi_unfinished', true);

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'registration_unfinished')
        ->where('sequenceable_type', $application->getMorphClass())
        ->where('sequenceable_id', $application->id)
        ->first();

    expect($sequence)->not->toBeNull()
        ->and($sequence->user_id)->toBe($this->user->id)
        ->and($sequence->business_id)->toBe($this->business->id)
        ->and($sequence->resume_url)->toBe(route('sales-tax.registrations.show', $application))
        ->and(abs($sequence->next_send_at->diffInMinutes(now()->addMinutes(1440))))->toBeLessThan(2);

    // A second payment event does not restart the clock.
    app(RegistrationPaymentService::class)->applyPayment($application->fresh(), 'pi_unfinished', true);

    expect(EmailSequence::where('sequence_type', 'registration_unfinished')->count())->toBe(1);
});

it('does not start the series when the payment also submits the registration', function () {
    $application = unfinishedRegistration($this->business, $this->user, ['CA'], ['current_phase' => 'review']);
    $application->states()->update(['status' => 'complete', 'completed_at' => now()]);

    app(RegistrationPaymentService::class)->applyPayment($application, 'pi_finished', false);

    expect($application->fresh()->isLocked())->toBeTrue()
        ->and(EmailSequence::where('sequence_type', 'registration_unfinished')->exists())->toBeFalse();
});

it('sends the reminders on schedule and stops once the questions are submitted', function () {
    Mail::fake();

    $application = unfinishedRegistration($this->business, $this->user, ['CA']);
    app(RegistrationPaymentService::class)->applyPayment($application, 'pi_sched', false);

    $sequence = EmailSequence::where('sequence_type', 'registration_unfinished')->firstOrFail();

    // Not due yet: nothing goes out.
    $this->artisan('email:process-sequences');
    Mail::assertNothingQueued();

    // Day 1.
    $sequence->update(['next_send_at' => now()->subMinute()]);
    $this->artisan('email:process-sequences');

    Mail::assertQueued(RegistrationUnfinishedReminder::class, fn ($mail) => $mail->step === 1 && $mail->sequence->id === $sequence->id);
    expect(abs($sequence->fresh()->next_send_at->diffInMinutes(now()->addMinutes(4320))))->toBeLessThan(2);

    // The customer submits before day 4: the series ends without another email.
    // (refresh first: the command moved next_send_at, and the stale model
    // would see the same value twice and skip the write)
    $application->update(['status' => 'submitted', 'submitted_at' => now(), 'locked_at' => now()]);
    $sequence->refresh()->update(['next_send_at' => now()->subMinute()]);
    $this->artisan('email:process-sequences');

    Mail::assertQueued(RegistrationUnfinishedReminder::class, 1);
    expect($sequence->fresh()->suppression_reason)->toBe('submitted')
        ->and($sequence->fresh()->suppressed_at)->not->toBeNull();
});

it('names the states and mentions rush processing in the email', function () {
    $application = unfinishedRegistration($this->business, $this->user, ['CA', 'TX', 'NY'], [
        'paid_at' => now(),
        'rush_requested_at' => now(),
    ]);

    $sequence = EmailSequence::startUnfinishedRegistrationFor(
        $application,
        $this->user,
        $this->business,
        route('sales-tax.registrations.show', $application),
    );

    $rendered = (new RegistrationUnfinishedReminder($sequence, 1))->render();

    expect($rendered)->toContain('California, Texas and New York')
        ->and($rendered)->toContain('rush processing')
        ->and($rendered)->toContain('Finish the questions')
        ->and($rendered)->toContain(route('sales-tax.registrations.show', $application));

    $third = (new RegistrationUnfinishedReminder($sequence, 3))->envelope()->subject;

    expect($third)->toBe('We cannot file your sales tax registration until you finish');
});

it('starts the order reminders as soon as the order screen is shown', function () {
    $application = unfinishedRegistration($this->business, $this->user, ['CA']);

    Livewire::test(RegistrationCheckout::class, ['application' => $application])
        ->assertSet('step', 'order');

    $sequence = EmailSequence::query()
        ->where('sequence_type', 'abandon_checkout')
        ->where('sequenceable_type', $application->getMorphClass())
        ->where('sequenceable_id', $application->id)
        ->first();

    expect($sequence)->not->toBeNull()
        ->and($sequence->resume_url)->toBe(route('sales-tax.registrations.checkout', $application))
        ->and(abs($sequence->next_send_at->diffInMinutes(now()->addMinutes(60))))->toBeLessThan(2);
});
