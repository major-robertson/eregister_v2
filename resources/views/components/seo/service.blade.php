@props([
    'name',
    'description',
    'url' => null,
    'price' => null,          // dollars, e.g. 99 or 29.00
    'priceUnit' => null,      // "MON" / "YEAR" for subscriptions, null for one-time
    'category' => null,
])
{{-- Service (+ Offer when a price is known) JSON-LD for a product page.
     Pushed to the schema stack so it lands in <head> next to the other tags. --}}
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => $name,
        'description' => $description,
        'url' => $url ?? url()->current(),
        'provider' => ['@id' => url('/').'#organization'],
        'areaServed' => ['@type' => 'Country', 'name' => 'United States'],
        'serviceType' => $category ?? $name,
    ];

    if ($price !== null) {
        $offer = [
            '@type' => 'Offer',
            'price' => number_format((float) $price, 2, '.', ''),
            'priceCurrency' => 'USD',
            'availability' => 'https://schema.org/InStock',
            'url' => $url ?? url()->current(),
        ];
        if ($priceUnit) {
            $offer['priceSpecification'] = [
                '@type' => 'UnitPriceSpecification',
                'price' => number_format((float) $price, 2, '.', ''),
                'priceCurrency' => 'USD',
                'unitCode' => $priceUnit,
            ];
        }
        $schema['offers'] = $offer;
    }
@endphp
@push('schema')
<x-seo.json-ld :data="$schema" />
@endpush
