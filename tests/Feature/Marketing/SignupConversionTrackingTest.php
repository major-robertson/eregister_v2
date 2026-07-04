<?php

use App\Models\User;

// The signup conversion scripts (Google + Reddit) render on the onboarding
// wizard, which the user only reaches several requests after registering
// (register -> /portal -> select-business -> create business -> wizard).
// A session flash dies on the first redirect, so the marker must persist
// until the wizard pulls it - these tests pin that whole chain.

function registerTestUser(): User
{
    test()->post(route('register.store'), [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane.signup@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    return User::where('email', 'jane.signup@example.com')->firstOrFail();
}

test('just_registered survives the post-register redirect chain', function () {
    registerTestUser();

    // A brand-new user has no business, so /portal bounces to the selector.
    $this->get('/portal')->assertRedirect(route('portal.select-business'));

    // The marker must still be there after that intermediate request.
    expect(session('just_registered'))->toBeTrue();
});

test('signup conversions fire once on the onboarding wizard', function () {
    $user = registerTestUser();

    $this->get('/portal');

    $business = App\Domains\Business\Models\Business::create(['name' => 'Fresh Business']);
    $user->businesses()->attach($business->id, ['role' => 'owner']);
    session(['current_business_id' => $business->id]);

    // First wizard render: both conversion scripts present.
    $response = $this->get(route('portal.onboarding'));
    $response->assertOk()
        ->assertSee("rdt('track', 'SignUp'", false)
        ->assertSee('signup-'.$user->id, false)
        ->assertSee('AW-984288380/XDg5CMWk_7oZEPyYrNUD', false);

    // The marker is consumed - a refresh must not re-fire the conversions.
    expect(session('just_registered'))->toBeNull();

    $this->get(route('portal.onboarding'))
        ->assertOk()
        ->assertDontSee("rdt('track', 'SignUp'", false);
});
