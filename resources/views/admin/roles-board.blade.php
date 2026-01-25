<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Roles Management</flux:heading>
            <flux:text class="mt-1">Manage user roles and view permissions.</flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openAssignModal">
            Assign Role
        </flux:button>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Users with Roles Table -->
        <div class="lg:col-span-2 space-y-4">
            <div class="rounded-lg border border-border bg-white">
                <div class="flex items-center justify-between border-b border-border px-4 py-3">
                    <flux:heading size="sm">Users with Roles</flux:heading>
                    <div class="w-64">
                        <flux:input
                            type="search"
                            placeholder="Search users..."
                            wire:model.live.debounce.300ms="search"
                            icon="magnifying-glass"
                        />
                    </div>
                </div>

                <div class="divide-y divide-border">
                    @forelse ($usersWithRoles as $user)
                        <div class="flex items-center justify-between px-4 py-3">
                            <div class="flex items-center gap-3">
                                <flux:avatar :initials="$user->initials()" size="sm" />
                                <div>
                                    <flux:text class="font-medium">{{ $user->name }}</flux:text>
                                    <flux:text class="text-sm text-gray-500">{{ $user->email }}</flux:text>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($user->roles as $role)
                                        <flux:badge size="sm" color="{{ $role->name === 'admin' ? 'red' : 'blue' }}">
                                            {{ str_replace('_', ' ', $role->name) }}
                                        </flux:badge>
                                    @endforeach
                                </div>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-vertical" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="openEditModal({{ $user->id }})">
                                            Edit Roles
                                        </flux:menu.item>
                                        <flux:menu.item
                                            icon="trash"
                                            variant="danger"
                                            wire:click="removeAllRoles({{ $user->id }})"
                                            wire:confirm="Are you sure you want to remove all roles from {{ $user->name }}?"
                                        >
                                            Remove All Roles
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        </div>
                    @empty
                        <div class="flex h-32 items-center justify-center">
                            <flux:text class="text-gray-400">No users with roles found.</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Roles & Permissions Reference -->
        <div class="space-y-4">
            <div class="rounded-lg border border-border bg-white">
                <div class="border-b border-border px-4 py-3">
                    <flux:heading size="sm">Roles & Permissions</flux:heading>
                </div>

                <div class="divide-y divide-border">
                    @foreach ($roles as $role)
                        <details class="group">
                            <summary class="flex cursor-pointer items-center justify-between px-4 py-3 hover:bg-gray-50">
                                <div class="flex items-center gap-2">
                                    <flux:badge size="sm" color="{{ $role->name === 'admin' ? 'red' : 'blue' }}">
                                        {{ str_replace('_', ' ', $role->name) }}
                                    </flux:badge>
                                    <flux:text class="text-sm text-gray-500">
                                        {{ $role->permissions->count() }} permissions
                                    </flux:text>
                                </div>
                                <flux:icon name="chevron-down" class="size-4 text-gray-400 transition group-open:rotate-180" />
                            </summary>
                            <div class="border-t border-border bg-gray-50 px-4 py-3">
                                @if ($role->name === 'admin')
                                    <flux:text class="text-sm text-gray-600 italic">
                                        Admin role bypasses all permission checks.
                                    </flux:text>
                                @endif
                                @if ($role->permissions->isEmpty())
                                    <flux:text class="text-sm text-gray-500">No permissions assigned.</flux:text>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($role->permissions as $permission)
                                            <flux:badge size="sm" color="zinc">{{ $permission->name }}</flux:badge>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Roles Modal -->
    @if ($editingUserId)
        @php
            $editingUser = \App\Models\User::find($editingUserId);
        @endphp
        <flux:modal name="edit-roles-modal" class="max-w-md" :show="true">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Edit Roles</flux:heading>
                    <flux:text class="mt-1">
                        Manage roles for <strong>{{ $editingUser?->name }}</strong>
                    </flux:text>
                </div>

                <div class="space-y-3">
                    @foreach ($allRoles as $roleName)
                        <label class="flex items-center justify-between rounded-lg border border-border px-4 py-3 hover:bg-gray-50 cursor-pointer">
                            <div>
                                <flux:text class="font-medium">{{ str_replace('_', ' ', ucfirst($roleName)) }}</flux:text>
                            </div>
                            <flux:switch
                                wire:model="userRoles.{{ $roleName }}"
                            />
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeEditModal">
                        Cancel
                    </flux:button>
                    <flux:button variant="primary" wire:click="saveUserRoles">
                        Save Changes
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif

    <!-- Assign Role Modal -->
    @if ($showAssignModal)
        <flux:modal name="assign-role-modal" class="max-w-md" :show="true">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Assign Role to User</flux:heading>
                    <flux:text class="mt-1">Search for a user and select a role to assign.</flux:text>
                </div>

                <div class="space-y-4">
                    <!-- User Search -->
                    <div class="space-y-2">
                        <flux:field>
                            <flux:label>Search User</flux:label>
                            <flux:input
                                type="search"
                                placeholder="Search by name or email..."
                                wire:model.live.debounce.300ms="assignUserSearch"
                                icon="magnifying-glass"
                            />
                        </flux:field>

                        @if ($this->searchableUsers->isNotEmpty())
                            <div class="rounded-lg border border-border divide-y divide-border max-h-48 overflow-y-auto">
                                @foreach ($this->searchableUsers as $user)
                                    <button
                                        type="button"
                                        class="w-full flex items-center gap-3 px-3 py-2 hover:bg-gray-50 text-left {{ $selectedUserId === $user->id ? 'bg-blue-50' : '' }}"
                                        wire:click="selectUser({{ $user->id }})"
                                    >
                                        <flux:avatar :initials="$user->initials()" size="sm" />
                                        <div>
                                            <flux:text class="font-medium text-sm">{{ $user->name }}</flux:text>
                                            <flux:text class="text-xs text-gray-500">{{ $user->email }}</flux:text>
                                        </div>
                                        @if ($selectedUserId === $user->id)
                                            <flux:icon name="check" class="size-4 text-blue-600 ml-auto" />
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Role Selection -->
                    <flux:field>
                        <flux:label>Role</flux:label>
                        <flux:select wire:model="selectedRole">
                            <flux:select.option value="">Select a role...</flux:select.option>
                            @foreach ($allRoles as $roleName)
                                <flux:select.option value="{{ $roleName }}">
                                    {{ str_replace('_', ' ', ucfirst($roleName)) }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:button variant="ghost" wire:click="closeAssignModal">
                        Cancel
                    </flux:button>
                    <flux:button
                        variant="primary"
                        wire:click="assignRole"
                        :disabled="!$selectedUserId || !$selectedRole"
                    >
                        Assign Role
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
