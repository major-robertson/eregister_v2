<div class="space-y-6">
    <x-ui.page-header title="Lien Waivers">
        <x-slot:actions>
            <div class="flex items-center gap-3">
                <flux:button href="{{ route('lien.waivers.list') }}" variant="ghost" wire:navigate>
                    All waivers
                </flux:button>
                <flux:button href="{{ route('lien.waivers.create') }}" variant="primary" icon="plus" wire:navigate>
                    New waiver
                </flux:button>
            </div>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Status Tiles --}}
    <div class="grid gap-4 sm:grid-cols-3">
        {{-- Drafts --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $draftCount }}</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Drafts &amp; Generated</div>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                    <flux:icon name="pencil-square" class="size-5 text-zinc-500" />
                </div>
            </div>
        </div>

        {{-- Awaiting Signature --}}
        <div @class([
            'rounded-xl border p-5',
            'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' => $awaitingCount > 0,
            'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' => $awaitingCount === 0,
        ])>
            <div class="flex items-start justify-between">
                <div>
                    <div @class([
                        'text-3xl font-bold',
                        'text-amber-600 dark:text-amber-400' => $awaitingCount > 0,
                        'text-zinc-900 dark:text-white' => $awaitingCount === 0,
                    ])>{{ $awaitingCount }}</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Awaiting Signature</div>
                </div>
                <div @class([
                    'flex size-10 items-center justify-center rounded-lg',
                    'bg-amber-100 dark:bg-amber-800/50' => $awaitingCount > 0,
                    'bg-zinc-100 dark:bg-zinc-700' => $awaitingCount === 0,
                ])>
                    <flux:icon name="clock" @class([
                        'size-5',
                        'text-amber-600 dark:text-amber-400' => $awaitingCount > 0,
                        'text-zinc-400' => $awaitingCount === 0,
                    ]) />
                </div>
            </div>
        </div>

        {{-- Signed This Month --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-800">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $signedThisMonthCount }}</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Signed This Month</div>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-800/50">
                    <flux:icon name="check-circle" class="size-5 text-green-600 dark:text-green-400" />
                </div>
            </div>
        </div>
    </div>

    {{-- Free-tier meter / seat status --}}
    @if (! $hasPaidAccess)
        <x-ui.card class="border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <flux:icon name="sparkles" class="size-5 text-blue-600 dark:text-blue-400" />
                        <span class="font-medium text-zinc-900 dark:text-white">
                            {{ $savedThisMonth }} of {{ $freeSavesLimit }} free saves used this month
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        @if ($businessSubscribed)
                            Your team has Lien Waiver Pro, but you don't have a seat yet —
                            {{ $canManageSeats ? 'assign yourself one to go unlimited.' : 'ask an owner or admin to assign you one.' }}
                        @else
                            Every free waiver includes downloads, e-sign, and signed storage. Upgrade for unlimited waivers per seat.
                        @endif
                    </p>
                    <div class="mt-3 h-2 w-full max-w-xs overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                        <div class="h-full rounded-full bg-blue-600"
                            style="width: {{ $freeSavesLimit > 0 ? min(100, (int) round($savedThisMonth / $freeSavesLimit * 100)) : 100 }}%"></div>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if ($businessSubscribed && $canManageSeats)
                        <flux:button href="{{ route('lien.waivers.seats') }}" variant="primary">
                            Manage seats
                        </flux:button>
                    @elseif (! $businessSubscribed)
                        <flux:modal.trigger name="waiver-upsell">
                            <flux:button variant="ghost" size="sm">See what's included</flux:button>
                        </flux:modal.trigger>
                        <flux:button href="{{ route('lien.waivers.subscribe') }}" variant="primary">
                            Upgrade
                        </flux:button>
                    @endif
                </div>
            </div>
        </x-ui.card>

        <flux:modal name="waiver-upsell" class="max-w-md">
            <x-lien.waiver-upsell heading="Upgrade to Waiver Pro" />
        </flux:modal>
    @elseif ($canManageSeats)
        <div class="flex justify-end">
            <flux:button href="{{ route('lien.waivers.seats') }}" variant="ghost" size="sm" icon="users">
                Manage seats
            </flux:button>
        </div>
    @endif

    {{-- Deemed-effective countdown (GA/MS) --}}
    @if ($tracksDeemedEffective)
        <x-ui.card @class(['border-amber-200 dark:border-amber-800' => $deemedEffectiveSoon->isNotEmpty()])>
            <x-slot:header>
                <div class="flex items-center gap-2">
                    <flux:icon name="exclamation-triangle" class="size-5 text-amber-500" />
                    <span>Deemed Effective Soon</span>
                </div>
            </x-slot:header>

            @if ($deemedEffectiveSoon->isEmpty())
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    None of your signed waivers reach their deemed-effective date in the next 30 days.
                    In Georgia and Mississippi, a signed waiver becomes conclusively effective 90 or 60 days
                    after signing <span class="font-medium">even if you were never paid</span>. Filing an
                    Affidavit of Nonpayment before that date preserves your lien rights. We'll list any
                    waiver approaching its date here.
                </p>
            @else
                <flux:callout color="amber" icon="exclamation-triangle" class="mb-4">
                    These signed waivers become conclusively effective soon. Under the GA 90-day / MS 60-day
                    rule they wipe out lien rights even if payment never arrived. If you haven't been paid,
                    file an Affidavit of Nonpayment before the date shown to preserve your rights.
                </flux:callout>

                <ul class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @foreach ($deemedEffectiveSoon as $waiver)
                        <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0">
                                <a href="{{ route('lien.waivers.show', $waiver) }}" class="text-sm font-medium text-zinc-900 hover:underline dark:text-white" wire:navigate>
                                    {{ $waiver->counterpartyDisplayName() }}, {{ $waiver->project?->name ?? 'Unknown project' }}
                                </a>
                                <p class="text-xs text-zinc-500">
                                    {{ $waiver->kind->shortLabel() }}
                                    @if ($waiver->formattedAmount())
                                        &bull; {{ $waiver->formattedAmount() }}
                                    @endif
                                </p>
                            </div>
                            <flux:badge color="amber" size="sm" class="shrink-0">
                                Effective {{ $waiver->deemed_effective_at->format('M j, Y') }}
                            </flux:badge>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-ui.card>
    @endif

    {{-- Recent Waivers --}}
    <x-ui.card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <span>Recent Waivers</span>
                <flux:button href="{{ route('lien.waivers.list') }}" variant="ghost" size="sm" wire:navigate>
                    View all
                </flux:button>
            </div>
        </x-slot:header>

        @if ($recentWaivers->isEmpty())
            <div class="py-8 text-center">
                <flux:icon name="document-check" class="mx-auto size-10 text-zinc-400" />
                <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">No waivers yet</h3>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                    Generate a state-compliant lien waiver in a couple of minutes. Downloading is free.
                </p>
                <div class="mt-4">
                    <flux:button href="{{ route('lien.waivers.create') }}" variant="primary" icon="plus" wire:navigate>
                        New waiver
                    </flux:button>
                </div>
            </div>
        @else
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Counterparty</flux:table.column>
                    <flux:table.column>Project</flux:table.column>
                    <flux:table.column>Type</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column align="end">Amount</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($recentWaivers as $waiver)
                        <flux:table.row wire:key="recent-waiver-{{ $waiver->id }}">
                            <flux:table.cell class="whitespace-nowrap">
                                <a href="{{ route('lien.waivers.show', $waiver) }}"
                                   class="text-sm font-medium text-zinc-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                   wire:navigate>
                                    {{ $waiver->counterpartyDisplayName() }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $waiver->project?->name ?? '-' }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $waiver->kind->shortLabel() }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">
                                <flux:badge size="sm" :color="$waiver->status->color()">
                                    {{ $waiver->status->label() }}
                                </flux:badge>
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
