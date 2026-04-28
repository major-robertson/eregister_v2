<?php

use App\Domains\Business\Models\Business;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users with no business are redirected to business selection', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('portal.select-business'));
});

test('authenticated users with one business are auto-directed to dashboard', function () {
    $user = User::factory()->create();
    $business = Business::create([
        'name' => 'My Business',
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('My Business');
});

test('authenticated users with session business can visit the dashboard', function () {
    $user = User::factory()->create();
    $business = Business::create([
        'name' => 'Session Business',
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    $this->actingAs($user)
        ->withSession(['current_business_id' => $business->id]);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Session Business');
});

test('the dashboard renders a workspace card for every enabled registry entry', function () {
    $user = User::factory()->create();
    $business = Business::create([
        'name' => 'Workspace Test Biz',
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    $this->actingAs($user)
        ->withSession(['current_business_id' => $business->id]);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Workspaces')
        ->assertSee('Liens')
        ->assertSee('Sales Tax')
        ->assertSee('Get Started');
});

test('disabling a workspace via config hides its card on the dashboard', function () {
    config()->set('workspaces.sales_tax.enabled', false);
    app()->forgetInstance(\App\Support\Workspaces\WorkspaceRegistry::class);

    $user = User::factory()->create();
    $business = Business::create([
        'name' => 'Hidden WS Biz',
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    $this->actingAs($user)
        ->withSession(['current_business_id' => $business->id]);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('Liens')
        ->assertDontSee('Sales Tax');
});
