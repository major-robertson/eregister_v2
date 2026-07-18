@extends('demo.clay.layout', [
    'active' => 'lake',
    'title' => 'Smithville Lake — Camping, Boating, Beaches & Trails | Clay County Parks (Concept Demo)',
    'metaDescription' => 'Plan a day at Smithville Lake: 752 campsites, three marinas, two swim beaches, 80 miles of trails, and today\'s lake conditions up front. Concept demo for RFP 78-26.',
    'ogImage' => 'hero-kayaks-smithville.webp',
])

@php
    $today = now()->timezone('America/Chicago')->format('l, F j');
    $lakeFaqs = collect($faqs)->whereIn('topic', ['reservations', 'beaches', 'camping'])->take(4);
    $lakeTrails = collect($trails);
    $lakeEvents = collect($events)->whereIn('destinationSlug', ['smithville-lake', 'nature-center', 'camp-branch-beach', 'little-platte-park'])->sortBy('date')->take(2);
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Park',
        'name' => 'Smithville Lake',
        'url' => route('clay-demo.smithville-lake'),
        'telephone' => '816-407-3400',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '17201 Paradesian',
            'addressLocality' => 'Smithville',
            'addressRegion' => 'MO',
            'postalCode' => '64089',
        ],
    ], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')

    {{-- ============ Hero ============ --}}
    <div class="relative h-[240px] bg-[#0B3A4E] md:h-[320px] xl:h-[400px]">
        <img src="{{ asset('img/demos/clay-county/hero-kayaks-smithville.webp') }}"
            alt="Kayaks at the water's edge on Smithville Lake"
            class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
    </div>

    <div class="mx-auto flex max-w-[1440px] flex-col items-start gap-10 px-4 md:px-8 xl:flex-row xl:px-12">
        <div class="w-full min-w-0 flex-1">

            {{-- Overlapping title card --}}
            <div class="relative z-[2] -mt-10 rounded-2xl border border-[#E0D9CB] bg-white p-6 shadow-[0_10px_30px_rgba(20,30,35,.12)] md:p-8 xl:-mt-[72px]">
                <nav aria-label="Breadcrumb" class="mb-2.5 text-[13px] text-[#5A646C]">
                    <a href="{{ route('clay-demo.home') }}">Home</a>
                    <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
                    <a href="{{ route('clay-demo.explore') }}">Explore</a>
                    <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
                    <span class="font-semibold text-[#232A2E]" aria-current="page">Smithville Lake</span>
                </nav>
                <div class="flex flex-col items-start justify-between gap-6 md:flex-row">
                    <div>
                        <h1 class="m-0 mb-2.5 text-[30px] font-extrabold tracking-[-.02em] text-[#0B3A4E] xl:text-[42px]">Smithville Lake</h1>
                        <p class="m-0 max-w-[60ch] text-[15px] leading-relaxed text-[#5A646C] xl:text-[16.5px]">Northland Kansas City's big backyard — open water, quiet coves, two swim beaches, three marinas, and more campsites than any other park in the county. 30 minutes from downtown KC.</p>
                    </div>
                    <div class="flex w-full flex-none flex-col gap-2.5 md:w-[230px]">
                        <button type="button" @click="openWebtrac('Smithville Lake camping', '752 sites at Camp Branch and Crows Creek campgrounds.')"
                            class="cc-hoverable flex items-center justify-center gap-2 rounded-lg bg-[#E7C55C] px-4 py-3.5 text-[15px] font-extrabold text-[#2A2000] hover:bg-[#F0D276]">
                            Reserve a campsite
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        </button>
                        <a href="https://www.google.com/maps/search/?api=1&query=17201+Paradesian+Smithville+MO+64089" target="_blank" rel="noopener"
                            class="cc-hoverable rounded-lg border-2 border-[#0E5A73] px-4 py-3 text-center text-[15px] font-bold text-[#0E5A73] no-underline hover:bg-[#F2F7F9]">
                            Get directions<span class="sr-only"> (opens external map in a new tab)</span>
                        </a>
                        <span class="text-center text-xs text-[#8A9199]">Reservations open in WebTrac (external site)</span>
                    </div>
                </div>
            </div>

            {{-- Know before you go --}}
            <section id="conditions" aria-label="Know before you go" class="mt-7 rounded-2xl border border-[#C6DAE3] bg-[#F0F5F7] p-5 md:p-7">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
                    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1.5">
                        <h2 class="m-0 text-[19px] font-extrabold text-[#0B3A4E] xl:text-[21px]">Know before you go</h2>
                        <span class="text-[13px] text-[#5A646C]">Updated 7:00 AM · {{ $today }}</span>
                        <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Prototype data</span>
                    </div>
                    <button type="button" @click="openNotify()" class="text-sm font-bold text-[#0E5A73] underline underline-offset-2">Sign up for lake alerts</button>
                </div>
                <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
                    @foreach ([
                        ['label' => 'Lake level', 'status' => 'Normal pool', 'tone' => 'ok', 'note' => 'Sample condition'],
                        ['label' => 'Water quality', 'status' => 'Beaches open', 'tone' => 'ok', 'note' => 'Tested biweekly in season'],
                        ['label' => 'Boat ramps', 'status' => 'Partial', 'tone' => 'warn', 'note' => 'Sample: Crows Creek lanes reduced'],
                        ['label' => 'Campgrounds', 'status' => 'Open', 'tone' => 'ok', 'note' => 'Reserve through WebTrac'],
                    ] as $card)
                        <div class="{{ $card['tone'] === 'warn' ? 'border-[#E7C55C]' : 'border-[#DCE5EA]' }} rounded-[10px] border bg-white px-4 py-3.5">
                            <p class="m-0 mb-1.5 text-[11.5px] font-bold tracking-[.07em] text-[#5A646C] uppercase">{{ $card['label'] }}</p>
                            <p class="m-0 flex items-center gap-1.5">
                                @if ($card['tone'] === 'warn')
                                    <svg class="flex-none" width="12" height="12" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5 15 14H1L8 1.5Z" fill="#7A5200"/></svg>
                                    <span class="text-base font-extrabold text-[#5A4200]">{{ $card['status'] }}</span>
                                @else
                                    <span class="h-[9px] w-[9px] flex-none rounded-full bg-[#256E3C]" aria-hidden="true"></span>
                                    <span class="text-base font-extrabold text-[#1C4A28]">{{ $card['status'] }}</span>
                                @endif
                            </p>
                            <p class="m-0 mt-1 text-[12.5px] text-[#5A646C]">{{ $card['note'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Stat tiles --}}
            <section aria-label="Lake statistics" class="mt-9 grid grid-cols-2 gap-3.5 xl:grid-cols-4">
                @foreach ([
                    ['stat' => '7,190', 'label' => 'acres of water at normal pool', 'cite' => 'Per U.S. Army Corps of Engineers', 'accent' => '#0E5A73'],
                    ['stat' => '175 mi', 'label' => 'of shoreline to explore', 'cite' => 'Per U.S. Army Corps of Engineers', 'accent' => '#0E5A73'],
                    ['stat' => '752', 'label' => 'campsites across two campgrounds', 'cite' => null, 'accent' => '#B98A54'],
                    ['stat' => '80.5 mi', 'label' => 'of walking, biking & equestrian trails', 'cite' => null, 'accent' => '#35663C'],
                ] as $tile)
                    <div class="rounded-b-[10px] border border-t-[3px] border-[#E0D9CB] bg-white px-5 py-4" style="border-top-color: {{ $tile['accent'] }}">
                        <p class="m-0 text-[24px] font-extrabold text-[#0B3A4E] xl:text-[30px]">{{ $tile['stat'] }}</p>
                        <p class="m-0 text-[13.5px] text-[#5A646C]">{{ $tile['label'] }}
                            @if ($tile['cite'])
                                <span class="mt-0.5 block text-[11px] text-[#8A9199]">{{ $tile['cite'] }}</span>
                            @endif
                        </p>
                    </div>
                @endforeach
            </section>

            {{-- Activities --}}
            <section id="activities" aria-label="Activities" class="mt-11 scroll-mt-24">
                <h2 class="m-0 mb-4.5 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[26px]">On the water and off</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ([
                        ['title' => 'Boating', 'body' => 'Five boat ramps, rentals at the marinas, and open water past every cove.', 'color' => '#0E5A73', 'href' => '#beaches', 'icon' => '<path d="M3 15h18l-2 4H5l-2-4Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M6 15V8l6-3v10" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/>'],
                        ['title' => 'Fishing', 'body' => 'Crappie, bass, walleye and catfish waters, plus an ADA-accessible fishing dock.', 'color' => '#0E5A73', 'href' => '#beaches', 'icon' => '<path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="12" r="1.6" fill="#0E5A73"/>'],
                        ['title' => 'Beaches', 'body' => 'Two sand beaches, open May 1 – Sep 15, 8:30 AM to sunset. No lifeguards.', 'color' => '#B98A54', 'href' => '#beaches', 'icon' => '<circle cx="12" cy="8" r="4" stroke="#B98A54" stroke-width="1.8"/><path d="M3 20c2-2 5-3 9-3s7 1 9 3" stroke="#B98A54" stroke-width="1.8" stroke-linecap="round"/>'],
                        ['title' => 'Trails', 'body' => '37 miles paved, 11.5 miles of singletrack, and 32 miles of equestrian trail.', 'color' => '#35663C', 'href' => '#trails', 'icon' => '<path d="M4 20c3-7 6-9 8-9s3 3 5 3 3-2 3-2" stroke="#35663C" stroke-width="1.8" stroke-linecap="round"/><path d="M4 20h16" stroke="#35663C" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="2 3"/>'],
                        ['title' => 'Equestrian', 'body' => 'A 32-mile horse trail network with an equestrian camping area and stalls.', 'color' => '#35663C', 'href' => '#camping', 'icon' => '<path d="M5 20c0-6 3-10 8-11l6-3-1 5c2 1 2 4 0 5l-3 4" stroke="#35663C" stroke-width="1.8" stroke-linejoin="round"/><path d="M5 20h10" stroke="#35663C" stroke-width="1.8" stroke-linecap="round"/>'],
                        ['title' => 'Golf & disc golf', 'body' => 'A 38-hole championship complex at Paradise Pointe plus three disc golf courses.', 'color' => '#0E5A73', 'href' => '#beaches', 'icon' => '<circle cx="12" cy="12" r="3.2" stroke="#0E5A73" stroke-width="1.8"/><path d="M12 3v3m0 12v3M3 12h3m12 0h3" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/>'],
                        ['title' => 'Nature Center', 'body' => 'Year-round programs and exhibits at the Smithville Lake Nature Center.', 'color' => '#35663C', 'href' => '#nature', 'icon' => '<path d="M12 21V11" stroke="#35663C" stroke-width="1.8" stroke-linecap="round"/><path d="M12 12c-5 0-7-3-7-7 4 0 7 2 7 7Zm0-3c0-4 2.5-6 6-6 0 3.5-2 6-6 6Z" stroke="#35663C" stroke-width="1.8" stroke-linejoin="round"/>'],
                        ['title' => 'Camping', 'body' => '752 sites from full-hookup RV pads to primitive tent loops at two campgrounds.', 'color' => '#0E5A73', 'href' => '#camping', 'icon' => '<path d="M12 3 3 19h18L12 3Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 10v9" stroke="#0E5A73" stroke-width="1.8"/>'],
                        ['title' => 'More to do', 'body' => 'Dog park, trap range, playgrounds, hunting areas, and an RC aircraft field.', 'color' => '#B98A54', 'href' => '#activities', 'icon' => '<circle cx="12" cy="12" r="9" stroke="#B98A54" stroke-width="1.8"/><path d="M8 12h8M12 8v8" stroke="#B98A54" stroke-width="1.8" stroke-linecap="round"/>'],
                    ] as $activity)
                        <div class="cc-hoverable flex flex-col gap-2 rounded-xl border border-[#E0D9CB] bg-white p-5 hover:shadow-[0_6px_18px_rgba(20,30,35,.1)]" style="--hover-border: {{ $activity['color'] }}">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $activity['icon'] !!}</svg>
                            <h3 class="m-0 text-[16.5px] font-extrabold text-[#0B3A4E]">{{ $activity['title'] }}</h3>
                            <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">{{ $activity['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Campgrounds --}}
            <section id="camping" aria-label="Campgrounds" class="mt-11 scroll-mt-24">
                <div class="mb-4.5 flex items-baseline justify-between pb-4">
                    <h2 class="m-0 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[26px]">Campgrounds</h2>
                    <a href="{{ route('clay-demo.plan-your-visit') }}" class="text-[14.5px] font-bold text-[#0E5A73]">Reservation guidelines</a>
                </div>
                <div class="grid gap-4 md:grid-cols-3">
                    <div class="flex flex-col overflow-hidden rounded-xl border border-[#E0D9CB] bg-white">
                        <div class="relative h-[150px]">
                            <img src="{{ asset('img/demos/clay-county/campground-camp-branch-1.webp') }}" alt="A campsite under the trees at Camp Branch campground" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        </div>
                        <div class="flex flex-1 flex-col gap-2 p-4.5">
                            <h3 class="m-0 mt-2 px-1 text-[17px] font-extrabold text-[#0B3A4E]">Camp Branch</h3>
                            <p class="m-0 px-1 text-[13.5px] leading-normal text-[#5A646C]">East side, near the main office, marina and beach. Walk to the water on Bonebender Trail.</p>
                            <p class="m-0 mt-0.5 flex flex-wrap gap-2 px-1">
                                <span class="rounded bg-[#F2ECDF] px-2 py-1 text-[11.5px] font-bold text-[#3C454C]">200 electric (50-amp)</span>
                                <span class="rounded bg-[#F2ECDF] px-2 py-1 text-[11.5px] font-bold text-[#3C454C]">146 unimproved</span>
                            </p>
                            <button type="button" @click="openWebtrac('Camp Branch Campground', '200 electric and 146 unimproved sites on the east shore.')"
                                class="mt-auto px-1 pb-2 pt-2.5 text-left text-sm font-bold text-[#0E5A73] underline underline-offset-2">Reserve at Camp Branch →</button>
                        </div>
                    </div>
                    <div class="flex flex-col overflow-hidden rounded-xl border border-[#E0D9CB] bg-white">
                        <div class="relative h-[150px]">
                            <img src="{{ asset('img/demos/clay-county/campground-crows-creek-1.webp') }}" alt="Campsites at Crows Creek campground" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        </div>
                        <div class="flex flex-1 flex-col gap-2 p-4.5">
                            <h3 class="m-0 mt-2 px-1 text-[17px] font-extrabold text-[#0B3A4E]">Crows Creek</h3>
                            <p class="m-0 px-1 text-[13.5px] leading-normal text-[#5A646C]">North shore camping close to the Crows Creek trail system and quiet coves for paddlers.</p>
                            <p class="m-0 mt-0.5 flex flex-wrap gap-2 px-1">
                                <span class="rounded bg-[#F2ECDF] px-2 py-1 text-[11.5px] font-bold text-[#3C454C]">91 water/electric</span>
                                <span class="rounded bg-[#F2ECDF] px-2 py-1 text-[11.5px] font-bold text-[#3C454C]">181 electric</span>
                                <span class="rounded bg-[#F2ECDF] px-2 py-1 text-[11.5px] font-bold text-[#3C454C]">134 unimproved</span>
                            </p>
                            <button type="button" @click="openWebtrac('Crows Creek Campground', '91 water/electric, 181 electric, and 134 unimproved sites.')"
                                class="mt-auto px-1 pb-2 pt-2.5 text-left text-sm font-bold text-[#0E5A73] underline underline-offset-2">Reserve at Crows Creek →</button>
                        </div>
                    </div>
                    <div class="flex flex-col overflow-hidden rounded-xl border border-[#E0D9CB] bg-white">
                        <div class="relative h-[150px]">
                            <img src="{{ asset('img/demos/clay-county/campground-camp-branch-3.webp') }}" alt="A grassy campsite loop at Smithville Lake" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        </div>
                        <div class="flex flex-1 flex-col gap-2 p-4.5">
                            <h3 class="m-0 mt-2 px-1 text-[17px] font-extrabold text-[#0B3A4E]">Group &amp; equestrian camps</h3>
                            <p class="m-0 px-1 text-[13.5px] leading-normal text-[#5A646C]">A group camp area with four covered shelters, plus an equestrian camping area with horse stalls on the trail network.</p>
                            <p class="m-0 mt-0.5 flex flex-wrap gap-2 px-1">
                                <span class="rounded bg-[#F2ECDF] px-2 py-1 text-[11.5px] font-bold text-[#3C454C]">4 picnic shelters</span>
                                <span class="rounded bg-[#F2ECDF] px-2 py-1 text-[11.5px] font-bold text-[#3C454C]">Horse stalls</span>
                            </p>
                            <button type="button" @click="openWebtrac('Group & equestrian camping', 'Group camp shelters and the equestrian camping area.')"
                                class="mt-auto px-1 pb-2 pt-2.5 text-left text-sm font-bold text-[#0E5A73] underline underline-offset-2">Check availability →</button>
                        </div>
                    </div>
                </div>
                <p class="m-0 mt-3.5 flex items-start gap-2.5 rounded-[10px] border border-[#E0D9CB] bg-[#F2ECDF] px-4.5 py-3.5 text-[13.5px] leading-normal text-[#3C454C]">
                    <svg class="mt-0.5 flex-none" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="#5A646C" stroke-width="1.8"/><path d="M12 11v5" stroke="#5A646C" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="8" r="1" fill="#5A646C"/></svg>
                    <span>Check-in 1:00 PM, check-out 11:00 AM (Sundays 5:00/3:00). Two-night minimum on weekends. Quiet hours 10 PM – 6 AM. See <a href="{{ route('clay-demo.plan-your-visit') }}">rules &amp; regulations</a> for full policies.</span>
                </p>
            </section>

            {{-- Beaches & marinas --}}
            <section id="beaches" aria-label="Beaches and marinas" class="mt-11 scroll-mt-24">
                <h2 class="m-0 mb-4.5 pb-4 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[26px]">Beaches &amp; marinas</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex flex-col gap-2.5 rounded-xl border border-[#E0D9CB] bg-white p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Camp Branch Beach</h3>
                            <span class="flex items-center gap-1.5 text-[12.5px] font-bold text-[#1C4A28]"><span class="h-2 w-2 rounded-full bg-[#256E3C]" aria-hidden="true"></span>Open · sample</span>
                        </div>
                        <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">East side, across from the main park office. Showers, changing rooms, restrooms. May 1 – Sep 15, 8:30 AM to sunset.</p>
                        <p class="m-0 flex gap-3.5 text-[13px] font-bold">
                            <a href="https://www.google.com/maps/search/?api=1&query=Camp+Branch+Park+Smithville+Lake+MO" target="_blank" rel="noopener">Directions<span class="sr-only"> (external map)</span></a>
                            <a href="https://www.claycountymo.gov/167/Beaches" target="_blank" rel="noopener">Water quality<span class="sr-only"> (external site)</span></a>
                        </p>
                    </div>
                    <div class="flex flex-col gap-2.5 rounded-xl border border-[#E0D9CB] bg-white p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Little Platte Beach</h3>
                            <span class="flex items-center gap-1.5 text-[12.5px] font-bold text-[#1C4A28]"><span class="h-2 w-2 rounded-full bg-[#256E3C]" aria-hidden="true"></span>Open · sample</span>
                        </div>
                        <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">West side off 180th Street, reachable by the Little Platte Trail. Near the marina, golf complex, and Woodhenge.</p>
                        <p class="m-0 flex gap-3.5 text-[13px] font-bold">
                            <a href="https://www.google.com/maps/search/?api=1&query=Little+Platte+Park+Smithville+MO" target="_blank" rel="noopener">Directions<span class="sr-only"> (external map)</span></a>
                            <a href="https://www.claycountymo.gov/167/Beaches" target="_blank" rel="noopener">Water quality<span class="sr-only"> (external site)</span></a>
                        </p>
                    </div>
                    <div class="flex flex-col gap-2.5 rounded-xl border border-[#E0D9CB] bg-white p-5">
                        <h3 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Camp Branch Marina</h3>
                        <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">17201 Paradesian · 816-407-3400. Boat rentals, covered slips, and a bait shop on the east shore.</p>
                        <p class="m-0 flex gap-3.5 text-[13px] font-bold">
                            <button type="button" @click="openWebtrac('Camp Branch Marina', 'Boat slips and rentals on the east shore.')" class="font-bold text-[#0E5A73] underline underline-offset-2">Slip rentals</button>
                        </p>
                    </div>
                    <div class="flex flex-col gap-2.5 rounded-xl border border-[#E0D9CB] bg-white p-5">
                        <h3 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Paradise Pointe Marina &amp; Sailboat Cove</h3>
                        <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">3008 NE 180th St · 816-407-3425. West-shore marina plus dedicated sailboat moorage at Sailboat Cove — 452 covered slips lakewide.</p>
                        <p class="m-0 flex gap-3.5 text-[13px] font-bold">
                            <button type="button" @click="openWebtrac('Paradise Pointe Marina', 'Slips, dry storage, and mooring balls on the west shore.')" class="font-bold text-[#0E5A73] underline underline-offset-2">Slip rentals</button>
                        </p>
                    </div>
                </div>
            </section>

            {{-- Trails around the lake --}}
            <section id="trails" aria-label="Trails at the lake" class="mt-11 scroll-mt-24">
                <div class="mb-4.5 flex items-baseline justify-between pb-4">
                    <h2 class="m-0 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[26px]">Trails around the lake</h2>
                    <a href="{{ route('clay-demo.trails') }}" class="text-[14.5px] font-bold text-[#0E5A73]">Open the Trails Explorer</a>
                </div>
                <div class="flex flex-wrap gap-3">
                    @foreach ($lakeTrails as $trail)
                        <a href="{{ route('clay-demo.trails') }}#trail-{{ $trail['slug'] }}"
                            class="cc-hoverable flex items-center gap-3 rounded-[10px] border border-[#E0D9CB] bg-white px-4.5 py-3.5 no-underline hover:border-[#35663C]">
                            @if ($trail['status'] === 'caution')
                                <svg class="flex-none" width="12" height="12" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5 15 14H1L8 1.5Z" fill="#7A5200"/></svg>
                            @else
                                <span class="h-[9px] w-[9px] flex-none rounded-full bg-[#256E3C]" aria-hidden="true"></span>
                            @endif
                            <span class="text-[14.5px] font-bold text-[#232A2E]">{{ $trail['name'] }}</span>
                            <span class="text-[12.5px] text-[#5A646C]">{{ $trail['distance'] }} · {{ $trail['activityLabel'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            {{-- FAQ --}}
            <section id="faq" aria-label="Frequently asked questions" class="mt-11 scroll-mt-24">
                <h2 class="m-0 mb-4.5 pb-4 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[26px]">Frequently asked questions</h2>
                <div class="flex flex-col gap-2.5">
                    @foreach ($lakeFaqs as $faq)
                        <details class="rounded-[10px] border border-[#E0D9CB] bg-white px-5" @if ($loop->first) open @endif>
                            <summary class="flex min-h-11 items-center justify-between gap-3 py-4 text-[15.5px] font-bold text-[#0B3A4E]">
                                {{ $faq['question'] }}
                                <svg class="cc-chevron flex-none" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m3 6 5 5 5-5" stroke="#5A646C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </summary>
                            <p class="m-0 pb-4 pl-1 text-[14.5px] leading-relaxed text-[#3C454C]">{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>

            {{-- Nearby historic sites --}}
            <section aria-label="Nearby historic sites" class="mb-14 mt-11 rounded-xl border border-l-4 border-[#E4D5C2] border-l-[#93402A] bg-[#F7F0E8] p-6 md:p-7">
                <div class="mb-3.5 flex items-baseline justify-between gap-3">
                    <h2 class="cc-serif m-0 text-[20px] font-bold text-[#5C2A1A] xl:text-[22px]">History within a short drive</h2>
                    <a href="{{ route('clay-demo.historic-sites') }}" class="flex-none text-sm font-bold text-[#93402A]">All historic sites</a>
                </div>
                <div class="grid gap-3.5 md:grid-cols-3">
                    <a href="{{ route('clay-demo.jesse-james-birthplace') }}" class="cc-hoverable rounded-[10px] border border-[#E4D5C2] bg-white px-4.5 py-4 no-underline hover:border-[#93402A]">
                        <span class="cc-serif block text-[15.5px] font-bold text-[#5C2A1A]">Jesse James Birthplace</span>
                        <span class="mt-1 block text-[13px] text-[#5A646C]">Kearney · 15 min from the dam</span>
                    </a>
                    <a href="{{ route('clay-demo.historic-sites') }}#mt-gilead-church" class="cc-hoverable rounded-[10px] border border-[#E4D5C2] bg-white px-4.5 py-4 no-underline hover:border-[#93402A]">
                        <span class="cc-serif block text-[15.5px] font-bold text-[#5C2A1A]">Mt. Gilead Church &amp; School</span>
                        <span class="mt-1 block text-[13px] text-[#5A646C]">Kearney · living history programs</span>
                    </a>
                    <a href="{{ route('clay-demo.explore') }}#dest-little-platte-park" class="cc-hoverable rounded-[10px] border border-[#E4D5C2] bg-white px-4.5 py-4 no-underline hover:border-[#93402A]">
                        <span class="cc-serif block text-[15.5px] font-bold text-[#5C2A1A]">Woodhenge &amp; Akers Cemetery</span>
                        <span class="mt-1 block text-[13px] text-[#5A646C]">In Little Platte Park, at the lake</span>
                    </a>
                </div>
            </section>
        </div>

        {{-- Sidebar --}}
        <aside class="mb-14 flex w-full flex-none flex-col gap-4 xl:sticky xl:top-5 xl:mt-7 xl:w-[320px]" aria-label="Visitor information">
            <div class="flex flex-col gap-3 rounded-xl border border-[#E0D9CB] bg-white p-5">
                <h2 class="m-0 text-xs font-extrabold tracking-[.08em] text-[#5A646C] uppercase">Visitor information</h2>
                <p class="m-0 text-sm leading-relaxed text-[#3C454C]"><strong class="text-[#0B3A4E]">Park office</strong><br>17201 Paradesian, Smithville, MO 64089<br><a href="tel:816-407-3400">816-407-3400</a></p>
                <p class="m-0 text-sm leading-relaxed text-[#3C454C]"><strong class="text-[#0B3A4E]">Getting here</strong><br>30 minutes from downtown Kansas City; 15 minutes from Smithville and Kearney.</p>
                <p class="m-0 text-sm leading-relaxed text-[#3C454C]"><strong class="text-[#0B3A4E]">Hours &amp; fees</strong><br>See <a href="{{ route('clay-demo.plan-your-visit') }}">hours &amp; rules</a>, or the county's <a href="https://www.claycountymo.gov/165/Parks-Recreation" target="_blank" rel="noopener">official pages<span class="sr-only"> (external site)</span></a> for current details.</p>
            </div>

            <div class="cc-on-dark flex flex-col gap-2.5 rounded-xl bg-[#0B3A4E] p-5 text-[#FAF7F0]">
                <h2 class="m-0 text-xs font-extrabold tracking-[.08em] text-[#9CC3D2] uppercase">Reservations</h2>
                <p class="m-0 text-sm leading-normal text-[#C9DAE1]">Campsites, shelters, and slips are reserved through WebTrac, the county's reservation system.</p>
                <button type="button" @click="openWebtrac('Smithville Lake reservations', 'Campsites, shelters, and slips in WebTrac.')"
                    class="cc-hoverable flex items-center justify-center gap-2 rounded-lg bg-[#E7C55C] px-4 py-3 text-[14.5px] font-extrabold text-[#2A2000] hover:bg-[#F0D276]">
                    Open WebTrac
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
                <span class="text-center text-[11.5px] text-[#9CC3D2]">Opens an external site in a new tab</span>
            </div>

            <div id="nature" class="overflow-hidden rounded-xl border border-[#E0D9CB] bg-white">
                <div class="relative h-[150px]">
                    <img src="{{ asset('img/demos/clay-county/nature-center.webp') }}" alt="Wildlife exhibits inside the Smithville Lake Nature Center" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                </div>
                <div class="p-4.5">
                    <h2 class="m-0 mt-2 px-1 text-[15.5px] font-extrabold text-[#0B3A4E]">Smithville Lake Nature Center</h2>
                    <p class="m-0 mt-1 px-1 text-[13px] leading-normal text-[#5A646C]">Exhibits, native wildlife, and family programs year-round.</p>
                    <a href="{{ route('clay-demo.events') }}" class="mt-2 inline-block px-1 pb-2 text-[13.5px] font-bold">Programs &amp; events →</a>
                </div>
            </div>

            @if ($lakeEvents->isNotEmpty())
                <div class="flex flex-col gap-3 rounded-xl border border-[#E0D9CB] bg-white p-5">
                    <div class="flex items-baseline justify-between gap-2">
                        <h2 class="m-0 text-xs font-extrabold tracking-[.08em] text-[#5A646C] uppercase">At the lake soon</h2>
                        <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 text-[10px] font-bold tracking-[.05em] text-[#7A5200] uppercase">Demo</span>
                    </div>
                    @foreach ($lakeEvents as $event)
                        <a href="{{ route('clay-demo.events') }}#event-{{ $event['slug'] }}" class="no-underline">
                            <span class="block text-sm font-bold text-[#0B3A4E]">{{ $event['title'] }}</span>
                            <span class="block text-[12.5px] text-[#5A646C]">{{ $event['dateLabel'] }} · {{ $event['time'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </aside>
    </div>

    @include('demo.clay.partials.sticky-reserve', ['reserveContext' => ['Smithville Lake camping', '752 sites at Camp Branch and Crows Creek campgrounds.']])
@endsection
