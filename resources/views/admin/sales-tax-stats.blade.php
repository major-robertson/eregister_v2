<div class="space-y-6">
    <div>
        <flux:heading size="xl">Sales Tax Stats</flux:heading>
        <flux:text class="mt-1">Overview of sales tax permit revenue and registrations.</flux:text>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
        <!-- Revenue Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100">
                    <flux:icon name="banknotes" class="size-5 text-green-600" />
                </div>
                <flux:heading size="sm">Revenue</flux:heading>
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

        <!-- Registrations Started Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="pencil-square" class="size-5 text-blue-600" />
                </div>
                <flux:heading size="sm">Registrations Started</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['started']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['started']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['started']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['started']['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Registrations Paid Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-purple-100">
                    <flux:icon name="receipt-percent" class="size-5 text-purple-600" />
                </div>
                <flux:heading size="sm">Registrations Paid</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['paid']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['paid']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['paid']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $registrationStats['paid']['this_month'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Registrations Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Registrations</flux:heading>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">Business</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Email</th>
                        <th class="px-4 py-3 font-medium text-gray-700">States</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Amount</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Progress</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Date (EST)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentRegistrations as $registration)
                        <tr class="hover:bg-gray-50" wire:key="registration-{{ $registration['id'] }}">
                            <td class="px-4 py-3">{{ $registration['business'] }}</td>
                            <td class="px-4 py-3">{{ $registration['name'] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $registration['email'] }}</td>
                            <td class="px-4 py-3">{{ $registration['states'] }}</td>
                            <td class="px-4 py-3 font-medium">
                                @if ($registration['amount'])
                                    {{ $registration['amount'] }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $registration['paid_at'] ? 'green' : 'zinc' }}">
                                    {{ $registration['status'] }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $done = $registration['progress']['done'];
                                    $total = $registration['progress']['total'];
                                    $percent = $total > 0 ? round($done / $total * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-1">
                                    <flux:text class="text-sm font-medium">{{ $done }}/{{ $total }} steps</flux:text>
                                    @if ($registration['phase'])
                                        <flux:badge size="sm" color="zinc">{{ ucfirst($registration['phase']) }}</flux:badge>
                                    @endif
                                </div>
                                <div class="mt-1 h-1.5 w-32 overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full bg-blue-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ ($registration['paid_at'] ?? $registration['created_at'])?->eastern()->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                No registrations yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
