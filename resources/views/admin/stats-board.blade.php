<div class="space-y-6">
    <div>
        <flux:heading size="xl">Stats Dashboard</flux:heading>
        <flux:text class="mt-1">Overview of signups, payments, and subscriptions.</flux:text>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
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
    </div>

    <!-- Recent Signups Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Signups</flux:heading>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Email</th>
                        <th class="px-4 py-3 font-medium text-gray-700">State</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Has Business</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Lien Ready</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Subscribed</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Signup Date (EST)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentSignups as $signup)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $signup['name'] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $signup['email'] }}</td>
                            <td class="px-4 py-3">
                                @if ($signup['state'])
                                    {{ $signup['state'] }}
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $signup['has_business'] ? 'green' : 'zinc' }}">
                                    {{ $signup['has_business'] ? 'Yes' : 'No' }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                @if (!$signup['has_business'])
                                    <flux:badge size="sm" color="zinc">N/A</flux:badge>
                                @else
                                    <flux:badge size="sm" color="{{ $signup['lien_ready'] ? 'green' : 'amber' }}">
                                        {{ $signup['lien_ready'] ? 'Yes' : 'No' }}
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if (!$signup['has_business'])
                                    <flux:badge size="sm" color="zinc">N/A</flux:badge>
                                @else
                                    <flux:badge size="sm" color="{{ $signup['subscribed'] ? 'green' : 'zinc' }}">
                                        {{ $signup['subscribed'] ? 'Yes' : 'No' }}
                                    </flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $signup['created_at']->setTimezone('America/New_York')->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                No signups yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Payments</flux:heading>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Email</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Amount</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Type</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Payment Date (EST)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentPayments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $payment['name'] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $payment['email'] }}</td>
                            <td class="px-4 py-3 font-medium">{{ $payment['amount'] }}</td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $payment['type'] === 'Subscription' ? 'purple' : 'blue' }}">
                                    {{ $payment['type'] }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="green">{{ $payment['status'] }}</flux:badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $payment['paid_at']->setTimezone('America/New_York')->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                No payments yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Subscriptions Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Subscriptions</flux:heading>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Email</th>
                        <th class="px-4 py-3 font-medium text-gray-700">State</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Start Date (EST)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentSubscriptions as $subscription)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $subscription['name'] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $subscription['email'] }}</td>
                            <td class="px-4 py-3">
                                @if ($subscription['state'])
                                    {{ $subscription['state'] }}
                                @else
                                    <span class="text-gray-400">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $subscription['status_color'] }}">
                                    {{ $subscription['status'] }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $subscription['created_at']->setTimezone('America/New_York')->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                No subscriptions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
