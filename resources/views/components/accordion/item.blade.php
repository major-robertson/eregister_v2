@props([
    'expanded' => false,
])

<div {{ $attributes->class('block border-b border-zinc-800/10 pt-4 pb-4 first:pt-0 last:border-b-0 last:pb-0') }}
    x-data="{ open: {{ $expanded ? 'true' : 'false' }} }">
    {{ $slot }}
</div>
