@extends('demo.clay.layout', [
    'active' => 'historic',
    'title' => 'Historic Sites — Jesse James Birthplace, Mt. Gilead & Pharis Farm | Clay County Parks (Concept Demo)',
    'metaDescription' => 'Five Clay County historic sites where the story is still standing: the Jesse James Birthplace and Bank Museum, Mt. Gilead Church and School, and Pharis Farm.',
    'ogImage' => 'james-farm-house.webp',
])

@php
    $bySlug = collect($destinations)->keyBy('slug');
    $historyEvents = collect($events)->filter(fn ($e) => in_array($e['slug'], ['one-room-school-day', 'lantern-tours', 'liberty-raid-talk', 'pharis-harvest-day']))->sortBy('date')->take(3);
@endphp

@section('content')
    <div x-data="{ experience: 'all' }">

        {{-- ============ Reversed split hero ============ --}}
        <section aria-label="Historic sites introduction" class="cc-on-dark bg-[#2B1B14]">
            <div class="mx-auto flex max-w-[1440px] flex-col lg:min-h-[480px] lg:flex-row">
                <div class="relative min-h-[220px] flex-1 md:min-h-[300px]">
                    <img src="{{ asset('img/demos/clay-county/james-farm-house.webp') }}"
                        alt="The white farmhouse at the Jesse James Birthplace, seen past a split-rail fence"
                        class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                </div>
                <div class="flex flex-col justify-center gap-5 px-4 py-9 text-[#F5EDE2] md:px-8 lg:w-[560px] lg:flex-none lg:px-14 lg:py-16">
                    <p class="m-0 flex items-center gap-2.5 text-[13px] font-bold tracking-[.14em] text-[#D9A88A] uppercase">
                        <span class="inline-block h-0.5 w-7 bg-[#B98A54]" aria-hidden="true"></span>
                        Historic Sites
                    </p>
                    <h1 class="cc-serif m-0 text-[30px] font-semibold leading-[1.15] tracking-[-.01em] xl:text-[46px] xl:leading-[1.12]">
                        Two hundred years of Missouri, kept in place.
                    </h1>
                    <p class="m-0 max-w-[46ch] text-base leading-relaxed text-[#D9C7B8] xl:text-[17px]">
                        Clay County preserves five sites where the story is still standing — the farm that raised the James brothers, the bank tied to the raid that made them famous, an 1870s church and schoolhouse, and a working farmstead from 1927.
                    </p>
                    <div class="flex flex-wrap gap-3.5">
                        <a href="#plan-history-day" class="cc-hoverable rounded-lg bg-[#B98A54] px-6 py-3.5 text-[15px] font-extrabold text-[#221507] no-underline hover:bg-[#CB9E68]">Plan a history day</a>
                        <a href="https://www.jessejamesmuseum.org/" target="_blank" rel="noopener"
                            class="cc-hoverable inline-flex items-center gap-2 rounded-lg border-2 border-[#8A6B54] px-5 py-3 text-[15px] font-bold text-[#F5EDE2] no-underline hover:border-[#F5EDE2]">
                            Hours &amp; admission
                            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <span class="sr-only">(opens external site)</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- Editorial intro --}}
        <section aria-label="About the historic sites" class="mx-auto max-w-[1440px] px-4 pb-2 pt-10 md:px-8 xl:px-12 xl:pt-14">
            <p class="cc-serif m-0 max-w-[70ch] text-[17px] leading-[1.65] text-[#3C3129] xl:text-[21px]">
                These aren't reconstructions. Visitors walk the same farmyard where Jesse James was born in 1847, and stand in the Liberty bank preserved as it looked in 1866. The county's historic sites team keeps each place open, honest, and in context — no theater, just the real thing and the people who can tell you about it.
            </p>
        </section>

        {{-- Experience filter --}}
        <div class="mx-auto flex max-w-[1440px] flex-wrap items-center gap-2.5 px-4 pb-2 pt-7 md:px-8 xl:px-12" role="group" aria-label="Filter by experience">
            <span class="mr-1 text-[13px] font-bold tracking-[.08em] text-[#5A646C] uppercase">Experience</span>
            @foreach ([
                'all' => 'All sites',
                'museum' => 'Museums',
                'living-history' => 'Living history',
                'weddings' => 'Weddings & rentals',
                'field-trips' => 'Field trips',
                'special-events' => 'Special events',
            ] as $key => $label)
                <button type="button" @click="experience = '{{ $key }}'" :aria-pressed="experience === '{{ $key }}'"
                    class="cc-hoverable min-h-11 rounded-full border-[1.5px] px-4.5 py-2.5 text-sm font-bold"
                    :class="experience === '{{ $key }}' ? 'border-[#93402A] bg-[#93402A] text-white' : 'border-[#B9B0A0] bg-white text-[#232A2E] hover:border-[#93402A]'">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- ============ Site cards ============ --}}
        <section aria-label="Historic sites" class="mx-auto max-w-[1440px] px-4 pb-6 pt-7 md:px-8 xl:px-12">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-6">

                {{-- Jesse James Birthplace (large) --}}
                <a id="jesse-james-birthplace" href="{{ route('clay-demo.jesse-james-birthplace') }}" x-show="['all', 'museum'].includes(experience)"
                    class="cc-hoverable flex scroll-mt-24 flex-col overflow-hidden rounded-xl border border-[#E4D5C2] bg-white no-underline hover:border-[#93402A] hover:shadow-[0_10px_28px_rgba(60,35,20,.16)] md:col-span-3">
                    <span class="relative block h-52 md:h-[280px]">
                        <img src="{{ asset('img/demos/clay-county/james-farm-house.webp') }}" alt="{{ $bySlug['jesse-james-birthplace']['imageAlt'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    </span>
                    <span class="flex flex-col gap-2 p-6">
                        <span class="flex gap-2">
                            <span class="rounded bg-[#F5E6DF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#93402A] uppercase">Museum</span>
                            <span class="rounded bg-[#F2ECDF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#3C454C] uppercase">Kearney</span>
                        </span>
                        <span class="cc-serif text-xl font-semibold text-[#3C2317] xl:text-2xl">Jesse James Birthplace</span>
                        <span class="text-[14.5px] leading-normal text-[#5A646C]">The James family farm — the farmhouse where Jesse was born, a 20-minute film, and the world's largest collection of James family artifacts, from Jesse's boots to Frank's surrender letter.</span>
                        <span class="text-[13px] text-[#5A646C]">21216 James Farm Rd, Kearney · 816-736-8500</span>
                        <span class="mt-0.5 text-sm font-bold text-[#93402A]">Visit the farm →</span>
                    </span>
                </a>

                {{-- Bank Museum (large) --}}
                <div id="jesse-james-bank-museum" x-show="['all', 'museum'].includes(experience)"
                    class="flex scroll-mt-24 flex-col overflow-hidden rounded-xl border border-[#E4D5C2] bg-white md:col-span-3">
                    <span class="relative block h-52 md:h-[280px]">
                        <img src="{{ asset('img/demos/clay-county/jj-bank-museum.webp') }}" alt="{{ $bySlug['jesse-james-bank-museum']['imageAlt'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    </span>
                    <div class="flex flex-col gap-2 p-6">
                        <p class="m-0 flex gap-2">
                            <span class="rounded bg-[#F5E6DF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#93402A] uppercase">Museum</span>
                            <span class="rounded bg-[#F2ECDF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#3C454C] uppercase">Liberty</span>
                        </p>
                        <h2 class="cc-serif m-0 text-xl font-semibold text-[#3C2317] xl:text-2xl">Jesse James Bank Museum</h2>
                        <p class="m-0 text-[14.5px] leading-normal text-[#5A646C]">On Liberty's historic square: the site of the nation's first successful daylight peacetime bank robbery, in 1866 — attributed to the James gang, never solved, and preserved as it stood that February afternoon.</p>
                        <p class="m-0 text-[13px] text-[#5A646C]">103 N Water St, Liberty · <a href="tel:816-736-8510" class="text-[#93402A]">816-736-8510</a></p>
                    </div>
                </div>

                {{-- Mt. Gilead Church --}}
                <div id="mt-gilead-church" x-show="['all', 'weddings'].includes(experience)"
                    class="flex scroll-mt-24 flex-col overflow-hidden rounded-xl border border-[#E4D5C2] bg-white md:col-span-2">
                    <span class="relative block h-[190px]">
                        <img src="{{ asset('img/demos/clay-county/mt-gilead-church.webp') }}" alt="{{ $bySlug['mt-gilead-church']['imageAlt'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    </span>
                    <div class="flex flex-col gap-2 p-5">
                        <p class="m-0"><span class="rounded bg-[#FCF1CF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Weddings &amp; rentals</span></p>
                        <h2 class="cc-serif m-0 text-[19px] font-semibold text-[#3C2317]">Mt. Gilead Church</h2>
                        <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">An 1870s country church near Kearney, rentable for weddings, reunions, showers, and picnics. Call to view and reserve.</p>
                        <p class="m-0 text-[13px] text-[#5A646C]">15918 Plattsburg Rd, Kearney · <a href="tel:816-736-8500" class="text-[#93402A]">816-736-8500</a></p>
                    </div>
                </div>

                {{-- Mt. Gilead School --}}
                <div id="mt-gilead-school" x-show="['all', 'living-history', 'field-trips'].includes(experience)"
                    class="flex scroll-mt-24 flex-col overflow-hidden rounded-xl border border-[#E4D5C2] bg-white md:col-span-2">
                    <span class="relative block h-[190px]">
                        <img src="{{ asset('img/demos/clay-county/mt-gilead-school.webp') }}" alt="{{ $bySlug['mt-gilead-school']['imageAlt'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    </span>
                    <div class="flex flex-col gap-2 p-5">
                        <p class="m-0 flex gap-2">
                            <span class="rounded bg-[#E5F0E4] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#35663C] uppercase">Living history</span>
                            <span class="rounded bg-[#F2ECDF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#3C454C] uppercase">Field trips</span>
                        </p>
                        <h2 class="cc-serif m-0 text-[19px] font-semibold text-[#3C2317]">Mt. Gilead School</h2>
                        <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">A one-room 1870s schoolhouse where classes still run 1880s-style — slates, McGuffey's readers, and a teacher in period dress. Four-hour program for up to 30.</p>
                        <p class="m-0 text-[13px] text-[#5A646C]">15918 Plattsburg Rd, Kearney · <a href="tel:816-736-8500" class="text-[#93402A]">816-736-8500</a></p>
                    </div>
                </div>

                {{-- Pharis Farm --}}
                <div id="pharis-farm" x-show="['all', 'special-events'].includes(experience)"
                    class="flex scroll-mt-24 flex-col overflow-hidden rounded-xl border border-[#E4D5C2] bg-white md:col-span-2">
                    <span class="relative block h-[190px]">
                        <img src="{{ asset('img/demos/clay-county/pharis-farm.webp') }}" alt="{{ $bySlug['pharis-farm']['imageAlt'] }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                    </span>
                    <div class="flex flex-col gap-2 p-5">
                        <p class="m-0"><span class="rounded bg-[#F2ECDF] px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#5A646C] uppercase">Special events</span></p>
                        <h2 class="cc-serif m-0 text-[19px] font-semibold text-[#3C2317]">Pharis Farm</h2>
                        <p class="m-0 text-[13.5px] leading-normal text-[#5A646C]">Donald Pharis's 1927 farmstead east of Liberty — 160 acres shaped by an early advocate of soil conservation. Open during special events.</p>
                        <p class="m-0 text-[13px] text-[#5A646C]">20611 EE Highway, Liberty · <a href="tel:816-736-8500" class="text-[#93402A]">816-736-8500</a></p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ Plan a history day ============ --}}
        <section id="plan-history-day" aria-label="Plan a history day" class="mx-auto max-w-[1440px] scroll-mt-24 px-4 py-8 md:px-8 xl:px-12">
            <div class="cc-on-dark flex flex-col overflow-hidden rounded-2xl bg-[#3C2317] xl:flex-row">
                <div class="flex flex-1 flex-col justify-center gap-3.5 p-7 text-[#F5EDE2] md:p-11">
                    <p class="m-0 flex items-center gap-2.5 text-[13px] font-bold tracking-[.14em] text-[#D9A88A] uppercase">
                        <span class="inline-block h-0.5 w-7 bg-[#B98A54]" aria-hidden="true"></span>
                        Plan a history day
                    </p>
                    <h2 class="cc-serif m-0 text-[24px] font-semibold leading-[1.2] xl:text-[30px]">The James story, start to finish, in one afternoon.</h2>
                    <p class="m-0 max-w-[52ch] text-[15px] leading-relaxed text-[#D9C7B8] xl:text-[15.5px]">Begin at the farm where the story starts, end at the bank where it turned legend. The two museums are about 20 minutes apart, and Mt. Gilead is on the way.</p>
                    <a href="{{ route('clay-demo.home') }}#build-your-day" class="cc-hoverable mt-1 self-start rounded-lg bg-[#B98A54] px-6 py-3 text-[15px] font-extrabold text-[#221507] no-underline hover:bg-[#CB9E68]">Build this itinerary</a>
                </div>
                <div class="flex w-full flex-none flex-col justify-center p-7 pt-0 md:p-10 xl:w-[480px] xl:pt-10">
                    @foreach ([
                        ['title' => 'Jesse James Birthplace', 'detail' => 'Morning · film + farmhouse trail'],
                        ['title' => 'Mt. Gilead Church & School', 'detail' => 'Midday stop · 10 min drive'],
                        ['title' => 'Jesse James Bank Museum', 'detail' => 'Afternoon · Liberty square · 15 min drive'],
                    ] as $i => $stop)
                        <div class="flex items-stretch gap-3.5">
                            <div class="flex flex-col items-center">
                                <span class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-full bg-[#B98A54] text-sm font-extrabold text-[#221507]">{{ $i + 1 }}</span>
                                @if ($i < 2)
                                    <span class="w-0.5 flex-1 bg-[#D9A88A]/40" aria-hidden="true"></span>
                                @endif
                            </div>
                            <div class="{{ $i < 2 ? 'mb-3' : '' }} flex-1 rounded-[10px] border border-[#D9A88A]/30 bg-[#F5EDE2]/10 px-4 py-3">
                                <p class="m-0 text-[14.5px] font-bold text-[#F5EDE2]">{{ $stop['title'] }}</p>
                                <p class="m-0 text-[12.5px] text-[#D9A88A]">{{ $stop['detail'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ============ Related events ============ --}}
        <section aria-label="History events" class="mx-auto max-w-[1440px] px-4 pb-14 pt-2 md:px-8 xl:px-12">
            <div class="mb-4.5 flex flex-wrap items-baseline justify-between gap-2 pb-4">
                <div class="flex items-baseline gap-3">
                    <h2 class="cc-serif m-0 text-[22px] font-semibold text-[#3C2317] xl:text-[26px]">History, live and in person</h2>
                    <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Demo events</span>
                </div>
                <a href="{{ route('clay-demo.events') }}" class="text-[14.5px] font-bold text-[#93402A]">All events</a>
            </div>
            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($historyEvents as $event)
                    <a href="{{ route('clay-demo.events') }}#event-{{ $event['slug'] }}"
                        class="cc-hoverable flex items-start gap-4 rounded-xl border border-[#E4D5C2] bg-white p-5 no-underline hover:border-[#93402A] hover:shadow-[0_8px_24px_rgba(60,35,20,.12)]">
                        <span class="w-[58px] flex-none rounded-lg bg-[#F5E6DF] py-2 text-center">
                            <span class="block text-[10.5px] font-extrabold tracking-[.08em] text-[#93402A]">{{ $event['monthShort'] }}</span>
                            <span class="block text-[22px] font-extrabold leading-tight text-[#3C2317]">{{ $event['day'] }}</span>
                        </span>
                        <span class="flex flex-col gap-1">
                            <span class="text-base font-extrabold leading-snug text-[#3C2317]">{{ $event['title'] }}</span>
                            <span class="text-[13px] text-[#5A646C]">{{ $event['time'] }} · {{ $event['location'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
