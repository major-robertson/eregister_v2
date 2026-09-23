{{--
    Shared SEO head tags for the public marketing layouts (landing, government).

    Pages opt in through named sections rather than hand-writing tags:

        @section('title', '...')          page title (already used everywhere)
        @section('description', '...')    meta + og:description
        @section('canonical', url)        override the default canonical (current URL, query string dropped)
        @section('og_image', url)         override the default share image
        @section('og_type', 'article')    defaults to "website"
        @section('noindex', 'true')       emit robots noindex for demos / private landings
        @push('schema') ... @endpush      JSON-LD blocks (see x-seo.* components)

    Anything not covered here still goes in @section('meta').
--}}
@php
    // Inline @section('x', 'value') content is escaped by Blade already, so
    // decode it here and let the {{ }} below escape exactly once.
    $seoSection = fn (string $name, string $default = '') => trim(html_entity_decode($__env->yieldContent($name, $default), ENT_QUOTES));
    $seoTitle = $seoSection('title', config('app.name', 'eRegister'));
    $seoDescription = $seoSection('description');
    $seoCanonical = $seoSection('canonical') ?: url()->current();
    $seoImage = $seoSection('og_image') ?: asset('img/og/default.png');
    $seoType = $seoSection('og_type') ?: 'website';
    $seoNoindex = $__env->hasSection('noindex');
@endphp
@if ($seoNoindex)
<meta name="robots" content="noindex, nofollow" />
@else
<meta name="robots" content="index, follow, max-image-preview:large" />
<link rel="canonical" href="{{ $seoCanonical }}" />
@endif
@if ($seoDescription !== '')
<meta name="description" content="{{ $seoDescription }}">
@endif

<meta property="og:type" content="{{ $seoType }}" />
<meta property="og:site_name" content="eRegister" />
<meta property="og:locale" content="en_US" />
<meta property="og:title" content="{{ $seoTitle }}" />
@if ($seoDescription !== '')
<meta property="og:description" content="{{ $seoDescription }}" />
@endif
<meta property="og:url" content="{{ $seoCanonical }}" />
<meta property="og:image" content="{{ $seoImage }}" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $seoTitle }}" />
@if ($seoDescription !== '')
<meta name="twitter:description" content="{{ $seoDescription }}" />
@endif
<meta name="twitter:image" content="{{ $seoImage }}" />

@unless ($seoNoindex)
<x-seo.json-ld :data="[
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    '@id' => url('/').'#organization',
    'name' => 'eRegister',
    'url' => url('/'),
    'logo' => asset('img/logo/eregister-logo-dark.png'),
    'contactPoint' => [
        '@type' => 'ContactPoint',
        'contactType' => 'customer support',
        'url' => route('contact'),
        'areaServed' => 'US',
        'availableLanguage' => 'en',
    ],
]" />
@endunless
@stack('schema')
