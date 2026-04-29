@props([
    'href',
    'trashed' => false,
])

{{--
    Generic kanban card frame: border, padding, hover, link wrapper.
    Card body content goes in the default slot.
--}}

<a href="{{ $href }}"
    class="block rounded-lg border p-3 shadow-sm transition hover:shadow-md {{ $trashed ? 'border-red-200 bg-red-50/50 opacity-75 hover:border-red-300' : 'border-border bg-white hover:border-blue-300' }}"
    wire:navigate
    {{ $attributes }}>
    {{ $slot }}
</a>
