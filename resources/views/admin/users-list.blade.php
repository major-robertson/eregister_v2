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

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">User</th>
                        <th class="px-4 py-3 font-medium">Businesses</th>
                        <th class="px-4 py-3 font-medium">Subscription</th>
                        <th class="px-4 py-3 font-medium">2FA</th>
                        <th class="px-4 py-3 font-medium">Joined</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <flux:avatar :initials="$user->initials()" size="sm" />
                                <div>
                                    <flux:text class="font-medium">{{ $user->name }}</flux:text>
                                    <flux:text class="text-sm text-gray-500">{{ $user->email }}</flux:text>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">
                                {{ $user->businesses_count }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->has_subscription)
                            <flux:badge size="sm" color="green">Active</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">None</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($user->two_factor_confirmed_at)
                            <flux:badge size="sm" color="green">Enabled</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">Disabled</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ $user->created_at->format('M j, Y') }}
                            </flux:text>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" icon="eye" :href="route('admin.users.show', $user)"
                                wire:navigate>
                                View
                            </flux:button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No users found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
        <div class="border-t border-border px-4 py-3">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>