<div class="space-y-6">
    <div>
        <flux:heading size="xl">Lien Stats</flux:heading>
        <flux:text class="mt-1">Overview of lien filing revenue and filings.</flux:text>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Revenue Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100">
                    <flux:icon name="banknotes" class="size-5 text-green-600" />
                </div>
                <div>
                    <flux:heading size="sm">Revenue</flux:heading>
                    <flux:text class="text-xs text-gray-400">All lien products, waivers included</flux:text>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($revenueStats['today']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($revenueStats['yesterday']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($revenueStats['this_week']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($revenueStats['this_month']) }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Filings Started Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="pencil-square" class="size-5 text-blue-600" />
                </div>
                <flux:heading size="sm">Filings Started</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['started']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['started']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['started']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['started']['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Filings Paid Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100">
                    <flux:icon name="document-text" class="size-5 text-amber-600" />
                </div>
                <flux:heading size="sm">Filings Paid</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['paid']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['paid']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['paid']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $filingStats['paid']['this_month'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Filings Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Filings</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Document</flux:table.column>
                    <flux:table.column>State</flux:table.column>
                    <flux:table.column>Amount</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Date (EST)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentFilings as $filing)
                        <flux:table.row wire:key="filing-{{ $filing['id'] }}">
                            <flux:table.cell>{{ $filing['business'] }}</flux:table.cell>
                            <flux:table.cell>{{ $filing['name'] }}</flux:table.cell>
                            <flux:table.cell class="text-gray-600">{{ $filing['email'] }}</flux:table.cell>
                            <flux:table.cell>{{ $filing['document'] }}</flux:table.cell>
                            <flux:table.cell>{{ $filing['state'] }}</flux:table.cell>
                            <flux:table.cell class="font-medium">
                                @if ($filing['amount'])
                                    {{ $filing['amount'] }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $filing['status_color'] }}">
                                    {{ $filing['status_label'] }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ ($filing['paid_at'] ?? $filing['created_at'])?->eastern()->format('M j, Y g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="py-8 text-center text-gray-400">
                                No filings yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Lien Waivers -->
    <div class="border-t border-border pt-6">
        <flux:heading size="lg">Lien Waivers</flux:heading>
        <flux:text class="mt-1">Subscription revenue, waiver volume, and the signature pipeline.</flux:text>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Waiver Revenue Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-100">
                    <flux:icon name="credit-card" class="size-5 text-indigo-600" />
                </div>
                <div>
                    <flux:heading size="sm">Waiver Revenue</flux:heading>
                    <flux:text class="text-xs text-gray-400">Subscriptions and renewals</flux:text>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($waiverRevenueStats['today']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($waiverRevenueStats['yesterday']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($waiverRevenueStats['this_week']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($waiverRevenueStats['this_month']) }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Waivers Created Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="clipboard-document-check" class="size-5 text-blue-600" />
                </div>
                <div>
                    <flux:heading size="sm">Waivers Created</flux:heading>
                    <flux:text class="text-xs text-gray-400">Every saved waiver, deleted ones included</flux:text>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['created']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['created']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['created']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['created']['this_month'] }}</flux:text>
                </div>
            </div>
            <div class="mt-4 border-t border-border pt-3">
                <flux:text class="text-xs text-gray-500">
                    Sent for signature this month: <span class="font-semibold text-gray-700">{{ $waiverStats['sent']['this_month'] }}</span>
                </flux:text>
            </div>
        </div>

        <!-- Waivers Signed Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-100">
                    <flux:icon name="check-badge" class="size-5 text-emerald-600" />
                </div>
                <div>
                    <flux:heading size="sm">Waivers Signed</flux:heading>
                    <flux:text class="text-xs text-gray-400">E-signed or signed copy uploaded</flux:text>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['signed']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['signed']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['signed']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverStats['signed']['this_month'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Waiver Subscriptions Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-100">
                    <flux:icon name="user-group" class="size-5 text-purple-600" />
                </div>
                <div>
                    <flux:heading size="sm">Waiver Subscriptions</flux:heading>
                    <flux:text class="text-xs text-gray-400">Yearly plans counted at 1/12</flux:text>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Active</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverSubscriptionStats['active'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Seats Billed</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverSubscriptionStats['seats'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">MRR</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $this->formatCents($waiverSubscriptionStats['mrr_cents']) }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">New This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $waiverSubscriptionStats['new_this_month'] }}</flux:text>
                </div>
            </div>
            <div class="mt-4 border-t border-border pt-3">
                <flux:text class="text-xs text-gray-500">
                    Cancelling at period end: <span class="font-semibold text-gray-700">{{ $waiverSubscriptionStats['cancelling'] }}</span>
                </flux:text>
            </div>
        </div>

        <!-- Waiver Pipeline Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100">
                    <flux:icon name="chart-bar" class="size-5 text-amber-600" />
                </div>
                <div>
                    <flux:heading size="sm">Waiver Pipeline</flux:heading>
                    <flux:text class="text-xs text-gray-400">All time, by status</flux:text>
                </div>
            </div>
            <div class="space-y-2">
                @foreach ($waiverPipeline as $stage)
                    <div class="flex items-center justify-between">
                        <flux:badge size="sm" color="{{ $stage['color'] }}">{{ $stage['label'] }}</flux:badge>
                        <flux:text class="text-lg font-semibold">{{ $stage['count'] }}</flux:text>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Waiver Mix Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100">
                    <flux:icon name="arrows-right-left" class="size-5 text-teal-600" />
                </div>
                <div>
                    <flux:heading size="sm">Waiver Mix</flux:heading>
                    <flux:text class="text-xs text-gray-400">All time</flux:text>
                </div>
            </div>
            <div class="space-y-2">
                @foreach ($waiverMix['directions'] as $direction)
                    <div class="flex items-center justify-between">
                        <flux:text class="text-sm text-gray-500">{{ $direction['label'] }}</flux:text>
                        <flux:text class="text-lg font-semibold">{{ $direction['count'] }}</flux:text>
                    </div>
                @endforeach
                <div class="flex items-center justify-between border-t border-border pt-2">
                    <flux:text class="text-sm text-gray-500">Built by our engine</flux:text>
                    <flux:text class="text-lg font-semibold">{{ $waiverMix['sources']['generated'] }}</flux:text>
                </div>
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm text-gray-500">Uploaded from outside</flux:text>
                    <flux:text class="text-lg font-semibold">{{ $waiverMix['sources']['uploaded'] }}</flux:text>
                </div>
            </div>
            <div class="mt-4 border-t border-border pt-3">
                <flux:text class="text-xs text-gray-500">Top states</flux:text>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse ($waiverMix['top_states'] as $state => $count)
                        <flux:badge size="sm" color="zinc">{{ $state }} · {{ $count }}</flux:badge>
                    @empty
                        <flux:text class="text-sm text-gray-400">—</flux:text>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Waiver Subscriptions Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Waiver Subscribers</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>Plan</flux:table.column>
                    <flux:table.column>Seats</flux:table.column>
                    <flux:table.column>MRR</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Started (EST)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($waiverSubscriptionRows as $subscription)
                        <flux:table.row wire:key="waiver-sub-{{ $subscription['id'] }}">
                            <flux:table.cell>{{ $subscription['business'] }}</flux:table.cell>
                            <flux:table.cell>{{ $subscription['plan'] }}</flux:table.cell>
                            <flux:table.cell>{{ $subscription['seats'] }}</flux:table.cell>
                            <flux:table.cell class="font-medium">{{ $subscription['mrr'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $subscription['status_color'] }}">
                                    {{ $subscription['status_label'] }}
                                </flux:badge>
                                @if ($subscription['ends_at'])
                                    <span class="ml-1 text-xs text-gray-400">
                                        {{ $subscription['ends_at']->eastern()->format('M j, Y') }}
                                    </span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ $subscription['created_at']?->eastern()->format('M j, Y g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="py-8 text-center text-gray-400">
                                No waiver subscriptions yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Recent Waivers Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Waivers</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Waiver</flux:table.column>
                    <flux:table.column>Direction</flux:table.column>
                    <flux:table.column>State</flux:table.column>
                    <flux:table.column>Amount</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Date (EST)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentWaivers as $waiver)
                        <flux:table.row wire:key="waiver-{{ $waiver['id'] }}">
                            <flux:table.cell>{{ $waiver['business'] }}</flux:table.cell>
                            <flux:table.cell>{{ $waiver['name'] }}</flux:table.cell>
                            <flux:table.cell class="text-gray-600">{{ $waiver['email'] }}</flux:table.cell>
                            <flux:table.cell>{{ $waiver['kind'] }}</flux:table.cell>
                            <flux:table.cell>{{ $waiver['direction'] }}</flux:table.cell>
                            <flux:table.cell>{{ $waiver['state'] }}</flux:table.cell>
                            <flux:table.cell class="font-medium">
                                @if ($waiver['amount'])
                                    {{ $waiver['amount'] }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $waiver['status_color'] }}">
                                    {{ $waiver['status_label'] }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ ($waiver['signed_at'] ?? $waiver['created_at'])?->eastern()->format('M j, Y g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="9" class="py-8 text-center text-gray-400">
                                No waivers yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
