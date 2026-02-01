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
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Steps</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Enrolled</th>
                        <th class="px-4 py-3 font-medium text-gray-700">In Progress</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Completed</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Failed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($campaigns as $campaign)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $campaign['name'] }}</td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="{{ $campaign['status']->color() }}">
                                {{ $campaign['status']->label() }}
                            </flux:badge>
                        </td>
                        <td class="px-4 py-3">{{ $campaign['steps_count'] }}</td>
                        <td class="px-4 py-3">{{ $campaign['total_enrolled'] }}</td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="blue">{{ $campaign['in_progress'] }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="green">{{ $campaign['completed'] }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm" color="red">{{ $campaign['failed'] }}</flux:badge>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                            No campaigns yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity Tables -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <!-- Recent Visits -->
        <div class="rounded-lg border border-border bg-white">
            <div class="border-b border-border px-4 py-3">
                <flux:heading size="sm">Recent Visits</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700">Lead</th>
                            <th class="px-4 py-3 font-medium text-gray-700">Source</th>
                            <th class="px-4 py-3 font-medium text-gray-700">Step</th>
                            <th class="px-4 py-3 font-medium text-gray-700">Time (EST)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($recentVisits as $visit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $visit['lead_name'] }}</td>
                            <td class="px-4 py-3">{{ $visit['source'] }}</td>
                            <td class="px-4 py-3">{{ $visit['step_name'] }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $visit['visited_at']->setTimezone('America/New_York')->format('M j, g:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                No visits yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="rounded-lg border border-border bg-white">
            <div class="border-b border-border px-4 py-3">
                <flux:heading size="sm">Recent Conversions</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-700">Lead</th>
                            <th class="px-4 py-3 font-medium text-gray-700">Event</th>
                            <th class="px-4 py-3 font-medium text-gray-700">Step</th>
                            <th class="px-4 py-3 font-medium text-gray-700">Time (EST)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($recentEvents as $event)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $event['lead_name'] }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="amber">{{ $event['event_type'] }}</flux:badge>
                            </td>
                            <td class="px-4 py-3">{{ $event['step_name'] }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $event['occurred_at']->setTimezone('America/New_York')->format('M j, g:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                No conversion events yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>