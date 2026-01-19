<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;

describe('ResolveCurrentBusiness Middleware', function () {
    it('redirects to business selector when user has no businesses', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/portal')
            ->assertRedirect(route('portal.select-business'));
    });

    it('auto-selects business when user has exactly one', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'My Only Business',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->get('/portal')
            ->assertOk()
            ->assertSee('My Only Business');
    });

    it('redirects to business selector when user has multiple businesses and none selected', function () {
        $user = User::factory()->create();
        $business1 = Business::create(['name' => 'Business 1', 'onboarding_completed_at' => now()]);
        $business2 = Business::create(['name' => 'Business 2', 'onboarding_completed_at' => now()]);
        $user->businesses()->attach($business1->id, ['role' => 'owner']);
        $user->businesses()->attach($business2->id, ['role' => 'member']);

        $this->actingAs($user)
            ->get('/portal')
            ->assertRedirect(route('portal.select-business'));
    });

    it('uses session business when set', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Session Business',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get('/portal')
            ->assertOk()
            ->assertSee('Session Business');
    });

    it('denies access when session business does not belong to user', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Other Business']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get('/portal')
            ->assertForbidden();
    });
});

describe('EnsureBusinessProfileComplete Middleware', function () {
    it('redirects to onboarding when profile is incomplete', function () {
        $user = User::factory()->create();
        $business = Business::create(['name' => 'Incomplete Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get('/portal')
            ->assertRedirect(route('portal.onboarding'));
    });

    it('allows access when profile is complete', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Complete Business',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get('/portal')
            ->assertOk();
    });
});

describe('EnsureHasAccess Middleware', function () {
    it('redirects to checkout when one-time application is not paid', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Test Business',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get("/portal/forms/applications/{$application->id}")
            ->assertRedirect(route('portal.checkout', $application));
    });

    it('allows access when one-time application is paid', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Test Business',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'CA',
            'status' => 'pending',
            'data' => [],
        ]);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get("/portal/forms/applications/{$application->id}")
            ->assertOk();
    });

    it('redirects to checkout when subscription application has no active subscription', function () {
        $user = User::factory()->create();
        $business = Business::create([
            'name' => 'Test Business',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $application = FormApplication::create([
            'business_id' => $business->id,
            'form_type' => 'llc',
            'definition_version' => 1,
            'selected_states' => ['DE'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $user->id,
        ]);

        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => 'DE',
            'status' => 'pending',
            'data' => [],
        ]);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get("/portal/forms/applications/{$application->id}")
            ->assertRedirect(route('portal.checkout', $application));
    });

    it('denies access when application belongs to different business', function () {
        $user = User::factory()->create();
        $business1 = Business::create([
            'name' => 'Business 1',
            'onboarding_completed_at' => now(),
        ]);
        $business2 = Business::create([
            'name' => 'Business 2',
            'onboarding_completed_at' => now(),
        ]);
        $user->businesses()->attach($business1->id, ['role' => 'owner']);
        $user->businesses()->attach($business2->id, ['role' => 'owner']);

        $application = FormApplication::create([
            'business_id' => $business2->id,
            'form_type' => 'sales_tax_permit',
            'definition_version' => 1,
            'selected_states' => ['CA'],
            'status' => 'draft',
            'current_phase' => 'core',
            'core_data' => [],
            'created_by_user_id' => $user->id,
            'paid_at' => now(),
        ]);

        // Trying to access business2's application while session is set to business1
        $this->actingAs($user)
            ->withSession(['current_business_id' => $business1->id])
            ->get("/portal/forms/applications/{$application->id}")
            ->assertForbidden();
    });
});
