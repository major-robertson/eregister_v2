@props([
    'column',  // enum case implementing label() and color()
    'count',
    'compact' => true,
    'emptyText' => 'No items',
])

{{--
    Single kanban column with header (label + count badge) and a
    scrollable content area for cards. Card markup goes in the slot;
    when the slot is empty (no items), an empty-state placeholder
    renders. The consumer is responsible for the empty check.

    For consistency, the consumer typically wraps with @forelse and
    nests <x-admin.kanban.card-frame> inside this component's slot.
--}}

<div class="{{ $compact ? 'flex flex-col' : 'flex w-72 shrink-0 flex-col' }} rounded-lg border border-border bg-white">
    {{-- Column header --}}
    <div class="flex items-center justify-between border-b border-border px-4 py-3">
        <div class="flex items-center gap-2">
            <flux:badge color="{{ $column->color() }}" size="sm">
                {{ $count }}
            </flux:badge>
            <flux:heading size="sm">{{ $column->label() }}</flux:heading>
        </div>
    </div>

    {{-- Column content --}}
    <div class="flex-1 space-y-3 overflow-y-auto p-3" style="max-height: 70vh;">
        @if ($count === 0)
            <div class="flex h-24 items-center justify-center text-center">
                <flux:text class="text-gray-400">{{ $emptyText }}</flux:text>
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</div>
