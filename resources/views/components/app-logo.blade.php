@props([
    'sidebar' => false,
    'badge' => null,
])

<a {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" class="h-8 brightness-0 dark:hidden {{ $sidebar ? '-ml-1' : '' }}" />
    <img src="/img/logo/eregister-logo-light-svg.svg" alt="eRegister" class="hidden h-8 brightness-0 invert dark:block {{ $sidebar ? '-ml-1' : '' }}" />
    @if($badge)
        <flux:badge color="amber" size="sm">{{ $badge }}</flux:badge>
    @endif
</a>
