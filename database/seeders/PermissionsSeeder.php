<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Lien permissions
            'lien.view',
            'lien.update',
            'lien.change_status',
            'lien.assign',

            // LLC permissions (future)
            'llc.view',
            'llc.update',
            'llc.change_status',

            // Tax permissions (future)
            'tax.view',
            'tax.update',
            'tax.change_status',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createAdminRole();
        $this->createLienAgentRole();
        $this->createLlcAgentRole();
        $this->createTaxAgentRole();
        $this->createViewerRole();
    }

    protected function createAdminRole(): void
    {
        // Admin role - bypasses all permissions via Gate::before
        // No explicit permissions needed, but we assign all for clarity
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());
    }

    protected function createLienAgentRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'lien_agent']);
        $role->syncPermissions([
            'lien.view',
            'lien.update',
            'lien.change_status',
        ]);
    }

    protected function createLlcAgentRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'llc_agent']);
        $role->syncPermissions([
            'llc.view',
            'llc.update',
            'llc.change_status',
        ]);
    }

    protected function createTaxAgentRole(): void
    {
        $role = Role::firstOrCreate(['name' => 'tax_agent']);
        $role->syncPermissions([
            'tax.view',
            'tax.update',
            'tax.change_status',
        ]);
    }

    protected function createViewerRole(): void
    {
        // Viewer role - read-only access to all modules
        $role = Role::firstOrCreate(['name' => 'viewer']);
        $role->syncPermissions([
            'lien.view',
            'llc.view',
            'tax.view',
        ]);
    }
}
