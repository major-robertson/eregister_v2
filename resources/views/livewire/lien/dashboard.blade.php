<div class="space-y-6">
    <x-ui.page-header title="Lien Dashboard" />

    {{-- Continue Where You Left Off --}}
    @if($continueDraft)
        <x-ui.card class="border-blue-200 bg-blue-50/50 dark:border-blue-800 dark:bg-blue-900/20">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <flux:icon name="pencil-square" class="size-5 text-blue-600 dark:text-blue-400" />
                        <span class="font-medium text-zinc-900 dark:text-white">Continue where you left off</span>
                    </div>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ $continueDraft->documentType?->name ?? 'Filing' }} &mdash; {{ $continueDraft->project?->name ?? 'Unknown Project' }}
                        <span class="text-zinc-500">&bull; Updated {{ $continueDraft->updated_at->diffForHumans() }}</span>
                    </p>
                </div>
                <flux:button href="{{ route('lien.filings.show', $continueDraft) }}" variant="primary">
                    Resume
                </flux:button>
            </div>
        </x-ui.card>
    @endif

    {{-- Quick Actions --}}
    <div class="flex flex-wrap items-center gap-3">
        <flux:button href="{{ route('lien.projects.create') }}" variant="primary" icon="plus">
            New Project
        </flux:button>
        <flux:button href="{{ route('lien.projects.index') }}" variant="filled" icon="document-text">
            File a Document
        </flux:button>
        @if($draftFilingsCount > 0 && !$continueDraft)
            <flux:button href="{{ route('lien.projects.index') }}" variant="ghost" icon="pencil">
                {{ $draftFilingsCount }} Draft{{ $draftFilingsCount > 1 ? 's' : '' }}
            </flux:button>
        @endif
    </div>

    {{-- Attention Cards Grid --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Overdue Deadlines --}}
        <div @class([
            'rounded-xl border p-5',
            'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20' => $overdueCount > 0,
            'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' => $overdueCount === 0,
        ])>
            <div class="flex items-start justify-between">
                <div>
                    <div @class([
                        'text-3xl font-bold',
                        'text-red-600 dark:text-red-400' => $overdueCount > 0,
                        'text-zinc-900 dark:text-white' => $overdueCount === 0,
                    ])>{{ $overdueCount }}</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Overdue Deadlines</div>
                </div>
                <div @class([
                    'flex size-10 items-center justify-center rounded-lg',
                    'bg-red-100 dark:bg-red-800/50' => $overdueCount > 0,
                    'bg-zinc-100 dark:bg-zinc-700' => $overdueCount === 0,
                ])>
                    <flux:icon name="exclamation-triangle" @class([
                        'size-5',
                        'text-red-600 dark:text-red-400' => $overdueCount > 0,
                        'text-zinc-400' => $overdueCount === 0,
                    ]) />
                </div>
            </div>
            @if($overdueDeadlines->isNotEmpty())
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach($overdueDeadlines as $deadline)
                        <li class="truncate text-zinc-700 dark:text-zinc-300">
                            <a href="{{ route('lien.projects.show', $deadline->project) }}" class="hover:underline" wire:navigate>
                                {{ $deadline->documentType->name }} &mdash; {{ $deadline->project->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if($overdueCount > 0)
                <div class="mt-3">
                    <flux:button href="{{ route('lien.deadlines.index') }}" variant="ghost" size="sm">
                        View Deadlines
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- Upcoming Deadlines --}}
        <div @class([
            'rounded-xl border p-5',
            'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20' => $upcomingCount > 0,
            'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' => $upcomingCount === 0,
        ])>
            <div class="flex items-start justify-between">
                <div>
                    <div @class([
                        'text-3xl font-bold',
                        'text-amber-600 dark:text-amber-400' => $upcomingCount > 0,
                        'text-zinc-900 dark:text-white' => $upcomingCount === 0,
                    ])>{{ $upcomingCount }}</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Due in 7 Days</div>
                </div>
                <div @class([
                    'flex size-10 items-center justify-center rounded-lg',
                    'bg-amber-100 dark:bg-amber-800/50' => $upcomingCount > 0,
                    'bg-zinc-100 dark:bg-zinc-700' => $upcomingCount === 0,
                ])>
                    <flux:icon name="clock" @class([
                        'size-5',
                        'text-amber-600 dark:text-amber-400' => $upcomingCount > 0,
                        'text-zinc-400' => $upcomingCount === 0,
                    ]) />
                </div>
            </div>
            @if($upcomingDeadlines->isNotEmpty())
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach($upcomingDeadlines as $deadline)
                        <li class="truncate text-zinc-700 dark:text-zinc-300">
                            <a href="{{ route('lien.projects.show', $deadline->project) }}" class="hover:underline" wire:navigate>
                                {{ $deadline->documentType->name }} &mdash; {{ $deadline->due_date->format('M j') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if($upcomingCount > 0)
                <div class="mt-3">
                    <flux:button href="{{ route('lien.deadlines.index') }}" variant="ghost" size="sm">
                        View Deadlines
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- Pending Payments --}}
        <div @class([
            'rounded-xl border p-5',
            'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20' => $pendingPaymentsCount > 0,
            'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' => $pendingPaymentsCount === 0,
        ])>
            <div class="flex items-start justify-between">
                <div>
                    <div @class([
                        'text-3xl font-bold',
                        'text-blue-600 dark:text-blue-400' => $pendingPaymentsCount > 0,
                        'text-zinc-900 dark:text-white' => $pendingPaymentsCount === 0,
                    ])>{{ $pendingPaymentsCount }}</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Pending Payments</div>
                </div>
                <div @class([
                    'flex size-10 items-center justify-center rounded-lg',
                    'bg-blue-100 dark:bg-blue-800/50' => $pendingPaymentsCount > 0,
                    'bg-zinc-100 dark:bg-zinc-700' => $pendingPaymentsCount === 0,
                ])>
                    <flux:icon name="credit-card" @class([
                        'size-5',
                        'text-blue-600 dark:text-blue-400' => $pendingPaymentsCount > 0,
                        'text-zinc-400' => $pendingPaymentsCount === 0,
                    ]) />
                </div>
            </div>
            @if($pendingPayments->isNotEmpty())
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach($pendingPayments as $filing)
                        <li class="truncate text-zinc-700 dark:text-zinc-300">
                            <a href="{{ route('lien.filings.checkout', $filing) }}" class="hover:underline" wire:navigate>
                                {{ $filing->documentType->name }} &mdash; {{ $filing->project->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if($pendingPaymentsCount > 0)
                <div class="mt-3">
                    <flux:button href="{{ route('lien.payments.index') }}" variant="ghost" size="sm">
                        Complete Payment
                    </flux:button>
                </div>
            @endif
        </div>

        {{-- Missing Information --}}
        <div @class([
            'rounded-xl border p-5',
            'border-zinc-300 bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800/50' => $missingInfoCount > 0,
            'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800' => $missingInfoCount === 0,
        ])>
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $missingInfoCount }}</div>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Missing Info</div>
                </div>
                <div class="flex size-10 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-700">
                    <flux:icon name="information-circle" class="size-5 text-zinc-500" />
                </div>
            </div>
            @if($missingInfoProjects->isNotEmpty())
                <ul class="mt-3 space-y-1 text-sm">
                    @foreach($missingInfoProjects as $item)
                        <li class="truncate text-zinc-700 dark:text-zinc-300">
                            <a href="{{ route('lien.projects.edit', $item['project']) }}" class="hover:underline" wire:navigate>
                                {{ $item['project']->name }}
                            </a>
                            <span class="text-xs text-zinc-500">&mdash; {{ $item['reasons'][0] ?? '' }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if($missingInfoCount > 0)
                <div class="mt-3">
                    <flux:button href="{{ route('lien.projects.index') }}" variant="ghost" size="sm">
                        Complete Info
                    </flux:button>
                </div>
            @endif
        </div>
    </div>

    {{-- Recent Activity Feed --}}
    <x-ui.card>
        <x-slot:header>
            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">Recent Activity</h3>
        </x-slot:header>

        @if($activityFeed->isEmpty())
            <div class="py-8 text-center">
                <flux:icon name="clock" class="mx-auto size-8 text-zinc-400" />
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">No recent activity</p>
            </div>
        @else
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-700">
                @foreach($activityFeed as $item)
                    <li class="flex items-start gap-3 py-3 first:pt-0 last:pb-0">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-700">
                            <flux:icon :name="$item->icon" class="size-4 text-zinc-500" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-zinc-900 dark:text-white">{{ $item->label }}</p>
                            <p class="text-xs text-zinc-500">
                                {{ $item->projectName }}
                                <span>&bull;</span>
                                {{ $item->createdAt->diffForHumans() }}
                            </p>
                        </div>
                        @if($item->filingPublicId)
                            <flux:button href="{{ route('lien.filings.show', ['filing' => $item->filingPublicId]) }}" variant="ghost" size="xs">
                                View
                            </flux:button>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
</div>
