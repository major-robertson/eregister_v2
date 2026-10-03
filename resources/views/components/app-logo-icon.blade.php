@props(['variant' => 'auto'])

@if($variant === 'auto')
    <img src="/img/logo/eregister-icon-dark-svg.svg" alt="eRegister" width="512" height="512" {{ $attributes->merge(['class' => 'dark:hidden']) }} />
    <img src="/img/logo/eregister-icon-light-svg.svg" alt="eRegister" width="512" height="512" {{ $attributes->merge(['class' => 'hidden dark:block']) }} />
@elseif($variant === 'light')
    <img src="/img/logo/eregister-icon-light-svg.svg" alt="eRegister" width="512" height="512" {{ $attributes->merge(['class' => '']) }} />
@else
    <img src="/img/logo/eregister-icon-dark-svg.svg" alt="eRegister" width="512" height="512" {{ $attributes->merge(['class' => '']) }} />
@endif
