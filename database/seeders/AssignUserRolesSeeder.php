<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AssignUserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->assignAdminRole();
        $this->assignLienAgentRole();
    }

    protected function assignAdminRole(): void
    {
        $email = 'major@major.holdings';
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->command->info("User '{$email}' not found - skipping admin role assignment.");

            return;
        }

        if (! Role::where('name', 'admin')->exists()) {
            $this->command->warn("Role 'admin' does not exist - run PermissionsSeeder first.");

            return;
        }

        $user->assignRole('admin');
        $this->command->info("Assigned 'admin' role to {$email}");
    }

    protected function assignLienAgentRole(): void
    {
        $email = 'admin-liens@test.test';
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->command->info("User '{$email}' not found - skipping lien agent role assignment.");

            return;
        }

        if (! Role::where('name', 'lien_agent')->exists()) {
            $this->command->warn("Role 'lien_agent' does not exist - run PermissionsSeeder first.");

            return;
        }

        $user->assignRole('lien_agent');
        $this->command->info("Assigned 'lien_agent' role to {$email}");
    }
}
