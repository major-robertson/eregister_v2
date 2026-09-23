@props([
    'items',              // [['name' => 'Liens', 'url' => route('liens')], ['name' => 'Texas']]  (last item may omit url)
    'visible' => true,
])
{{-- BreadcrumbList JSON-LD plus (optionally) the visible trail. The trail is
     rendered from the same array so the markup can never disagree with it. --}}
@php
    $crumbs = array_values(array_filter($items));
    $list = [];
    foreach ($crumbs as $i => $crumb) {
        $entry = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $crumb['name']];
        if (! empty($crumb['url'])) {
            $entry['item'] = $crumb['url'];
        }
        $list[] = $entry;
    }
@endphp
@push('schema')
<x-seo.json-ld :data="['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list]" />
@endpush
@if ($visible)
<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'text-sm']) }}>
    <ol class="flex flex-wrap items-center gap-1.5">
        @foreach ($crumbs as $i => $crumb)
            @if ($i > 0)
                <li aria-hidden="true" class="opacity-50">/</li>
            @endif
            <li>
                @if (! empty($crumb['url']) && $i < count($crumbs) - 1)
                    <a href="{{ $crumb['url'] }}" class="hover:underline">{{ $crumb['name'] }}</a>
                @else
                    <span aria-current="page" class="font-medium">{{ $crumb['name'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
@endif
