<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Domains\SalesTax\Livewire\Dashboard;
use App\Mail\PermitApprovedResaleOffer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * A paid, submitted sales tax registration with one admin card per state.
 *
 * @param  array<int, string>  $states
 */
function approvableRegistration(Business $business, User $user, array $states): FormApplication
{
    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => [],
        'created_by_user_id' => $user->id,
        'paid_at' => now(),
        'submitted_at' => now(),
        'locked_at' => now(),
    ]);

    foreach ($states as $code) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $code,
            'status' => 'complete',
            'completed_at' => now(),
            'data' => [],
        ]);
    }

    return $application;
}

beforeEach(function () {
    Mail::fake();

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create(['onboarding_completed_at' => now()]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);
});

it('offers the generator once when the first state approves the registration', function () {
    $application = approvableRegistration($this->business, $this->user, ['CA', 'TX']);
    [$california, $texas] = $application->states()->orderBy('state_code')->get();

    $california->transitionAdminStatusTo(FormApplicationStateAdminStatus::SubmittedToState);
    Mail::assertNothingQueued();

    $california->transitionAdminStatusTo(FormApplicationStateAdminStatus::Approved);

    Mail::assertQueued(PermitApprovedResaleOffer::class, function (PermitApprovedResaleOffer $mail) use ($application) {
        return $mail->hasTo($this->user->email)
            && $mail->stateCode === 'CA'
            && $mail->application->is($application);
    });

    // The second state's approval does not send it again.
    $texas->transitionAdminStatusTo(FormApplicationStateAdminStatus::Approved);

    Mail::assertQueued(PermitApprovedResaleOffer::class, 1);
});

it('stays quiet for a business that already subscribes to the generator', function () {
    DB::table('subscriptions')->insert([
        'business_id' => $this->business->id,
        'type' => config('resale_cert.subscription_type'),
        'stripe_id' => 'sub_existing',
        'stripe_status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $application = approvableRegistration($this->business, $this->user, ['CA']);

    $application->states()->first()->transitionAdminStatusTo(FormApplicationStateAdminStatus::Approved);

    Mail::assertNothingQueued();
});

it('names the state, the price and the next step in the email', function () {
    $application = approvableRegistration($this->business, $this->user, ['CA']);

    $rendered = (new PermitApprovedResaleOffer($application, 'CA', $this->user))->render();

    expect($rendered)->toContain('California has approved your sales tax registration')
        ->and($rendered)->toContain('Create my first certificate')
        ->and($rendered)->toContain(route('resale-cert.dashboard'))
        ->and($rendered)->toContain('a year');
});

it('switches the dashboard offer to "your permit is approved" once a state approves', function () {
    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);

    $application = approvableRegistration($this->business, $this->user, ['CA']);

    Livewire::test(Dashboard::class)
        ->assertSee('Skip the paperwork')
        ->assertDontSee('permit is approved');

    $application->states()->first()->transitionAdminStatusTo(FormApplicationStateAdminStatus::Approved);

    Livewire::test(Dashboard::class)
        ->assertSee('Your California permit is approved')
        ->assertSee('Create my first certificate');
});
