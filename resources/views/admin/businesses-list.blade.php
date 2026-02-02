<div class="space-y-6">
    <div>
        <flux:heading size="xl">Businesses</flux:heading>
        <flux:text class="mt-1">View and manage all registered businesses.</flux:text>
    </div>

    <div class="rounded-lg border border-border bg-white">
        <div class="flex items-center justify-between border-b border-border px-4 py-3">
            <flux:heading size="sm">All Businesses</flux:heading>
            <div class="w-64">
                <flux:input type="search" placeholder="Search by city, state, name..."
                    wire:model.live.debounce.300ms="search" icon="magnifying-glass" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 text-left text-sm text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Business</th>
                        <th class="px-4 py-3 font-medium">Onboarding</th>
                        <th class="px-4 py-3 font-medium">Users</th>
                        <th class="px-4 py-3 font-medium">Applications</th>
                        <th class="px-4 py-3 font-medium">Lien Projects</th>
                        <th class="px-4 py-3 font-medium">Subscription</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($businesses as $business)
                    @php
                    $address = $business->business_address ?? [];
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div>
                                <flux:text class="font-medium">
                                    {{ $address['city'] ?? 'Unknown' }}, {{ $address['state'] ?? 'N/A' }}
                                </flux:text>
                                <flux:text class="text-sm text-gray-500">
                                    {{ $address['street'] ?? 'No address' }}
                                </flux:text>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                @if ($business->onboarding_completed_at)
                                <flux:badge size="sm" color="green" title="Onboarding Complete">Main</flux:badge>
                                @endif
                                @if ($business->lien_onboarding_completed_at)
                                <flux:badge size="sm" color="green" title="Lien Onboarding Complete">Lien</flux:badge>
                                @endif
                                @if (!$business->onboarding_completed_at && !$business->lien_onboarding_completed_at)
                                <flux:badge size="sm" color="zinc">None</flux:badge>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">{{ $business->users_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">{{ $business->form_applications_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="zinc">{{ $business->lien_projects_count }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            @if ($business->has_active_subscription)
                            <flux:badge size="sm" color="green">Active</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">None</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-gray-500">
                                {{ $business->created_at->format('M j, Y') }}
                            </flux:text>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <flux:button variant="ghost" size="sm" icon="eye"
                                :href="route('admin.businesses.show', $business)" wire:navigate>
                                View
                            </flux:button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <flux:text class="text-gray-400">No businesses found.</flux:text>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($businesses->hasPages())
        <div class="border-t border-border px-4 py-3">
            {{ $businesses->links() }}
        </div>
        @endif
    </div>
</div>