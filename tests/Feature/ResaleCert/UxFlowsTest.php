<?php

use App\Domains\Business\Livewire\OnboardingWizard;
use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Ux Flow Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

function paidSalesTaxRegistration(Business $business, User $user, array $states): FormApplication
{
    return FormApplication::create([
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
    ]);
}

describe('blank resale certificate forms', function () {
    it('lets paid sales-tax customers download their state blank form for free', function () {
        paidSalesTaxRegistration($this->business, $this->user, ['TX']);

        $this->get(route('sales-tax.blank-resale-certificate', 'TX'))
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('TX_Blank_Resale_Certificate.pdf');
    });

    it('blocks downloads without a paid registration for that state', function () {
        paidSalesTaxRegistration($this->business, $this->user, ['CA']);

        $this->get(route('sales-tax.blank-resale-certificate', 'TX'))->assertForbidden();
    });

    it('blocks unpaid registrations', function () {
        FormApplication::create([
            'business_id' => $this->business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['TX'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $this->user->id,
        ]);

        $this->get(route('sales-tax.blank-resale-certificate', 'TX'))->assertForbidden();
    });

    it('has no blank form for FL (state-issued only)', function () {
        paidSalesTaxRegistration($this->business, $this->user, ['FL']);

        $this->get(route('sales-tax.blank-resale-certificate', 'FL'))->assertNotFound();
    });

    it('shows the free-forms perk and resale upsell on the sales-tax dashboard', function () {
        paidSalesTaxRegistration($this->business, $this->user, ['TX', 'CO']);

        $this->get(route('sales-tax.dashboard'))
            ->assertSuccessful()
            ->assertSee('Blank resale certificate forms')
            ->assertSee(route('sales-tax.blank-resale-certificate', 'TX'))
            ->assertSee('Generate Certificates Automatically');
    });

    it('hides the resale upsell once subscribed', function () {
        paidSalesTaxRegistration($this->business, $this->user, ['TX']);
        subscribeToResaleCerts($this->business);

        $this->get(route('sales-tax.dashboard'))
            ->assertSuccessful()
            ->assertSee('Blank resale certificate forms')
            ->assertDontSee('Generate Certificates Automatically');
    });
});

describe('signup funnel', function () {
    it('sends first-business resale signups to the resale dashboard after onboarding', function () {
        $user = User::factory()->create(['signup_landing_path' => '/resale-certificates']);
        $business = Business::create(['name' => 'Resale Signup Co', 'legal_name' => 'Resale Signup Co']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id]);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '10 Resale Way')
            ->set('businessAddress.city', 'Austin')
            ->set('businessAddress.state', 'TX')
            ->set('businessAddress.zip', '73301')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('resale-cert.dashboard'));
    });
});

describe('email verification return', function () {
    it('returns the user to their intended destination after verifying', function () {
        $unverified = User::factory()->unverified()->create();
        $unverified->businesses()->attach($this->business->id, ['role' => 'owner']);

        $this->actingAs($unverified);
        session([
            'current_business_id' => $this->business->id,
            'url.intended' => route('resale-cert.onboarding'),
        ]);

        $verifyUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(30), [
            'id' => $unverified->id,
            'hash' => sha1($unverified->email),
        ]);

        $this->get($verifyUrl)->assertRedirect(route('resale-cert.onboarding'));

        expect($unverified->fresh()->email_verified_at)->not->toBeNull();
    });
});
