<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('signup captures ip and user agent', function () {
    $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user->signup_ip)->not->toBeNull();
    expect($user->signup_user_agent)->not->toBeNull();
});

test('signup captures utm parameters from session', function () {
    $this->withSession([
        'signup_utm_source' => 'google',
        'signup_utm_medium' => 'cpc',
        'signup_utm_campaign' => 'spring_sale',
        'signup_utm_term' => 'lien+rights',
        'signup_utm_content' => 'banner_ad',
    ]);

    $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user->signup_utm_source)->toBe('google');
    expect($user->signup_utm_medium)->toBe('cpc');
    expect($user->signup_utm_campaign)->toBe('spring_sale');
    expect($user->signup_utm_term)->toBe('lien+rights');
    expect($user->signup_utm_content)->toBe('banner_ad');
});

test('signup captures landing path and url from session', function () {
    $this->withSession([
        'signup_landing_path' => '/liens',
        'signup_landing_url' => 'http://example.com/liens?utm_source=google',
    ]);

    $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user->signup_landing_path)->toBe('/liens');
    expect($user->signup_landing_url)->toBe('http://example.com/liens?utm_source=google');
});

test('signup captures external referrer from session', function () {
    $this->withSession([
        'signup_referrer' => 'https://www.google.com/search?q=lien+rights',
    ]);

    $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::where('email', 'test@example.com')->first();

    expect($user->signup_referrer)->toBe('https://www.google.com/search?q=lien+rights');
});

test('signup clears attribution session data after registration', function () {
    $this->withSession([
        'signup_landing_path' => '/liens',
        'signup_landing_url' => 'http://example.com/liens',
        'signup_utm_source' => 'google',
    ]);

    $this->post(route('register.store'), [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    expect(session('signup_landing_path'))->toBeNull();
    expect(session('signup_landing_url'))->toBeNull();
    expect(session('signup_utm_source'))->toBeNull();
});
