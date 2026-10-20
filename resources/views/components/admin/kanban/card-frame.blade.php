@props([
    'href',
    'trashed' => false,
])

{{--
    Generic kanban card frame: border, padding, hover, link wrapper.
    Card body content goes in the default slot. An optional `actions` slot
    (buttons) renders below the body, outside the link, so clicking a
    button doesn't open the card.
--}}

@php
    $frameClasses = 'rounded-lg border shadow-sm transition hover:shadow-md '
        .($trashed ? 'border-red-200 bg-red-50/50 opacity-75 hover:border-red-300' : 'border-border bg-white hover:border-blue-300');
@endphp

@if (isset($actions) && $actions->hasActualContent())
<div class="{{ $frameClasses }}" {{ $attributes }}>
    <a href="{{ $href }}" class="block p-3" wire:navigate>
        {{ $slot }}
    </a>
    <div class="flex items-center gap-2 px-3 pb-3">
        {{ $actions }}
    </div>
</div>
@else
<a href="{{ $href }}"
    class="block p-3 {{ $frameClasses }}"
    wire:navigate
    {{ $attributes }}>
    {{ $slot }}
</a>
@endif
