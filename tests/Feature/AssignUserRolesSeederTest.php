<?php

use App\Models\User;
use Database\Seeders\AssignUserRolesSeeder;
use Database\Seeders\PermissionsSeeder;

beforeEach(function () {
    // Run PermissionsSeeder to ensure roles exist
    $this->seed(PermissionsSeeder::class);
});

it('assigns admin role to major@major.holdings when user exists', function () {
    $user = User::factory()->create(['email' => 'major@major.holdings']);

    $this->seed(AssignUserRolesSeeder::class);

    expect($user->fresh()->hasRole('admin'))->toBeTrue();
});

it('assigns lien_agent role to admin-liens@test.test when user exists', function () {
    $user = User::factory()->create(['email' => 'admin-liens@test.test']);

    $this->seed(AssignUserRolesSeeder::class);

    expect($user->fresh()->hasRole('lien_agent'))->toBeTrue();
});

it('does not fail when users do not exist', function () {
    // Don't create any users - seeder should handle gracefully
    $this->seed(AssignUserRolesSeeder::class);

    // If we get here without exception, the test passes
    expect(true)->toBeTrue();
});

it('assigns both roles when both users exist', function () {
    $adminUser = User::factory()->create(['email' => 'major@major.holdings']);
    $lienUser = User::factory()->create(['email' => 'admin-liens@test.test']);

    $this->seed(AssignUserRolesSeeder::class);

    expect($adminUser->fresh()->hasRole('admin'))->toBeTrue();
    expect($lienUser->fresh()->hasRole('lien_agent'))->toBeTrue();
});
