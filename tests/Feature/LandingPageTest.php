<?php

use App\Models\User;

test('landing page can be rendered', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('landing page shows dashboard link to portal for regular users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('href="'.url('/portal').'"', false);
});

test('landing page shows dashboard link to admin for users with roles', function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('href="'.route('admin.home').'"', false);
});

test('landing page shows dashboard link to admin for lien agent users', function () {
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);

    $agent = User::factory()->create();
    $agent->assignRole('lien_agent');

    $response = $this->actingAs($agent)->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('href="'.route('admin.home').'"', false);
});
