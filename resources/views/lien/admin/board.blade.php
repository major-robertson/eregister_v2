<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Lien Filings Board</flux:heading>
            <flux:text class="mt-1">Manage and process lien filings across all businesses.</flux:text>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        @foreach ($columns as $column)
            @php
                $columnFilings = $filings->get($column->value) ?? collect();
                $count = $columnFilings->count();
            @endphp

            <div class="flex flex-col rounded-lg border border-border bg-white">
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
                        <a
                            href="{{ route('admin.liens.show', $filing->public_id) }}"
                            class="block rounded-lg border border-border bg-white p-3 shadow-sm transition hover:border-blue-300 hover:shadow-md"
                            wire:navigate
                        >
                            <!-- Business Name -->
                            <div class="flex items-start justify-between gap-2">
                                <flux:text class="font-medium text-gray-900 truncate">
                                    {{ $filing->project?->business?->name ?? 'Unknown Business' }}
                                </flux:text>
                                @if ($filing->needs_review)
                                    <flux:badge color="amber" size="sm">Review</flux:badge>
                                @endif
                            </div>

                            <!-- Project Name -->
                            <flux:text class="mt-1 text-sm text-gray-600 truncate">
                                {{ $filing->project?->name ?? 'Unknown Project' }}
                            </flux:text>

                            <!-- Document Type -->
                            <div class="mt-2 flex items-center gap-2">
                                <flux:badge size="sm" color="zinc">
                                    {{ $filing->documentType?->name ?? 'Unknown' }}
                                </flux:badge>
                                <flux:badge size="sm" color="{{ $filing->status->color() }}">
                                    {{ $filing->status->label() }}
                                </flux:badge>
                            </div>

                            <!-- Date -->
                            <flux:text class="mt-2 text-xs text-gray-500">
                                Created {{ $filing->created_at->diffForHumans() }}
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
