@props([
    'sidebar' => false,
    'badge' => null,
])

<a {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <span class="text-lg font-semibold text-zinc-900 dark:text-white" style="font-family: 'Inter', sans-serif;">eRegister</span>
    @if($badge)
        <flux:badge color="amber" size="sm">{{ $badge }}</flux:badge>
    @endif
</a>
