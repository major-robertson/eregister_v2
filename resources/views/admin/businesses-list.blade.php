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

        <div class="px-4 pb-4">
            <flux:table :paginate="$businesses">
                <flux:table.columns>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>Onboarding</flux:table.column>
                    <flux:table.column>Users</flux:table.column>
                    <flux:table.column>Applications</flux:table.column>
                    <flux:table.column>Lien Projects</flux:table.column>
                    <flux:table.column>Subscription</flux:table.column>
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($businesses as $business)
                    @php
                    $address = $business->business_address ?? [];
                    @endphp
                    <flux:table.row wire:key="business-{{ $business->id }}">
                        <flux:table.cell>
                            <div>
                                <flux:text class="font-medium">
                                    {{ $address['city'] ?? 'Unknown' }}, {{ $address['state'] ?? 'N/A' }}
                                </flux:text>
                                <flux:text class="text-sm text-gray-500">
                                    {{ $address['street'] ?? 'No address' }}
                                </flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
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
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $business->users_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $business->form_applications_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="zinc">{{ $business->lien_projects_count }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($business->has_active_subscription)
                            <flux:badge size="sm" color="green">Active</flux:badge>
                            @else
                            <flux:badge size="sm" color="zinc">None</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text class="text-sm text-gray-500">
                                {{ $business->created_at->format('M j, Y') }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:button variant="ghost" size="sm" icon="eye"
                                :href="route('admin.businesses.show', $business)" wire:navigate>
                                View
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="py-12 text-center">
                            <flux:text class="text-gray-400">No businesses found.</flux:text>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>