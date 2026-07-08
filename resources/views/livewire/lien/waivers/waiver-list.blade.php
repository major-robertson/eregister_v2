<div class="space-y-6">
    <x-ui.page-header title="All Waivers">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Waivers', 'url' => route('lien.waivers.index')],
                ['label' => 'All Waivers'],
            ]" />
        </x-slot:breadcrumbs>
        <x-slot:actions>
            <flux:button href="{{ route('lien.waivers.create') }}" variant="primary" icon="plus" wire:navigate>
                New waiver
            </flux:button>
        </x-slot:actions>
    </x-ui.page-header>

    @if ($projectFilterName)
        <flux:callout icon="funnel">
            <flux:callout.text>
                Showing waivers for <span class="font-medium">{{ $projectFilterName }}</span>.
                <a href="{{ route('lien.waivers.list') }}" class="font-medium underline" wire:navigate>Show all waivers</a>
            </flux:callout.text>
        </flux:callout>
    @endif

    <x-ui.card>
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by counterparty or project..."
                    icon="magnifying-glass"
                />
            </div>
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="statusFilter">
                    <option value="">All Statuses</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}">{{ $status->label() }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="w-full sm:w-48">
                <flux:select wire:model.live="directionFilter">
                    <option value="">All Directions</option>
                    @foreach ($directions as $direction)
                        <option value="{{ $direction->value }}">{{ $direction->label() }}</option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        @if ($waivers->isEmpty())
            <div class="py-12 text-center">
                <flux:icon name="document-check" class="mx-auto h-12 w-12 text-zinc-400" />
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">No waivers found</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    @if ($search || $statusFilter || $directionFilter)
                        Try adjusting your filters.
                    @else
                        Create your first waiver. Downloading is free.
                    @endif
                </p>
                <div class="mt-6">
                    <flux:button href="{{ route('lien.waivers.create') }}" variant="primary" icon="plus" wire:navigate>
                        New waiver
                    </flux:button>
                </div>
            </div>
        @else
            <flux:table :paginate="$waivers">
                <flux:table.columns>
                    <flux:table.column>Counterparty</flux:table.column>
                    <flux:table.column>Project</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Direction</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column align="end">Amount</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($waivers as $waiver)
                        <flux:table.row
                            wire:key="waiver-{{ $waiver->id }}"
                            wire:click="openWaiver('{{ $waiver->public_id }}')"
                            class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <flux:table.cell class="whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $waiver->counterpartyDisplayName() }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $waiver->project?->name ?? '-' }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $waiver->kind->shortLabel() }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $waiver->direction->label() }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">
                                <flux:badge size="sm" :color="$waiver->status->color()">
                                    {{ $waiver->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $waiver->created_at->format('M j, Y') }}
                            </flux:table.cell>
                            <flux:table.cell align="end" class="whitespace-nowrap text-sm text-zinc-900 dark:text-white">
                                {{ $waiver->formattedAmount() ?? '-' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @endif
    </x-ui.card>
</div>
