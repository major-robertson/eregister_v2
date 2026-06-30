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
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-border bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-700">Business</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Name</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Email</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Document</th>
                        <th class="px-4 py-3 font-medium text-gray-700">State</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Amount</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Status</th>
                        <th class="px-4 py-3 font-medium text-gray-700">Date (EST)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($recentFilings as $filing)
                        <tr class="hover:bg-gray-50" wire:key="filing-{{ $filing['id'] }}">
                            <td class="px-4 py-3">{{ $filing['business'] }}</td>
                            <td class="px-4 py-3">{{ $filing['name'] }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $filing['email'] }}</td>
                            <td class="px-4 py-3">{{ $filing['document'] }}</td>
                            <td class="px-4 py-3">{{ $filing['state'] }}</td>
                            <td class="px-4 py-3 font-medium">
                                @if ($filing['amount'])
                                    {{ $filing['amount'] }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <flux:badge size="sm" color="{{ $filing['status_color'] }}">
                                    {{ $filing['status_label'] }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ ($filing['paid_at'] ?? $filing['created_at'])?->eastern()->format('M j, Y g:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                No filings yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
