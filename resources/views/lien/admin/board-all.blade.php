<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">All Filings Board</flux:heading>
            <flux:text class="mt-1">Every filing across all statuses.</flux:text>
        </div>
        <flux:button href="{{ route('admin.liens.board') }}" variant="primary" icon="arrow-left" wire:navigate>
            Back to Board
        </flux:button>
    </div>

    <flux:input type="search" placeholder="Search by name, email, address, business..."
        wire:model.live.debounce.300ms="search" icon="magnifying-glass" />

    <!-- Kanban Board — horizontal scroll for 10 columns -->
    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($columns as $column)
            @php
                $columnFilings = $filings->get($column->value) ?? collect();
                $count = $columnFilings->count();
            @endphp

            <div class="flex w-72 shrink-0 flex-col rounded-lg border border-border bg-white">
                <!-- Column Header -->
                <div class="flex items-center justify-between border-b border-border px-4 py-3">
                    <div class="flex items-center gap-2">
                        <flux:badge color="{{ $column->color() }}" size="sm">
                            {{ $count }}
                        </flux:badge>
                        <flux:heading size="sm">{{ $column->label() }}</flux:heading>
                    </div>
                </div>

                <!-- Column Content -->
                <div class="flex-1 space-y-3 overflow-y-auto p-3" style="max-height: 70vh;">
                    @forelse ($columnFilings as $filing)
                        @php
                            $latestComment = $filing->events->first();
                        @endphp
                        <a
                            href="{{ route('admin.liens.show', $filing->public_id) }}"
                            class="block rounded-lg border border-border bg-white p-3 shadow-sm transition hover:border-blue-300 hover:shadow-md"
                            wire:navigate
                        >
                            <div class="flex items-start justify-between gap-2">
                                <flux:text class="font-medium text-gray-900 truncate">
                                    {{ $filing->project?->business?->name ?? 'Unknown Business' }}
                                </flux:text>
                                <div class="flex shrink-0 items-center gap-1">
                                    @if ($filing->project?->jobsite_state)
                                        <flux:badge size="sm" color="zinc">{{ $filing->project->jobsite_state }}</flux:badge>
                                    @endif
                                    @if ($filing->needs_review)
                                        <flux:badge color="amber" size="sm">Review</flux:badge>
                                    @endif
                                </div>
                            </div>

                            @if ($filing->createdBy)
                                <flux:text class="mt-1 text-xs text-gray-500 truncate">
                                    {{ $filing->createdBy->name }} &middot; {{ $filing->createdBy->email }}
                                </flux:text>
                            @endif

                            @if ($filing->project?->jobsite_address1)
                                <flux:text class="mt-1 text-xs text-gray-500 truncate">
                                    {{ $filing->project->jobsiteAddressLine() }}
                                </flux:text>
                            @endif

                            <div class="mt-2 flex flex-wrap items-center gap-1">
                                <flux:badge size="sm" color="zinc">
                                    {{ $filing->documentType?->name ?? 'Unknown' }}
                                </flux:badge>
                                <flux:badge size="sm" color="{{ $filing->service_level === \App\Domains\Lien\Enums\ServiceLevel::FullService ? 'indigo' : 'zinc' }}">
                                    {{ $filing->service_level->label() }}
                                </flux:badge>
                            </div>

                            @if ($latestComment)
                                <div class="mt-2 flex items-start gap-1.5">
                                    <flux:icon name="chat-bubble-left" class="mt-0.5 size-3 shrink-0 text-gray-400" />
                                    <flux:text class="text-xs text-gray-500 line-clamp-2">
                                        {{ Str::limit($latestComment->payload_json['comment'] ?? '', 200) }}
                                    </flux:text>
                                </div>
                            @endif

                            <flux:text class="mt-2 text-xs text-gray-400">
                                {{ $filing->created_at->diffForHumans() }}
                            </flux:text>
                        </a>
                    @empty
                        <div class="flex h-24 items-center justify-center text-center">
                            <flux:text class="text-gray-400">No filings</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
