@props(['title' => null])

<x-layouts.workspace key="liens" :title="$title">
    {{ $slot }}
</x-layouts.workspace>
