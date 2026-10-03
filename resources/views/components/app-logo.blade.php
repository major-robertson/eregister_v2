@props([
    'sidebar' => false,
    'badge' => null,
    'badgeColor' => 'amber',
])

<a {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" width="1538" height="520" class="h-8 w-auto dark:hidden {{ $sidebar ? '-ml-1' : '' }}" />
    <img src="/img/logo/eregister-logo-light-svg.svg" alt="eRegister" width="1538" height="520" class="hidden h-8 w-auto dark:block {{ $sidebar ? '-ml-1' : '' }}" />
    @if($badge)
        <flux:badge :color="$badgeColor" size="sm">{{ $badge }}</flux:badge>
    @endif
</a>
