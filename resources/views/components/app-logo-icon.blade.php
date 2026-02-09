@props(['variant' => 'auto'])

@if($variant === 'auto')
    <img src="/img/logo/eregister-icon-dark-svg.svg" alt="eRegister" {{ $attributes->merge(['class' => 'brightness-0 dark:hidden']) }} />
    <img src="/img/logo/eregister-icon-light-svg.svg" alt="eRegister" {{ $attributes->merge(['class' => 'hidden brightness-0 invert dark:block']) }} />
@elseif($variant === 'light')
    <img src="/img/logo/eregister-icon-light-svg.svg" alt="eRegister" {{ $attributes->merge(['class' => 'brightness-0 invert']) }} />
@else
    <img src="/img/logo/eregister-icon-dark-svg.svg" alt="eRegister" {{ $attributes->merge(['class' => 'brightness-0']) }} />
@endif
