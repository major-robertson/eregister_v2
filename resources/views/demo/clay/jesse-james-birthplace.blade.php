@extends('demo.clay.layout', [
    'active' => 'historic',
    'title' => 'Jesse James Birthplace — Museum & Historic Farm, Kearney | Clay County Parks (Concept Demo)',
    'metaDescription' => 'Visit the farm where Jesse James was born in 1847: museum, 20-minute film, paved creekside trail to the farmhouse, and the world\'s largest James family collection.',
    'ogImage' => 'james-farm-house.webp',
])

@php
    $siteEvents = collect($events)->where('destinationSlug', 'jesse-james-birthplace')->sortBy('date')->take(2);
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Museum',
        'name' => 'Jesse James Birthplace',
        'url' => route('clay-demo.jesse-james-birthplace'),
        'telephone' => '816-736-8500',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '21216 James Farm Rd',
            'addressLocality' => 'Kearney',
            'addressRegion' => 'MO',
            'postalCode' => '64060',
        ],
    ], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')

    {{-- Hero --}}
    <div class="relative h-[220px] bg-[#2B1B14] md:h-[320px] xl:h-[440px]">
        <img src="{{ asset('img/demos/clay-county/james-farm-house.webp') }}"
            alt="The farmhouse and grounds at the Jesse James Birthplace"
            class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
    </div>

    <div class="mx-auto flex max-w-[1440px] flex-col items-start gap-11 px-4 md:px-8 xl:flex-row xl:px-12">
        <div class="w-full min-w-0 flex-1">

            {{-- Overlapping title card --}}
            <div class="relative z-[2] -mt-10 rounded-2xl border border-[#E4D5C2] bg-white p-6 shadow-[0_10px_30px_rgba(60,35,20,.14)] md:p-8 xl:-mt-[76px]">
                <nav aria-label="Breadcrumb" class="mb-2.5 text-[13px] text-[#5A646C]">
                    <a href="{{ route('clay-demo.home') }}">Home</a>
                    <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
                    <a href="{{ route('clay-demo.historic-sites') }}">Historic Sites</a>
                    <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
                    <span class="font-semibold text-[#232A2E]" aria-current="page">Jesse James Birthplace</span>
                </nav>
                <p class="m-0 mb-2.5 flex flex-wrap gap-2">
                    <span class="rounded bg-[#F5E6DF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#93402A] uppercase">Museum &amp; historic farm</span>
                    <span class="rounded bg-[#F2ECDF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#3C454C] uppercase">Kearney, Missouri</span>
                </p>
                <h1 class="cc-serif m-0 mb-2.5 text-[28px] font-semibold tracking-[-.01em] text-[#3C2317] xl:text-[44px]">Jesse James Birthplace</h1>
                <p class="m-0 max-w-[68ch] text-[15px] leading-relaxed text-[#5A646C] xl:text-[17px]">The farm where Jesse James was born in 1847 and where the James story keeps its roots. Walk the paved trail along the creek to the farmhouse, see the museum's collection, and stand at the site of Jesse's original grave in the yard his mother tended.</p>
            </div>

            {{-- Mobile-only actions + visitor info --}}
            <div class="mt-4 flex gap-2.5 xl:hidden">
                <a href="https://www.google.com/maps/search/?api=1&query=21216+James+Farm+Rd+Kearney+MO+64060" target="_blank" rel="noopener"
                    class="flex-1 rounded-lg bg-[#93402A] px-4 py-3.5 text-center text-[14.5px] font-extrabold text-white no-underline">Directions<span class="sr-only"> (opens external map)</span></a>
                <a href="tel:816-736-8500" class="flex-1 rounded-lg border-2 border-[#93402A] px-4 py-3 text-center text-[14.5px] font-bold text-[#93402A] no-underline">Call 816-736-8500</a>
            </div>
            <div class="mt-3 flex flex-col gap-2.5 rounded-xl border border-[#E4D5C2] bg-white p-4.5 xl:hidden">
                <h2 class="m-0 mt-2 px-1 text-[11px] font-extrabold tracking-[.08em] text-[#93402A] uppercase">Visitor information</h2>
                <p class="m-0 px-1 text-[13.5px] leading-relaxed text-[#3C3129]">21216 James Farm Road, Kearney, MO 64060<br>Hours &amp; admission: see the <a href="https://www.jessejamesmuseum.org/" target="_blank" rel="noopener" class="text-[#93402A]">official hours page<span class="sr-only"> (external site)</span></a>.</p>
                <p class="m-0 border-t border-[#F0E4D6] px-1 pb-2 pt-2.5 text-xs leading-normal text-[#5A646C]">Paved trail to the farmhouse · accessible parking &amp; restrooms · call ahead for mobility assistance.</p>
            </div>

            {{-- Story --}}
            <section aria-label="The museum and the farmhouse" class="mt-10">
                <h2 class="cc-serif m-0 mb-4 text-[22px] font-semibold text-[#3C2317] xl:text-[26px]">The museum and the farmhouse</h2>
                <div class="flex flex-col gap-6 md:flex-row md:items-start">
                    <div class="flex max-w-[75ch] flex-1 flex-col gap-4 text-[15px] leading-[1.75] text-[#3C3129] xl:text-[15.5px]">
                        <p class="m-0">A visit begins in the museum with a 20-minute film that sets the James family in the Missouri of the 1860s — border war, occupation, and the raid on the farm itself. From there, a winding paved trail follows the creek where Frank and Jesse played as boys, ending at the farmhouse kept much as Zerelda James Samuel kept it.</p>
                        <p class="m-0">The collection is the largest assembly of James family artifacts anywhere. It's a museum, not a show: the story is told straight, with the family's own things and the house they lived in. In the yard is Jesse's original burial site — where his mother and brother once sold souvenir pebbles from the grave for a quarter.</p>
                    </div>
                    <figure class="m-0 w-full flex-none md:w-[220px]">
                        <img src="{{ asset('img/demos/clay-county/jesse-james-portrait.webp') }}"
                            alt="Colorized portrait of a young Jesse James in a red shirt and hat"
                            class="w-full rounded-xl border border-[#E4D5C2]" loading="lazy">
                        <figcaption class="mt-2 text-xs leading-normal text-[#8A9199]">Jesse James, colorized portrait — image from the county's Historic Sites collection.</figcaption>
                    </figure>
                </div>
            </section>

            {{-- Artifacts --}}
            <section aria-label="Artifact highlights" class="mt-9">
                <h2 class="cc-serif m-0 mb-4 text-[20px] font-semibold text-[#3C2317] xl:text-[22px]">From the collection</h2>
                <div class="cc-scrollbar-none -mx-4 flex gap-4 overflow-x-auto px-4 md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0">
                    @foreach ([
                        ['title' => "Jesse's boots", 'note' => 'Worn at the time of his death in 1882.'],
                        ['title' => "Frank's surrender letter", 'note' => 'Written to the governor of Missouri, 1882.'],
                        ['title' => 'The original gravesite', 'note' => 'Jesse was first buried in the farmyard — the family sold pebbles from the grave for a quarter.'],
                    ] as $artifact)
                        <div class="w-56 flex-none overflow-hidden rounded-xl border border-[#E4D5C2] bg-white md:w-auto">
                            <div class="relative flex h-[140px] flex-col items-center justify-center gap-1.5 bg-[#F5EDE2] text-[#A08B77] md:h-[170px]">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="m3 16 5-5 4 4 3-3 6 6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="9" cy="9.5" r="1.4" stroke="currentColor" stroke-width="1.4"/></svg>
                                <span class="px-4 text-center text-[11px] font-bold tracking-[.05em] uppercase">Photo pending from curator</span>
                            </div>
                            <div class="px-4 py-3.5">
                                <p class="m-0 text-[14.5px] font-bold text-[#3C2317]">{{ $artifact['title'] }}</p>
                                <p class="m-0 mt-1 text-[13px] leading-normal text-[#5A646C]">{{ $artifact['note'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Timeline --}}
            <section aria-label="Timeline" class="mt-10">
                <h2 class="cc-serif m-0 mb-5 text-[20px] font-semibold text-[#3C2317] xl:text-[22px]">The farm through time</h2>
                <div class="flex flex-col">
                    @foreach ([
                        ['year' => '1822', 'text' => 'The farmhouse is built — among the oldest standing structures in Clay County.'],
                        ['year' => '1847', 'text' => 'Jesse Woodson James is born on the farm, the son of a Baptist minister and the formidable Zerelda.'],
                        ['year' => '1863', 'text' => 'Union militia raid the farm during the border war — an event that pushes the brothers toward the guerrilla bands.'],
                        ['year' => '1882', 'text' => 'Jesse is killed in St. Joseph and buried in the farmyard, where his mother could watch over the grave.'],
                        ['year' => 'Today', 'text' => 'Clay County preserves the farm, museum, and collection — open to visitors. Check the official site for seasonal hours.'],
                    ] as $i => $item)
                        <div class="flex gap-5">
                            <div class="flex w-16 flex-none flex-col items-center">
                                <span class="text-[15px] font-extrabold text-[#93402A]">{{ $item['year'] }}</span>
                                @if ($i < 4)
                                    <span class="mt-1.5 w-0.5 flex-1 bg-[#E4D5C2]" aria-hidden="true"></span>
                                @endif
                            </div>
                            <p class="m-0 {{ $i < 4 ? 'pb-5' : '' }} max-w-[64ch] text-[14.5px] leading-relaxed text-[#3C3129]">{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Gallery --}}
            <section aria-label="Gallery" class="mb-12 mt-10">
                <h2 class="cc-serif m-0 mb-4 text-[20px] font-semibold text-[#3C2317] xl:text-[22px]">On the grounds</h2>
                <div class="grid h-auto grid-cols-1 gap-3 md:h-[260px] md:grid-cols-[2fr_1fr_1fr]">
                    <div class="relative h-48 overflow-hidden rounded-[10px] md:h-auto">
                        <img src="{{ asset('img/demos/clay-county/james-farm-house.webp') }}"
                            alt="The farmhouse porch behind the split-rail fence at the James farm"
                            class="absolute inset-0 h-full w-full object-cover object-[center_65%]" loading="lazy">
                    </div>
                    <div class="flex h-32 flex-col items-center justify-center gap-1.5 rounded-[10px] bg-[#F5EDE2] text-[#A08B77] md:h-auto">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20c3-7 6-9 8-9s3 3 5 3 3-2 3-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <span class="px-3 text-center text-[11px] font-bold tracking-[.05em] uppercase">Creek trail — photo pending</span>
                    </div>
                    <div class="flex h-32 flex-col items-center justify-center gap-1.5 rounded-[10px] bg-[#F5EDE2] text-[#A08B77] md:h-auto">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20V9l8-5 8 5v11" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                        <span class="px-3 text-center text-[11px] font-bold tracking-[.05em] uppercase">Museum exhibits — photo pending</span>
                    </div>
                </div>
            </section>
        </div>

        {{-- Sidebar (desktop) --}}
        <aside class="relative z-[2] mb-12 hidden w-[330px] flex-none flex-col gap-4 xl:-mt-10 xl:flex" aria-label="Visitor information">
            <div class="flex flex-col gap-3.5 rounded-xl border border-[#E4D5C2] bg-white p-6 shadow-[0_10px_30px_rgba(60,35,20,.12)]">
                <h2 class="m-0 text-xs font-extrabold tracking-[.08em] text-[#93402A] uppercase">Visitor information</h2>
                <p class="m-0 text-sm leading-relaxed text-[#3C3129]"><strong class="text-[#3C2317]">Address</strong><br>21216 James Farm Road<br>Kearney, MO 64060</p>
                <p class="m-0 text-sm leading-relaxed text-[#3C3129]"><strong class="text-[#3C2317]">Phone</strong><br><a href="tel:816-736-8500" class="text-[#93402A]">816-736-8500</a></p>
                <p class="m-0 text-sm leading-relaxed text-[#3C3129]"><strong class="text-[#3C2317]">Hours &amp; admission</strong><br>See the <a href="https://www.jessejamesmuseum.org/" target="_blank" rel="noopener" class="text-[#93402A]">official hours page<span class="sr-only"> (external site)</span></a> before visiting.</p>
                <a href="https://www.google.com/maps/search/?api=1&query=21216+James+Farm+Rd+Kearney+MO+64060" target="_blank" rel="noopener"
                    class="cc-hoverable rounded-lg bg-[#93402A] px-4 py-3 text-center text-[14.5px] font-extrabold text-white no-underline hover:bg-[#7C3522]">Get directions<span class="sr-only"> (opens external map)</span></a>
                <a href="tel:816-736-8500"
                    class="cc-hoverable rounded-lg border-2 border-[#93402A] px-4 py-[11px] text-center text-[14.5px] font-bold text-[#93402A] no-underline hover:bg-[#F5E6DF]">Group visits &amp; field trips</a>
                <p class="m-0 border-t border-[#F0E4D6] pt-3 text-[12.5px] leading-normal text-[#5A646C]">Accessibility: paved trail to the farmhouse; accessible parking and restrooms at the museum. Call ahead for mobility assistance.</p>
            </div>

            <a href="{{ route('clay-demo.historic-sites') }}#jesse-james-bank-museum"
                class="cc-hoverable block overflow-hidden rounded-xl border border-[#E4D5C2] bg-white no-underline hover:border-[#93402A] hover:shadow-[0_8px_24px_rgba(60,35,20,.12)]">
                <span class="relative block h-[140px]">
                    <img src="{{ asset('img/demos/clay-county/jj-bank-square.webp') }}" alt="The Jesse James Bank Museum building in Liberty" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                </span>
                <span class="block p-4.5">
                    <span class="mt-2 block px-1 text-[11px] font-bold tracking-[.06em] text-[#93402A] uppercase">Continue the story</span>
                    <span class="cc-serif block px-1 text-[17px] font-semibold text-[#3C2317]">Jesse James Bank Museum</span>
                    <span class="block px-1 pb-2 pt-1 text-[13px] leading-normal text-[#5A646C]">The 1866 Liberty bank robbery site — 20 minutes away on the historic square.</span>
                </span>
            </a>

            @if ($siteEvents->isNotEmpty())
                <div class="rounded-xl border border-[#E4D5C2] bg-white p-5">
                    <div class="mb-2.5 flex items-baseline justify-between">
                        <h2 class="m-0 text-xs font-extrabold tracking-[.08em] text-[#93402A] uppercase">Upcoming here</h2>
                        <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 text-[10px] font-bold tracking-[.05em] text-[#7A5200] uppercase">Demo</span>
                    </div>
                    <div class="flex flex-col gap-2.5">
                        @foreach ($siteEvents as $event)
                            <a href="{{ route('clay-demo.events') }}#event-{{ $event['slug'] }}" class="flex items-center gap-3 no-underline">
                                <span class="w-11 flex-none rounded-md bg-[#F5E6DF] py-1.5 text-center">
                                    <span class="block text-[9px] font-extrabold tracking-[.08em] text-[#93402A]">{{ $event['monthShort'] }}</span>
                                    <span class="block text-base font-extrabold text-[#3C2317]">{{ $event['day'] }}</span>
                                </span>
                                <span class="text-[13.5px] font-bold leading-snug text-[#3C2317]">{{ $event['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Nearby --}}
            <div class="rounded-xl border border-[#E4D5C2] bg-[#F7F0E8] p-5">
                <h2 class="m-0 mb-2.5 text-xs font-extrabold tracking-[.08em] text-[#93402A] uppercase">Nearby</h2>
                <ul class="m-0 flex list-none flex-col gap-2 p-0 text-[13.5px] font-semibold">
                    <li><a href="{{ route('clay-demo.historic-sites') }}#mt-gilead-church" class="text-[#3C2317]">Mt. Gilead Church &amp; School — 10 min</a></li>
                    <li><a href="{{ route('clay-demo.explore') }}#dest-tryst-falls-park" class="text-[#3C2317]">Tryst Falls Park — 10 min</a></li>
                    <li><a href="{{ route('clay-demo.smithville-lake') }}" class="text-[#3C2317]">Smithville Lake — 15 min</a></li>
                </ul>
            </div>
        </aside>
    </div>
@endsection
