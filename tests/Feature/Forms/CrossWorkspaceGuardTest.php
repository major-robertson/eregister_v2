<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->business = Business::create([
        'name' => 'Cross Workspace Guard Co',
        'onboarding_completed_at' => now(),
    ]);
    $this->user->businesses()->attach($this->business->id, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

function makeApplication(string $formType, array $states, Business $business, User $user, array $overrides = []): FormApplication
{
    $application = FormApplication::create(array_merge([
        'business_id' => $business->id,
        'form_type' => $formType,
        'definition_version' => 1,
        'selected_states' => $states,
        'status' => 'draft',
        'current_phase' => 'core',
        'core_data' => [],
        'created_by_user_id' => $user->id,
    ], $overrides));

    foreach ($states as $state) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $state,
            'status' => 'pending',
            'data' => [],
        ]);
    }

    return $application;
}

it('returns 404 when an LLC application is loaded via the sales-tax show URL', function () {
    $llcApp = makeApplication('llc', ['DE'], $this->business, $this->user, ['paid_at' => now()]);

    $this->get("/portal/sales-tax/registrations/{$llcApp->id}")
        ->assertNotFound();
});

it('returns 404 when a sales-tax application is loaded via the formations show URL', function () {
    $stApp = makeApplication('sales_tax_permit', ['CA'], $this->business, $this->user, ['paid_at' => now()]);

    $this->get("/portal/formations/applications/{$stApp->id}")
        ->assertNotFound();
});

it('loads the LLC application via the formations show URL', function () {
    // LLC uses subscription billing; without a subscription the app
    // redirects to checkout via the application.access middleware, which
    // is a separate code path from the workspace guard. Mark it as
    // having an active subscription would require Stripe wiring; the
    // simpler proof here is that the URL doesn't 404 (it redirects).
    $llcApp = makeApplication('llc', ['DE'], $this->business, $this->user);

    $this->get("/portal/formations/applications/{$llcApp->id}")
        ->assertRedirect(route('portal.checkout', $llcApp));
});

it('loads the sales-tax application via the sales-tax show URL', function () {
    $stApp = makeApplication('sales_tax_permit', ['CA'], $this->business, $this->user, ['paid_at' => now()]);

    $this->get("/portal/sales-tax/registrations/{$stApp->id}")
        ->assertOk();
});
