<div class="space-y-6">
    <div>
        <flux:heading size="xl">Stats Dashboard</flux:heading>
        <flux:text class="mt-1">Overview of signups, payments, and subscriptions.</flux:text>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Signups Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="users" class="size-5 text-blue-600" />
                </div>
                <flux:heading size="sm">Signups</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $signupStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $signupStats['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $signupStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $signupStats['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Payments Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100">
                    <flux:icon name="credit-card" class="size-5 text-green-600" />
                </div>
                <flux:heading size="sm">Payments</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $paymentStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $paymentStats['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $paymentStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $paymentStats['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Subscriptions Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-100">
                    <flux:icon name="arrow-path" class="size-5 text-purple-600" />
                </div>
                <flux:heading size="sm">Subscriptions</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $subscriptionStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $subscriptionStats['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $subscriptionStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $subscriptionStats['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Lien Filings Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-amber-100">
                    <flux:icon name="document-text" class="size-5 text-amber-600" />
                </div>
                <flux:heading size="sm">Lien Filings Paid</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $lienFilingStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $lienFilingStats['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $lienFilingStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $lienFilingStats['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Sales Tax Registrations Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-teal-100">
                    <flux:icon name="receipt-percent" class="size-5 text-teal-600" />
                </div>
                <flux:heading size="sm">Sales Tax Registrations Paid</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $salesTaxStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $salesTaxStats['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $salesTaxStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $salesTaxStats['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Formations Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-100">
                    <flux:icon name="building-office-2" class="size-5 text-indigo-600" />
                </div>
                <flux:heading size="sm">Formations Paid</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['this_month'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Signups Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Signups</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Landing Path</flux:table.column>
                    <flux:table.column>Referrer</flux:table.column>
                    <flux:table.column>State</flux:table.column>
                    <flux:table.column>Has Business</flux:table.column>
                    <flux:table.column>Lien Ready</flux:table.column>
                    <flux:table.column>Subscribed</flux:table.column>
                    <flux:table.column>Signup Date (EST)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentSignups as $signup)
                        <flux:table.row>
                            <flux:table.cell class="text-gray-600">{{ $signup['email'] }}</flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                @if ($signup['landing_path'])
                                    <span class="font-mono text-xs">{{ $signup['landing_path'] }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                @if ($signup['referrer'])
                                    <span class="font-mono text-xs">{{ $signup['referrer'] }}</span>
                                @else
                                    <span class="text-gray-400">Direct</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if ($signup['state'])
                                    {{ $signup['state'] }}
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $signup['has_business'] ? 'green' : 'zinc' }}">
                                    {{ $signup['has_business'] ? 'Yes' : 'No' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if (!$signup['has_business'])
                                    <flux:badge size="sm" color="zinc">N/A</flux:badge>
                                @else
                                    <flux:badge size="sm" color="{{ $signup['lien_ready'] ? 'green' : 'amber' }}">
                                        {{ $signup['lien_ready'] ? 'Yes' : 'No' }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if (!$signup['has_business'])
                                    <flux:badge size="sm" color="zinc">N/A</flux:badge>
                                @else
                                    <flux:badge size="sm" color="{{ $signup['subscribed'] ? 'green' : 'zinc' }}">
                                        {{ $signup['subscribed'] ? 'Yes' : 'No' }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ $signup['created_at']->eastern()->format('M j, Y g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="py-8 text-center text-gray-400">
                                No signups yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Payments</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Amount</flux:table.column>
                    <flux:table.column>Kind</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Payment Date (EST)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentPayments as $payment)
                        <flux:table.row>
                            <flux:table.cell class="text-gray-600">{{ $payment['email'] }}</flux:table.cell>
                            <flux:table.cell class="font-medium">{{ $payment['amount'] }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $payment['kind_color'] }}">
                                    {{ $payment['kind'] }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $payment['type'] === 'Subscription' ? 'purple' : 'blue' }}">
                                    {{ $payment['type'] }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ $payment['paid_at']->eastern()->format('M j, Y g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center text-gray-400">
                                No payments yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Recent Subscriptions Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Subscriptions</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>State</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Start Date (EST)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentSubscriptions as $subscription)
                        <flux:table.row>
                            <flux:table.cell>{{ $subscription['name'] }}</flux:table.cell>
                            <flux:table.cell class="text-gray-600">{{ $subscription['email'] }}</flux:table.cell>
                            <flux:table.cell>
                                @if ($subscription['state'])
                                    {{ $subscription['state'] }}
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $subscription['status_color'] }}">
                                    {{ $subscription['status'] }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ $subscription['created_at']->eastern()->format('M j, Y g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center text-gray-400">
                                No subscriptions yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
