@props([
    'guide',               // the registry entry from App\Support\Seo\Guides (the controller passes it as $guide)
    'sources' => [],       // [['name' => 'Tex. Prop. Code ch. 53', 'url' => 'https://…'], …]: primary sources, rendered as an ordered list
    'sourcesNote' => null, // optional sentence printed above the source list
    'faq' => [],           // [['q' => 'Question?', 'a' => 'Plain-text answer.'], …]: rendered with x-seo.faq, which emits FAQPage JSON-LD
])
{{--
    The shell every guide uses. A guide view looks like this:

        @extends('layouts.landing')
        @section('title', $guide['page_title'])
        @section('description', $guide['description'])
        @section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
        @section('og_type', 'article')

        @section('content')
        <x-guides.article :guide="$guide" :sources="$sources" :faq="$faq">
            <p>Intro…</p>
            <h2>A section</h2>
            <p>…</p>
            <x-guides.table id="some-table"> <thead>…</thead><tbody>…</tbody> </x-guides.table>
        </x-guides.article>
        @endsection

    The component renders the breadcrumbs (Home / Guides / title), the H1 and
    dateline from the registry, the body (plain h2, h3, p, ul, ol, a and
    tables are styled here; never add another h1), then the FAQ, the
    sources, the "confirm with counsel" line for lien and waiver guides, the
    related links from the registry, and the Article and BreadcrumbList
    JSON-LD. Author and publisher are the Organization node the head emits.

    Build $faq and $sources in a PHP block at the top of the view, or
    leave them off: no FAQ means no FAQPage markup, no sources means no
    Sources section. sources-note="…" adds a sentence above the list. In
    tables, use <th scope="col"> in the thead and <th scope="row"> for the
    first cell of each body row.
--}}
@php
    $organization = ['@id' => \App\Support\Seo\Urls::absolute('/').'#organization'];
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $guide['title'],
        'description' => $guide['description'],
        'datePublished' => $guide['published'],
        'dateModified' => $guide['updated'],
        'mainEntityOfPage' => \App\Support\Seo\Guides::url($guide['slug']),
        'author' => $organization,
        'publisher' => $organization,
        'image' => asset('img/og/default.png'),
    ];
    $published = \App\Support\Seo\Guides::displayDate($guide['published']);
    $updated = \App\Support\Seo\Guides::displayDate($guide['updated']);
    $counsel = in_array($guide['cluster'], ['liens', 'waivers'], true);

    // Prose styles for the body, without the typography plugin.
    $prose = implode(' ', [
        'text-base leading-relaxed text-zinc-700',
        '[&_h2]:mt-14 [&_h2]:max-w-3xl [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-zinc-900',
        '[&_h3]:mt-8 [&_h3]:max-w-3xl [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-zinc-900',
        '[&_p]:mt-4 [&_p]:max-w-3xl',
        '[&_ul]:mt-4 [&_ul]:max-w-3xl [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6',
        '[&_ol]:mt-4 [&_ol]:max-w-3xl [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6',
        '[&_a]:font-medium [&_a]:text-blue-700 [&_a]:underline [&_a:hover]:text-blue-900',
        '[&_thead_th]:whitespace-nowrap [&_thead_th]:bg-zinc-50 [&_thead_th]:px-4 [&_thead_th]:py-3 [&_thead_th]:text-xs [&_thead_th]:font-semibold [&_thead_th]:uppercase [&_thead_th]:tracking-wider [&_thead_th]:text-zinc-500',
        '[&_tbody_th]:whitespace-nowrap [&_tbody_th]:px-4 [&_tbody_th]:py-3 [&_tbody_th]:align-top [&_tbody_th]:font-semibold [&_tbody_th]:text-zinc-900',
        '[&_td]:px-4 [&_td]:py-3 [&_td]:align-top',
    ]);
@endphp
@push('schema')
<x-seo.json-ld :data="$articleSchema" />
@endpush

<article>
    <header class="bg-white pb-4 pt-16 lg:pt-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <x-seo.breadcrumbs class="mb-6 text-zinc-500" :items="[
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Guides', 'url' => route('guides.index')],
                ['name' => $guide['title']],
            ]" />
            <h1 class="max-w-3xl text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl">{{ $guide['title'] }}</h1>
            <p class="mt-4 text-sm text-zinc-500">
                Published <time datetime="{{ $guide['published'] }}">{{ $published }}</time>.
                @if ($guide['updated'] !== $guide['published'])
                Updated <time datetime="{{ $guide['updated'] }}">{{ $updated }}</time>.
                @endif
            </p>
        </div>
    </header>

    <div class="bg-white pb-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="{{ $prose }}">
                {{ $slot }}
            </div>

            @if ($faq)
            <section class="mt-16 max-w-3xl">
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Frequently asked questions</h2>
                <x-seo.faq class="mt-6" card="zinc" :items="$faq" />
            </section>
            @endif

            @if ($sources)
            <section class="mt-16 max-w-3xl">
                <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Sources</h2>
                @if ($sourcesNote)
                <p class="mt-4 text-zinc-600">{{ $sourcesNote }}</p>
                @endif
                <ol class="mt-4 list-decimal space-y-2 pl-6 text-sm text-zinc-600">
                    @foreach ($sources as $source)
                    <li><a href="{{ $source['url'] }}" rel="noopener" target="_blank" class="font-medium text-zinc-700 underline hover:text-zinc-900">{{ $source['name'] }}</a></li>
                    @endforeach
                </ol>
            </section>
            @endif

            @if ($counsel)
            <p class="mt-12 max-w-3xl text-sm text-zinc-500">
                This guide is general information, not legal advice. Lien rules turn on facts specific to your project. Confirm with counsel before relying on them.
            </p>
            @endif
        </div>
    </div>
</article>

@if (! empty($guide['related']))
<x-seo.related-links heading="Related pages" :links="$guide['related']" />
@endif
