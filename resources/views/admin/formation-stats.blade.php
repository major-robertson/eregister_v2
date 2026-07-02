<div class="space-y-6">
    <div>
        <flux:heading size="xl">Formation Stats</flux:heading>
        <flux:text class="mt-1">Overview of LLC formation revenue and formations.</flux:text>
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

        <!-- Formations Started Card -->
        <div class="rounded-lg border border-border bg-white p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex size-10 items-center justify-center rounded-lg bg-blue-100">
                    <flux:icon name="pencil-square" class="size-5 text-blue-600" />
                </div>
                <flux:heading size="sm">Formations Started</flux:heading>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <flux:text class="text-sm text-gray-500">Today</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['started']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['started']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['started']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['started']['this_month'] }}</flux:text>
                </div>
            </div>
        </div>

        <!-- Formations Paid Card -->
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
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['paid']['today'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">Yesterday</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['paid']['yesterday'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Week</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['paid']['this_week'] }}</flux:text>
                </div>
                <div>
                    <flux:text class="text-sm text-gray-500">This Month</flux:text>
                    <flux:text class="text-2xl font-semibold">{{ $formationStats['paid']['this_month'] }}</flux:text>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Formations Table -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Last 20 Formations</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Business</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>State</flux:table.column>
                    <flux:table.column>Amount</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Progress</flux:table.column>
                    <flux:table.column>Date (EST)</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($recentFormations as $formation)
                        <flux:table.row wire:key="formation-{{ $formation['id'] }}">
                            <flux:table.cell>{{ $formation['business'] }}</flux:table.cell>
                            <flux:table.cell>{{ $formation['name'] }}</flux:table.cell>
                            <flux:table.cell class="text-gray-600">{{ $formation['email'] }}</flux:table.cell>
                            <flux:table.cell>{{ $formation['state'] }}</flux:table.cell>
                            <flux:table.cell class="font-medium">
                                @if ($formation['amount'])
                                    {{ $formation['amount'] }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" color="{{ $formation['paid_at'] ? 'green' : 'zinc' }}">
                                    {{ $formation['status'] }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                @php
                                    $done = $formation['progress']['done'];
                                    $total = $formation['progress']['total'];
                                    $percent = $total > 0 ? round($done / $total * 100) : 0;
                                @endphp
                                <div class="flex items-center gap-1">
                                    <flux:text class="text-sm font-medium">{{ $done }}/{{ $total }} steps</flux:text>
                                    @if ($formation['phase'])
                                        <flux:badge size="sm" color="zinc">{{ ucfirst($formation['phase']) }}</flux:badge>
                                    @endif
                                </div>
                                <div class="mt-1 h-1.5 w-32 overflow-hidden rounded-full bg-gray-200">
                                    <div class="h-full rounded-full bg-blue-500" style="width: {{ $percent }}%"></div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="text-gray-600">
                                {{ ($formation['paid_at'] ?? $formation['created_at'])?->eastern()->format('M j, Y g:i A') }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8" class="py-8 text-center text-gray-400">
                                No formations yet.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
