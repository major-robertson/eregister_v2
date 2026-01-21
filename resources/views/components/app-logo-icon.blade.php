@props(['variant' => 'auto'])

@php
    $baseClasses = $attributes->get('class', '');
    $isDarkVariant = $variant === 'dark' || ($variant === 'auto' && str_contains($baseClasses, 'text-white'));
@endphp

@if($variant === 'auto')
    <img src="/img/logo/e-black.png" alt="eRegister" {{ $attributes->merge(['class' => 'dark:hidden']) }} />
    <img src="/img/logo/e-white.png" alt="eRegister" {{ $attributes->merge(['class' => 'hidden dark:block']) }} />
@elseif($variant === 'light')
    <img src="/img/logo/e-white.png" alt="eRegister" {{ $attributes }} />
@else
    <img src="/img/logo/e-black.png" alt="eRegister" {{ $attributes }} />
@endif
