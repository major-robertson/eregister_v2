<?php

it('renders the public demo pages without auth', function (string $uri, string $expected) {
    $this->get($uri)
        ->assertOk()
        ->assertSee($expected, escape: false);
})->with([
    'public homepage' => ['/mdcps-demo', 'Everglades Elementary School'],
    'public calendar' => ['/mdcps-demo/calendar', 'School Calendar'],
    'cms login' => ['/mdcps-demo/admin/login', 'Content Management System'],
]);

it('keeps the demo noindexed', function () {
    $this->get('/mdcps-demo')
        ->assertOk()
        ->assertSee('noindex', escape: false);
});

it('redirects guests away from the CMS', function (string $uri) {
    $this->get($uri)->assertRedirect('/mdcps-demo/admin/login');
})->with([
    'dashboard' => '/mdcps-demo/admin',
    'calendar' => '/mdcps-demo/admin/calendar',
    'alert' => '/mdcps-demo/admin/alert',
    'media' => '/mdcps-demo/admin/media',
]);

it('allows authed sessions into the CMS', function (string $uri, string $expected) {
    $this->withSession(['mdcps_demo_authed' => true])
        ->get($uri)
        ->assertOk()
        ->assertSee($expected, escape: false);
})->with([
    'dashboard' => ['/mdcps-demo/admin', 'CMS Dashboard'],
    'calendar' => ['/mdcps-demo/admin/calendar', 'Calendar event'],
    'alert' => ['/mdcps-demo/admin/alert', 'Emergency alert'],
    'media' => ['/mdcps-demo/admin/media', 'Media'],
]);

it('logs in with valid sandbox credentials', function () {
    $this->post('/mdcps-demo/admin/login', [
        'username' => config('mdcps_demo.username'),
        'password' => config('mdcps_demo.password'),
    ])
        ->assertRedirect('/mdcps-demo/admin');

    expect(session('mdcps_demo_authed'))->toBeTrue();
});

it('rejects invalid sandbox credentials', function () {
    $this->from('/mdcps-demo/admin/login')
        ->post('/mdcps-demo/admin/login', [
            'username' => 'wrong',
            'password' => 'nope',
        ])
        ->assertRedirect('/mdcps-demo/admin/login')
        ->assertSessionHasErrors('username');

    expect(session('mdcps_demo_authed'))->toBeNull();
});

it('logs out and clears the session flag', function () {
    $this->withSession(['mdcps_demo_authed' => true])
        ->post('/mdcps-demo/admin/logout')
        ->assertRedirect('/mdcps-demo/admin/login');
});
