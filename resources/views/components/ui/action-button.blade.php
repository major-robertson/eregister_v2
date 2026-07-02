{{-- Green action button — revenue moments ONLY (start filing, start
     registration, checkout, pay). Every other in-portal button uses Flux
     primary blue. See /styleguide § Buttons for the policy. --}}
<flux:button
    variant="primary"
    {{ $attributes->class('[--color-accent:var(--color-action)] [--color-accent-content:var(--color-action)] [--color-accent-foreground:var(--color-white)]') }}
>
    {{ $slot }}
</flux:button>
