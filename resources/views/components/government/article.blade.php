@props([
    'headline',            // the page's H1, repeated as the Article headline
    'description',         // the meta description, repeated in the Article markup
    'path',                // the page path, e.g. '/government/florida'
    'updated',             // ISO date (Y-m-d) the content was last checked
    'sources' => [],       // [['name' => 'Fla. Stat. 282.603', 'url' => 'https://…'], …]: rendered as an ordered list
    'faq' => [],           // [['q' => 'Question?', 'a' => 'Plain-text answer.'], …]: rendered with x-seo.faq (FAQPage JSON-LD)
    'faqHeading' => 'Frequently asked questions',
])
{{--
    Long-form body for the government guide pages (the ADA Title II guide and
    the state pages). The page keeps its own hero (breadcrumbs and H1); this
    renders the dateline, the body (plain h2, p, ul, ol, a and tables are
    styled here; never add an h1), the Sources list, the FAQ and the Article
    JSON-LD. Author and publisher are the Organization node the head emits.
    In tables, use <th scope="col"> in the thead and <th scope="row"> for the
    first cell of each body row, inside an overflow-x-auto wrapper.
--}}
@php
    $organization = ['@id' => \App\Support\Seo\Urls::absolute('/').'#organization'];
    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $headline,
        'description' => $description,
        'datePublished' => $updated,
        'dateModified' => $updated,
        'mainEntityOfPage' => \App\Support\Seo\Urls::absolute($path),
        'author' => $organization,
        'publisher' => $organization,
        'image' => asset('img/og/default.png'),
    ];

    // Prose styles for the body, without the typography plugin (the guides set,
    // with row headers allowed to wrap because these tables label rows in full).
    $prose = implode(' ', [
        'text-base leading-relaxed text-zinc-700',
        '[&_h2]:mt-14 [&_h2]:max-w-3xl [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:tracking-tight [&_h2]:text-zinc-900',
        '[&_h3]:mt-8 [&_h3]:max-w-3xl [&_h3]:text-lg [&_h3]:font-semibold [&_h3]:text-zinc-900',
        '[&_p]:mt-4 [&_p]:max-w-3xl',
        '[&_ul]:mt-4 [&_ul]:max-w-3xl [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6',
        '[&_ol]:mt-4 [&_ol]:max-w-3xl [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6',
        '[&_a]:font-medium [&_a]:text-blue-700 [&_a]:underline [&_a:hover]:text-blue-900',
        '[&_thead_th]:whitespace-nowrap [&_thead_th]:bg-zinc-50 [&_thead_th]:px-4 [&_thead_th]:py-3 [&_thead_th]:text-xs [&_thead_th]:font-semibold [&_thead_th]:uppercase [&_thead_th]:tracking-wider [&_thead_th]:text-zinc-500',
        '[&_tbody_th]:px-4 [&_tbody_th]:py-3 [&_tbody_th]:align-top [&_tbody_th]:font-semibold [&_tbody_th]:text-zinc-900',
        '[&_td]:px-4 [&_td]:py-3 [&_td]:align-top',
    ]);
@endphp
@push('schema')
<x-seo.json-ld :data="$articleSchema" />
@endpush

<article class="bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm text-zinc-500">
            Updated <time datetime="{{ $updated }}">{{ \Illuminate\Support\Carbon::parse($updated)->format('F j, Y') }}</time>
        </p>

        <div class="mt-2 {{ $prose }}">
            {{ $slot }}
        </div>

        @if ($sources)
        <section class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Sources</h2>
            <ol class="mt-4 list-decimal space-y-2 pl-6 text-sm text-zinc-600">
                @foreach ($sources as $source)
                <li><a href="{{ $source['url'] }}" rel="noopener" target="_blank" class="font-medium text-zinc-700 underline hover:text-zinc-900">{{ $source['name'] }}</a></li>
                @endforeach
            </ol>
        </section>
        @endif

        @if ($faq)
        <section class="mt-16">
            <h2 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $faqHeading }}</h2>
            <x-seo.faq class="mt-6" card="zinc" :items="$faq" />
        </section>
        @endif
    </div>
</article>
