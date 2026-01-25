<?php

namespace App\Domains\Admin\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RolesBoard extends Component
{
    public string $search = '';

    public ?int $editingUserId = null;

    /** @var array<string, bool> */
    public array $userRoles = [];

    public bool $showAssignModal = false;

    public string $assignUserSearch = '';

    public ?int $selectedUserId = null;

    public string $selectedRole = '';

    public function render(): View
    {
        return view('admin.roles-board', [
            'usersWithRoles' => $this->getUsersWithRoles(),
            'roles' => $this->getRoles(),
            'allRoles' => $this->getAllRoleNames(),
        ])->layout('layouts.admin', ['title' => 'Roles Management']);
    }

    /**
     * Get all users who have at least one role.
     */
    public function getUsersWithRoles(): Collection
    {
        $query = User::role($this->getAllRoleNames())
            ->with('roles');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', "%{$this->search}%")
                    ->orWhere('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('first_name')->get();
    }

    /**
     * Get all roles with their permissions.
     */
    public function getRoles(): Collection
    {
        return Role::with('permissions')->orderBy('name')->get();
    }

    /**
     * Get all role names.
     *
     * @return array<int, string>
     */
    public function getAllRoleNames(): array
    {
        return Role::pluck('name')->toArray();
    }

    /**
     * Open the edit roles modal for a user.
     */
    public function openEditModal(int $userId): void
    {
        $this->editingUserId = $userId;
        $user = User::findOrFail($userId);

        // Initialize role toggles
        $this->userRoles = [];
        foreach ($this->getAllRoleNames() as $roleName) {
            $this->userRoles[$roleName] = $user->hasRole($roleName);
        }
    }

    /**
     * Close the edit modal.
     */
    public function closeEditModal(): void
    {
        $this->editingUserId = null;
        $this->userRoles = [];
    }

    /**
     * Save the user's roles.
     */
    public function saveUserRoles(): void
    {
        if (! $this->editingUserId) {
            return;
        }

        $user = User::findOrFail($this->editingUserId);

        // Get roles to assign
        $rolesToAssign = array_keys(array_filter($this->userRoles));

        $user->syncRoles($rolesToAssign);

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->closeEditModal();

        $this->dispatch('toast', message: "Roles updated for {$user->name}.", type: 'success');
    }

    /**
     * Remove all roles from a user.
     */
    public function removeAllRoles(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->syncRoles([]);

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->dispatch('toast', message: "All roles removed from {$user->name}.", type: 'success');
    }

    /**
     * Open the assign role modal.
     */
    public function openAssignModal(): void
    {
        $this->showAssignModal = true;
        $this->assignUserSearch = '';
        $this->selectedUserId = null;
        $this->selectedRole = '';
    }

    /**
     * Close the assign role modal.
     */
    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignUserSearch = '';
        $this->selectedUserId = null;
        $this->selectedRole = '';
    }

    /**
     * Search for users to assign a role.
     */
    #[Computed]
    public function searchableUsers(): Collection
    {
        if (strlen($this->assignUserSearch) < 2) {
            return collect();
        }

        return User::where('email', 'like', "%{$this->assignUserSearch}%")
            ->orWhere('first_name', 'like', "%{$this->assignUserSearch}%")
            ->orWhere('last_name', 'like', "%{$this->assignUserSearch}%")
            ->limit(10)
            ->get();
    }

    /**
     * Select a user for role assignment.
     */
    public function selectUser(int $userId): void
    {
        $this->selectedUserId = $userId;
        $user = User::find($userId);
        $this->assignUserSearch = $user?->email ?? '';
    }

    /**
     * Assign a role to the selected user.
     */
    public function assignRole(): void
    {
        if (! $this->selectedUserId || ! $this->selectedRole) {
            $this->dispatch('toast', message: 'Please select a user and a role.', type: 'error');

            return;
        }

        $user = User::findOrFail($this->selectedUserId);

        if ($user->hasRole($this->selectedRole)) {
            $this->dispatch('toast', message: "{$user->name} already has the {$this->selectedRole} role.", type: 'warning');

            return;
        }

        $user->assignRole($this->selectedRole);

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->closeAssignModal();

        $this->dispatch('toast', message: "Role '{$this->selectedRole}' assigned to {$user->name}.", type: 'success');
    }
}
