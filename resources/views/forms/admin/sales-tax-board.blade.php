<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Sales Tax Registrations Board</flux:heading>
            <flux:text class="mt-1">
                @if ($showAll)
                    Showing every status. Use the active board to focus on work that needs your attention.
                @else
                    Manage and process sales tax permit registrations across all businesses.
                @endif
            </flux:text>
        </div>
        @if ($showAll)
            <flux:button :href="route('admin.sales-tax.board')" variant="subtle" icon="arrow-left" wire:navigate>
                Back to Active Board
            </flux:button>
        @else
            <flux:button :href="route('admin.sales-tax.board-all')" variant="subtle" icon="view-columns" wire:navigate>
                View All Statuses
            </flux:button>
        @endif
    </div>

    <flux:input
        type="search"
        placeholder="Search businesses..."
        wire:model.live.debounce.300ms="search"
        icon="magnifying-glass"
    />

    @if ($search)
        <div class="flex items-center gap-3">
            <flux:badge size="sm" color="zinc">
                {{ $resultCount }} {{ Str::plural('result', $resultCount) }}
            </flux:badge>
            <flux:text class="text-sm text-gray-500">for "{{ $search }}"</flux:text>
        </div>
    @endif

    <div wire:loading.delay class="flex items-center gap-2 text-sm text-gray-500">
        <flux:icon name="arrow-path" class="size-4 animate-spin" />
        Loading...
    </div>

    @if ($search !== '')
        {{-- Business search results panel --}}
        <div wire:loading.remove>
            @if ($businessResults->isEmpty())
                <div class="flex h-48 items-center justify-center rounded-lg border border-dashed border-gray-300">
                    <div class="text-center">
                        <flux:icon name="building-office" class="mx-auto size-8 text-gray-400" />
                        <flux:text class="mt-2 text-gray-500">No businesses found for "{{ $search }}"</flux:text>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($businessResults as $business)
                        <a href="{{ route('admin.businesses.show', $business) }}"
                            class="block rounded-lg border border-border bg-white p-4 shadow-sm transition hover:border-blue-300 hover:shadow-md"
                            wire:navigate
                            wire:key="business-{{ $business->id }}">
                            <flux:heading size="sm" class="truncate">{{ $business->name }}</flux:heading>

                            @if ($business->users->isNotEmpty())
                                <div class="mt-3 space-y-1">
                                    @foreach ($business->users->take(3) as $user)
                                        <div class="flex items-center gap-2">
                                            <flux:icon name="user" class="size-3 shrink-0 text-gray-400" />
                                            <flux:text class="text-xs text-gray-600 truncate">
                                                {{ $user->name }} &middot; {{ $user->email }}
                                            </flux:text>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <flux:badge size="sm" color="zinc">
                                    {{ $business->form_applications_count }}
                                    {{ Str::plural('application', $business->form_applications_count) }}
                                </flux:badge>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-4">{{ $businessResults->links() }}</div>
            @endif
        </div>
    @else
        {{-- Default kanban view --}}
        <x-admin.kanban.grid :compact="true">
            @foreach ($columns as $column)
                @php $columnCards = $cards->get($column->value) ?? collect(); @endphp
                <x-admin.kanban.column
                    :column="$column"
                    :count="$columnCards->count()"
                    empty-text="No registrations"
                >
                    @foreach ($columnCards as $card)
                        @php
                            $app = $card->application;
                            $siblings = $app?->states ?? collect();
                            $totalSiblings = $siblings->count();
                            $thisIndex = $siblings->search(fn ($s) => $s->id === $card->id);
                            $position = $thisIndex !== false ? $thisIndex + 1 : null;
                            $breakdown = $siblings->groupBy(fn ($s) => $s->current_admin_status->value)->map->count();
                            $latestTransition = $card->transitions->first();
                            $formTypeLabel = $app
                                ? (\App\Domains\Forms\FormTypeConfig::exists($app->form_type)
                                    ? \App\Domains\Forms\FormTypeConfig::get($app->form_type)['name']
                                    : \Illuminate\Support\Str::headline((string) $app->form_type))
                                : null;
                        @endphp
                        <x-admin.kanban.card-frame
                            :href="route('admin.sales-tax.states.show', $card)"
                            wire:key="state-{{ $card->id }}"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <flux:text class="font-medium text-gray-900 truncate">
                                    {{ $app?->business?->name ?? 'Unknown Business' }}
                                </flux:text>
                                <flux:badge color="emerald" size="sm">{{ $card->state_code }}</flux:badge>
                            </div>

                            @if ($app?->createdBy)
                                <flux:text class="mt-1 text-xs text-gray-500 truncate">
                                    {{ $app->createdBy->name }} &middot; {{ $app->createdBy->email }}
                                </flux:text>
                            @endif

                            @if ($formTypeLabel)
                                <div class="mt-2">
                                    <flux:badge size="sm" color="zinc">{{ $formTypeLabel }}</flux:badge>
                                </div>
                            @endif

                            {{-- Sibling progress strip (hidden for single-state apps) --}}
                            @if ($totalSiblings > 1 && $position !== null)
                                <div class="mt-2 rounded-md bg-zinc-50 px-2 py-1.5">
                                    <flux:text class="text-xs font-medium text-gray-600">
                                        State {{ $position }} of {{ $totalSiblings }}
                                    </flux:text>
                                    <div class="mt-1 flex flex-wrap items-center gap-1">
                                        @foreach ($breakdown as $statusValue => $count)
                                            @php
                                                $status = \App\Domains\Forms\Enums\FormApplicationStateAdminStatus::tryFrom($statusValue);
                                            @endphp
                                            @if ($status && $count > 0)
                                                <flux:badge color="{{ $status->color() }}" size="sm">
                                                    {{ $count }} {{ $status->label() }}
                                                </flux:badge>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($latestTransition?->comment)
                                <div class="mt-2 flex items-start gap-1.5">
                                    <flux:icon name="chat-bubble-left" class="mt-0.5 size-3 shrink-0 text-gray-400" />
                                    <flux:text class="text-xs text-gray-500 line-clamp-2">
                                        {{ Str::limit($latestTransition->comment, 200) }}
                                    </flux:text>
                                </div>
                            @endif

                            <flux:text class="mt-2 text-xs text-gray-400">
                                @if ($app?->paid_at)
                                    Paid {{ $app->paid_at->diffForHumans() }}
                                @else
                                    {{ $card->created_at->diffForHumans() }}
                                @endif
                            </flux:text>
                        </x-admin.kanban.card-frame>
                    @endforeach
                </x-admin.kanban.column>
            @endforeach
        </x-admin.kanban.grid>
    @endif
</div>
