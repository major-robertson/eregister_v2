<div class="space-y-6">
    <div>
        <flux:heading size="xl">Marketing Stats</flux:heading>
        <flux:text class="mt-1">Overview of leads, visits, mailings, and conversions.</flux:text>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        <!-- Leads Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="user-group" class="size-5 text-blue-600" />
                </div>
                <flux:heading size="sm">Leads</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Total</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($leadStats['total']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $leadStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $leadStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $leadStats['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Visits Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100">
                    <flux:icon name="eye" class="size-5 text-green-600" />
                </div>
                <flux:heading size="sm">Visits</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Total</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($visitStats['total']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $visitStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $visitStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">QR Scans</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($visitStats['qr_scans']) }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Mailings Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-100">
                    <flux:icon name="envelope" class="size-5 text-purple-600" />
                </div>
                <flux:heading size="sm">Mailings</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Total</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($mailingStats['total']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Executed</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($mailingStats['executed']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Delivered</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($mailingStats['delivered']) }}
                    </flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Failed</flux:text>
                    <flux:text class="text-2xl font-semibold text-red-600">{{ $mailingStats['failed'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Events Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100">
                    <flux:icon name="cursor-arrow-rays" class="size-5 text-amber-600" />
                </div>
                <flux:heading size="sm">Conversions</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Total Events</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($eventStats['total']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $eventStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $eventStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">CTA Clicks</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ number_format($eventStats['cta_clicks']) }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Campaigns</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Steps</flux:table.column>
                    <flux:table.column>Enrolled</flux:table.column>
                    <flux:table.column>In Progress</flux:table.column>
                    <flux:table.column>Completed</flux:table.column>
                    <flux:table.column>Failed</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($campaigns as $campaign)
                    <flux:table.row wire:key="campaign-{{ $campaign['id'] }}">
                        <flux:table.cell class="font-medium">{{ $campaign['name'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $campaign['status']->color() }}">
                                {{ $campaign['status']->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $campaign['steps_count'] }}</flux:table.cell>
                        <flux:table.cell>{{ $campaign['total_enrolled'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="blue">{{ $campaign['in_progress'] }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="green">{{ $campaign['completed'] }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="red">{{ $campaign['failed'] }}</flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-8 text-center text-gray-400">
                            No campaigns yet.
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Redirect Campaigns Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Redirect Campaigns</flux:heading>
            <flux:text class="mt-1 text-sm text-gray-500">Banner ads and partnership links (/r/slug)</flux:text>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Slug</flux:table.column>
                    <flux:table.column>Destination</flux:table.column>
                    <flux:table.column>Source</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Total Clicks</flux:table.column>
                    <flux:table.column>Today</flux:table.column>
                    <flux:table.column>This Week</flux:table.column>
                    <flux:table.column>This Month</flux:table.column>
                    <flux:table.column>Signups</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($redirectStats as $redirect)
                    <flux:table.row wire:key="redirect-{{ $redirect['id'] }}">
                        <flux:table.cell class="font-mono text-sm">/r/{{ $redirect['slug'] }}</flux:table.cell>
                        <flux:table.cell>{{ $redirect['destination_path'] }}</flux:table.cell>
                        <flux:table.cell>{{ $redirect['utm_source'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $redirect['is_active'] ? 'green' : 'zinc' }}">
                                {{ $redirect['is_active'] ? 'Active' : 'Inactive' }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="font-semibold">{{ number_format($redirect['total_clicks']) }}</flux:table.cell>
                        <flux:table.cell>{{ $redirect['clicks_today'] }}</flux:table.cell>
                        <flux:table.cell>{{ $redirect['clicks_this_week'] }}</flux:table.cell>
                        <flux:table.cell>{{ $redirect['clicks_this_month'] }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="blue">{{ $redirect['attributed_signups'] }}</flux:badge>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-8 text-center text-gray-400">
                            No redirect campaigns yet.
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Recent Activity Tables -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Recent Visits -->
        <div class="rounded-lg border border-border bg-white">
            <div class="border-b border-border px-4 py-3">
                <flux:heading size="sm">Recent Visits</flux:heading>
            </div>
            <div class="px-4 pb-4">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Lead</flux:table.column>
                        <flux:table.column>Source</flux:table.column>
                        <flux:table.column>Step</flux:table.column>
                        <flux:table.column>Time (EST)</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($recentVisits as $visit)
                        <flux:table.row wire:key="visit-{{ $visit['id'] }}">
                            <flux:table.cell>{{ $visit['lead_name'] }}</flux:table.cell>
                            <flux:table.cell>{{ $visit['source'] }}</flux:table.cell>
                            <flux:table.cell>{{ $visit['step_name'] }}</flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ $visit['visited_at']->eastern()->format('M j, g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                        @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="py-8 text-center text-gray-400">
                                No visits yet.
                            </flux:table.cell>
                        </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="rounded-lg border border-border bg-white">
            <div class="border-b border-border px-4 py-3">
                <flux:heading size="sm">Recent Conversions</flux:heading>
            </div>
            <div class="px-4 pb-4">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Lead</flux:table.column>
                        <flux:table.column>Event</flux:table.column>
                        <flux:table.column>Step</flux:table.column>
                        <flux:table.column>Time (EST)</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($recentEvents as $event)
                        <flux:table.row wire:key="event-{{ $event['id'] }}">
                            <flux:table.cell>{{ $event['lead_name'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="amber">{{ $event['event_type'] }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>{{ $event['step_name'] }}</flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ $event['occurred_at']->eastern()->format('M j, g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                        @empty
                        <flux:table.row>
                            <flux:table.cell colspan="4" class="py-8 text-center text-gray-400">
                                No conversion events yet.
                            </flux:table.cell>
                        </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    </div>
</div>