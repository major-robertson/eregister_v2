<div class="space-y-6">
    <div>
        <flux:heading size="xl">Users</flux:heading>
        <flux:text class="mt-1">View and manage all registered users.</flux:text>
    </div>

    <div class="rounded-lg border border-border bg-white">
        <div class="flex items-center justify-between border-b border-border px-4 py-3">
            <flux:heading size="sm">All Users</flux:heading>
            <div class="w-64">
                <flux:input type="search" placeholder="Search by name or email..."
                    wire:model.live.debounce.300ms="search" icon="magnifying-glass" />
            </div>
        </div>

        <div class="px-4 pb-4">
            <flux:table :paginate="$users">
                <flux:table.columns>
                    <flux:table.column>User</flux:table.column>
                    <flux:table.column>Businesses</flux:table.column>
                    <flux:table.column>Subscription</flux:table.column>
                    <flux:table.column>2FA</flux:table.column>
                    <flux:table.column>Joined</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($users as $user)
                    <flux:table.row wire:key="user-{{ $user->id }}">
                        <flux:table.cell>
                            <div class="flex items-center gap-3">
                                <flux:avatar :initials="$user->initials()" size="sm" />
                                <div>
                                    <flux:text class="font-medium">{{ $user->name }}</flux:text>
                                    <flux:text class="text-sm text-gray-500">{{ $user->email }}</flux:text>
                                </div>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">
                                {{ $user->businesses_count }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($user->has_subscription)
                            <flux:badge size="sm" color="green">Active</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">None</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($user->two_factor_confirmed_at)
                            <flux:badge size="sm" color="green">Enabled</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">Disabled</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-sm text-gray-500">
                                {{ $user->created_at->format('M j, Y') }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="ghost" size="sm" icon="eye" :href="route('admin.users.show', $user)"
                                wire:navigate>
                                View
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-12 text-center">
                            <flux:text class="text-gray-400">No users found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>