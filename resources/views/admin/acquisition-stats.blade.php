<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <flux:heading size="lg">Acquisition by First Touch</flux:heading>
            <flux:text class="mt-1">
                Signups since {{ $since->format('M j, Y') }} (EST) by channel and landing section. Paid and revenue are each business's lifetime succeeded payments, counted in the month its owner signed up.
                Updated {{ $generatedAt->eastern()->format('M j, Y g:i A') }} EST.
            </flux:text>
        </div>
        <div class="w-48">
            <flux:select wire:model.live="channel" size="sm" label="Channel">
                <flux:select.option value="">All channels</flux:select.option>
                @foreach ($channels as $option)
                    <flux:select.option value="{{ $option }}">{{ ucfirst($option) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>
    </div>

    <!-- Month by channel -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Signups, Paid and Revenue by Month and Channel</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Month</flux:table.column>
                    <flux:table.column>Channel</flux:table.column>
                    <flux:table.column>Section</flux:table.column>
                    <flux:table.column align="end">Signups</flux:table.column>
                    <flux:table.column align="end">Paid Businesses</flux:table.column>
                    <flux:table.column align="end">Revenue</flux:table.column>
                    <flux:table.column align="end">Conversion</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($months as $month)
                        @foreach ($month['rows'] as $row)
                            <flux:table.row>
                                <flux:table.cell class="text-gray-600">{{ $month['label'] }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" color="{{ match ($row['channel']) { 'organic' => 'green', 'ads' => 'amber', 'campaign' => 'purple', default => 'zinc' } }}">
                                        {{ ucfirst($row['channel']) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="text-gray-600">
                                    @if ($row['section'])
                                        <span class="font-mono text-xs">{{ $row['section'] }}</span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell align="end">{{ number_format($row['signups']) }}</flux:table.cell>
                                <flux:table.cell align="end">{{ number_format($row['paying_businesses']) }}</flux:table.cell>
                                <flux:table.cell align="end">${{ number_format($row['revenue_cents'] / 100, 2) }}</flux:table.cell>
                                <flux:table.cell align="end">{{ number_format($row['conversion_rate'], 1) }}%</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                        <flux:table.row class="bg-gray-50">
                            <flux:table.cell class="font-semibold">{{ $month['label'] }} total</flux:table.cell>
                            <flux:table.cell></flux:table.cell>
                            <flux:table.cell></flux:table.cell>
                            <flux:table.cell align="end" class="font-semibold">{{ number_format($month['total']['signups']) }}</flux:table.cell>
                            <flux:table.cell align="end" class="font-semibold">{{ number_format($month['total']['paying_businesses']) }}</flux:table.cell>
                            <flux:table.cell align="end" class="font-semibold">${{ number_format($month['total']['revenue_cents'] / 100, 2) }}</flux:table.cell>
                            <flux:table.cell align="end" class="font-semibold">{{ number_format($month['total']['conversion_rate'], 1) }}%</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="py-8 text-center text-gray-400">
                                No signups in this period.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Top landing paths -->
    <div class="rounded-lg border border-border bg-white">
        <div class="border-b border-border px-4 py-3">
            <flux:heading size="sm">Top 20 Landing Paths</flux:heading>
        </div>
        <div class="px-4 pb-4">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Landing Path</flux:table.column>
                    <flux:table.column align="end">Signups</flux:table.column>
                    <flux:table.column align="end">Paid Businesses</flux:table.column>
                    <flux:table.column align="end">Revenue</flux:table.column>
                    <flux:table.column align="end">Conversion</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($topPaths as $path)
                        <flux:table.row>
                            <flux:table.cell class="text-gray-600">
                                <span class="font-mono text-xs">{{ $path['landing_path'] }}</span>
                            </flux:table.cell>
                            <flux:table.cell align="end">{{ number_format($path['signups']) }}</flux:table.cell>
                            <flux:table.cell align="end">{{ number_format($path['paying_businesses']) }}</flux:table.cell>
                            <flux:table.cell align="end">${{ number_format($path['revenue_cents'] / 100, 2) }}</flux:table.cell>
                            <flux:table.cell align="end">{{ number_format($path['conversion_rate'], 1) }}%</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5" class="py-8 text-center text-gray-400">
                                No signups in this period.
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
