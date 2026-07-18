@extends('demo.clay.layout', [
    'active' => 'home',
    'title' => 'Clay County Parks, Recreation & Historic Sites — Your Virtual Welcome Center (Concept Demo)',
    'metaDescription' => 'Explore Smithville Lake, 80 miles of trails, and five historic sites — with today\'s conditions, reservations, and events in one place. Concept demo for Clay County RFP 78-26.',
    'ogImage' => 'hero-kayaks-smithville.webp',
])

@php
    $bySlug = collect($destinations)->keyBy('slug');
    $featured = [$bySlug['smithville-lake'], $bySlug['jesse-james-birthplace'], $bySlug['tryst-falls-park'], $bySlug['pharis-farm']];
    $upcoming = collect($events)->sortBy('date')->take(3);
    $today = now()->timezone('America/Chicago')->format('l, F j');
@endphp

@push('head')
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'GovernmentOrganization',
        'name' => 'Clay County Parks, Recreation & Historic Sites',
        'url' => route('clay-demo.home'),
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
    <section aria-label="Welcome" class="cc-on-dark bg-[#0B3A4E]">
        <div class="mx-auto flex max-w-[1440px] flex-col-reverse lg:min-h-[560px] lg:flex-row">
            <div class="flex flex-col justify-center gap-5 px-4 py-9 text-[#FAF7F0] md:px-8 lg:w-[600px] lg:flex-none lg:gap-6 lg:py-16 xl:pl-12 xl:pr-16">
                <p class="m-0 flex items-center gap-2.5 text-[13px] font-bold tracking-[.12em] text-[#9CC3D2] uppercase">
                    <span class="inline-block h-0.5 w-7 bg-[#B98A54]" aria-hidden="true"></span>
                    Your virtual welcome center
                </p>
                <h1 class="m-0 text-3xl font-extrabold leading-[1.12] tracking-[-.02em] md:text-4xl xl:text-[54px] xl:leading-[1.08]">
                    Explore the lake. Walk the trails. Step into history.
                </h1>
                <p class="m-0 max-w-[44ch] text-base leading-relaxed text-[#C9DAE1] lg:text-[19px]">
                    Smithville Lake, 80 miles of trails, five historic sites, and every campground, beach, and marina in between — all in one place, with today's conditions up front.
                </p>
                <div class="mt-1 flex flex-wrap gap-3.5">
                    <a href="{{ route('clay-demo.explore') }}"
                        class="cc-hoverable rounded-lg bg-[#E7C55C] px-6 py-3.5 text-base font-extrabold text-[#2A2000] no-underline hover:bg-[#F0D276]">Explore Clay County</a>
                    <button type="button" @click="openWebtrac('Campsite reservation', '752 sites at Camp Branch and Crows Creek campgrounds.')"
                        class="cc-hoverable rounded-lg border-2 border-[#6F97A8] px-6 py-3 text-base font-bold text-[#FAF7F0] hover:border-[#FAF7F0] hover:bg-[#FAF7F0]/10">
                        Reserve a campsite
                    </button>
                </div>
                <dl class="m-0 mt-3 flex flex-wrap gap-x-7 gap-y-3 border-t border-[#9CC3D2]/30 pt-4">
                    <div><dt class="sr-only">Acres of parks</dt><dd class="m-0 text-xl font-extrabold lg:text-2xl">6,000+</dd><dd class="m-0 text-[13px] text-[#9CC3D2]">acres of parks</dd></div>
                    <div><dt class="sr-only">Miles of trails</dt><dd class="m-0 text-xl font-extrabold lg:text-2xl">80.5 mi</dd><dd class="m-0 text-[13px] text-[#9CC3D2]">of trails</dd></div>
                    <div><dt class="sr-only">Campsites</dt><dd class="m-0 text-xl font-extrabold lg:text-2xl">752</dd><dd class="m-0 text-[13px] text-[#9CC3D2]">campsites</dd></div>
                    <div><dt class="sr-only">Historic sites</dt><dd class="m-0 text-xl font-extrabold lg:text-2xl">5</dd><dd class="m-0 text-[13px] text-[#9CC3D2]">historic sites</dd></div>
                </dl>
            </div>
            <div class="relative min-h-[220px] flex-1 md:min-h-[300px]">
                <img src="{{ asset('img/demos/clay-county/hero-kayaks-smithville.webp') }}"
                    alt="Kayaks lined up at a boat ramp on Smithville Lake under a clear blue sky"
                    class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
            </div>
        </div>
    </section>

    {{-- ============ Quick actions ============ --}}
    <div class="relative z-[2] mx-auto -mt-6 max-w-[1440px] px-4 md:px-8 lg:-mt-11 xl:px-12">
        <nav aria-label="Quick actions" class="grid grid-cols-2 overflow-hidden rounded-xl border border-[#E0D9CB] bg-white shadow-[0_6px_24px_rgba(20,30,35,.1)] md:grid-cols-3 xl:grid-cols-6">
            <button type="button" @click="openWebtrac('Campsite reservation', '752 sites at Camp Branch and Crows Creek campgrounds.')"
                class="cc-hoverable flex min-h-14 flex-col items-center gap-2 border-b border-r border-[#EFE9DD] px-2.5 py-5 hover:bg-[#F7F2E7] xl:border-b-0">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 3 19h18L12 3Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 10v9" stroke="#0E5A73" stroke-width="1.8"/></svg>
                <span class="text-sm font-bold text-[#232A2E]">Reserve camping</span>
            </button>
            <button type="button" @click="alertsOpen = true"
                class="cc-hoverable flex min-h-14 flex-col items-center gap-2 border-b border-r border-[#EFE9DD] px-2.5 py-5 hover:bg-[#F7F2E7] md:border-r xl:border-b-0">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 14c2-2 4.5-2 6.5 0s4.5 2 6.5 0 4.5-2 7 0" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/><path d="M2 19c2-2 4.5-2 6.5 0s4.5 2 6.5 0 4.5-2 7 0" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/><path d="M6 9a6 6 0 0 1 12 0" stroke="#B98A54" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span class="text-sm font-bold text-[#232A2E]">Lake conditions</span>
            </button>
            <a href="{{ route('clay-demo.trails') }}"
                class="cc-hoverable flex min-h-14 flex-col items-center gap-2 border-b border-r border-[#EFE9DD] px-2.5 py-5 no-underline hover:bg-[#F7F2E7] md:border-r-0 xl:border-b-0 xl:border-r">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20c3-7 6-9 8-9s3 3 5 3 3-2 3-2" stroke="#35663C" stroke-width="1.8" stroke-linecap="round"/><path d="M4 20h16" stroke="#35663C" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="2 3"/></svg>
                <span class="text-sm font-bold text-[#232A2E]">Find a trail</span>
            </a>
            <a href="{{ route('clay-demo.smithville-lake') }}#beaches"
                class="cc-hoverable flex min-h-14 flex-col items-center gap-2 border-r border-[#EFE9DD] px-2.5 py-5 no-underline hover:bg-[#F7F2E7]">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 18h18M5 18l2-8h10l2 8" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 10V5m0 0 4 2.5L12 10" stroke="#B98A54" stroke-width="1.8" stroke-linejoin="round"/></svg>
                <span class="text-sm font-bold text-[#232A2E]">Beaches &amp; marinas</span>
            </a>
            <a href="{{ route('clay-demo.historic-sites') }}"
                class="cc-hoverable flex min-h-14 flex-col items-center gap-2 border-r-0 border-[#EFE9DD] px-2.5 py-5 no-underline hover:bg-[#F7F2E7] md:border-r">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20V9l8-5 8 5v11" stroke="#93402A" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 20v-6h6v6" stroke="#93402A" stroke-width="1.8" stroke-linejoin="round"/></svg>
                <span class="text-sm font-bold text-[#232A2E]">Historic sites</span>
            </a>
            <a href="{{ route('clay-demo.events') }}"
                class="cc-hoverable flex min-h-14 flex-col items-center gap-2 px-2.5 py-5 no-underline hover:bg-[#F7F2E7]">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="#0E5A73" stroke-width="1.8"/><path d="M3 10h18M8 3v4m8-4v4" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span class="text-sm font-bold text-[#232A2E]">View events</span>
            </a>
        </nav>
    </div>

    {{-- ============ Today in Clay County Parks ============ --}}
    <section aria-label="Today in Clay County Parks" class="mx-auto max-w-[1440px] px-4 pb-2 pt-10 md:px-8 xl:px-12 xl:pt-14">
        <div class="mb-4 flex flex-wrap items-baseline justify-between gap-2">
            <div class="flex flex-wrap items-center gap-x-3.5 gap-y-1.5">
                <h2 class="m-0 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[28px]">Today in Clay County Parks</h2>
                <span class="text-sm text-[#5A646C]">{{ $today }} · Updated 7:00 AM</span>
                <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Prototype data</span>
            </div>
            <button type="button" @click="alertsOpen = true" class="text-[15px] font-bold text-[#0E5A73] underline underline-offset-2">All conditions &amp; alerts</button>
        </div>

        <div class="cc-scrollbar-none -mx-4 flex gap-3.5 overflow-x-auto px-4 md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0 xl:grid-cols-5">
            @foreach ([
                ['label' => 'Lake level', 'status' => 'Normal pool', 'tone' => 'ok', 'note' => 'Sample condition · all ramps usable'],
                ['label' => 'Beaches', 'status' => 'Open', 'tone' => 'ok', 'note' => 'Water quality tested biweekly'],
                ['label' => 'Boat ramps', 'status' => 'Partial', 'tone' => 'warn', 'note' => 'Sample: Crows Creek lanes reduced'],
                ['label' => 'Campgrounds', 'status' => 'Open', 'tone' => 'ok', 'note' => 'Reserve through WebTrac'],
                ['label' => 'Trails', 'status' => 'All open', 'tone' => 'ok', 'note' => 'Sample: dry, good footing'],
            ] as $card)
                <div class="{{ $card['tone'] === 'warn' ? 'border-[#E7C55C]' : 'border-[#E0D9CB]' }} flex w-56 flex-none flex-col gap-2 rounded-[10px] border bg-white px-4 py-4 md:w-auto">
                    <p class="m-0 text-xs font-bold tracking-[.08em] text-[#5A646C] uppercase">{{ $card['label'] }}</p>
                    <p class="m-0 flex items-center gap-2">
                        @if ($card['tone'] === 'warn')
                            <svg class="flex-none" width="12" height="12" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5 15 14H1L8 1.5Z" fill="#7A5200"/></svg>
                            <span class="text-[17px] font-extrabold text-[#5A4200]">{{ $card['status'] }}</span>
                        @else
                            <span class="h-2.5 w-2.5 flex-none rounded-full bg-[#256E3C]" aria-hidden="true"></span>
                            <span class="text-[17px] font-extrabold text-[#1C4A28]">{{ $card['status'] }}</span>
                        @endif
                    </p>
                    <p class="m-0 text-[13px] text-[#5A646C]">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ Featured destinations ============ --}}
    <section aria-label="Featured destinations" class="mx-auto max-w-[1440px] px-4 pb-3 pt-10 md:px-8 xl:px-12 xl:pt-14">
        <div class="mb-5 flex items-baseline justify-between gap-3">
            <h2 class="m-0 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[28px]">Where do you want to go?</h2>
            <a href="{{ route('clay-demo.explore') }}" class="text-[15px] font-bold text-[#0E5A73]">Explore all destinations</a>
        </div>
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($featured as $dest)
                @include('demo.clay.partials.destination-card', ['dest' => $dest])
            @endforeach
        </div>
    </section>

    {{-- ============ Experience selector ============ --}}
    <section aria-label="Experience categories" class="mx-auto max-w-[1440px] px-4 pb-3 pt-10 md:px-8 xl:px-12">
        <h2 class="m-0 mb-5 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[28px]">Find your kind of day</h2>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-8">
            @foreach ([
                ['label' => 'On the water', 'href' => route('clay-demo.smithville-lake').'#activities', 'color' => '#0E5A73', 'hover' => 'hover:border-[#0E5A73] hover:bg-[#F2F7F9]', 'icon' => '<path d="M3 15h18l-2 4H5l-2-4Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M6 15V8l6-3v10" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/>'],
                ['label' => 'Camping', 'href' => route('clay-demo.smithville-lake').'#camping', 'color' => '#0E5A73', 'hover' => 'hover:border-[#0E5A73] hover:bg-[#F2F7F9]', 'icon' => '<path d="M12 3 3 19h18L12 3Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 10v9" stroke="#0E5A73" stroke-width="1.8"/>'],
                ['label' => 'Fishing', 'href' => route('clay-demo.smithville-lake').'#activities', 'color' => '#0E5A73', 'hover' => 'hover:border-[#0E5A73] hover:bg-[#F2F7F9]', 'icon' => '<path d="M3 12s3-5 9-5 9 5 9 5-3 5-9 5-9-5-9-5Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="12" r="1.6" fill="#0E5A73"/>'],
                ['label' => 'Trails', 'href' => route('clay-demo.trails'), 'color' => '#35663C', 'hover' => 'hover:border-[#35663C] hover:bg-[#F4F8F3]', 'icon' => '<path d="M4 20c3-7 6-9 8-9s3 3 5 3 3-2 3-2" stroke="#35663C" stroke-width="1.8" stroke-linecap="round"/><path d="M4 20h16" stroke="#35663C" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="2 3"/>'],
                ['label' => 'Beaches', 'href' => route('clay-demo.smithville-lake').'#beaches', 'color' => '#B98A54', 'hover' => 'hover:border-[#B98A54] hover:bg-[#FAF5EC]', 'icon' => '<circle cx="12" cy="8" r="4" stroke="#B98A54" stroke-width="1.8"/><path d="M3 20c2-2 5-3 9-3s7 1 9 3" stroke="#B98A54" stroke-width="1.8" stroke-linecap="round"/>'],
                ['label' => 'Historic sites', 'href' => route('clay-demo.historic-sites'), 'color' => '#93402A', 'hover' => 'hover:border-[#93402A] hover:bg-[#F9F1ED]', 'icon' => '<path d="M4 20V9l8-5 8 5v11" stroke="#93402A" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 20v-6h6v6" stroke="#93402A" stroke-width="1.8" stroke-linejoin="round"/>'],
                ['label' => 'Nature', 'href' => route('clay-demo.explore'), 'color' => '#35663C', 'hover' => 'hover:border-[#35663C] hover:bg-[#F4F8F3]', 'icon' => '<path d="M12 21V11" stroke="#35663C" stroke-width="1.8" stroke-linecap="round"/><path d="M12 12c-5 0-7-3-7-7 4 0 7 2 7 7Zm0-3c0-4 2.5-6 6-6 0 3.5-2 6-6 6Z" stroke="#35663C" stroke-width="1.8" stroke-linejoin="round"/>'],
                ['label' => 'Family activities', 'href' => route('clay-demo.events'), 'color' => '#0E5A73', 'hover' => 'hover:border-[#0E5A73] hover:bg-[#F2F7F9]', 'icon' => '<rect x="3" y="5" width="18" height="16" rx="2" stroke="#0E5A73" stroke-width="1.8"/><path d="M3 10h18M8 3v4m8-4v4" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/>'],
            ] as $tile)
                <a href="{{ $tile['href'] }}"
                    class="cc-hoverable {{ $tile['hover'] }} flex min-h-14 flex-col items-center gap-2 rounded-[10px] border border-[#E0D9CB] bg-white px-3 py-4 text-center text-[13.5px] font-bold text-[#0B3A4E] no-underline">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">{!! $tile['icon'] !!}</svg>
                    {{ $tile['label'] }}
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============ Build your day (interactive planner) ============ --}}
    <section id="build-your-day" aria-label="Build your day" class="mx-auto max-w-[1440px] scroll-mt-24 px-4 py-10 md:px-8 xl:px-12 xl:py-14">
        <div class="cc-on-dark overflow-hidden rounded-2xl bg-[#0B3A4E]" x-data="ccPlanner">
            <div class="flex flex-col xl:flex-row">
                <div class="flex flex-1 flex-col justify-center gap-4 p-6 text-[#FAF7F0] md:p-10 xl:p-12">
                    <p class="m-0 flex items-center gap-2.5 text-[13px] font-bold tracking-[.12em] text-[#9CC3D2] uppercase">
                        <span class="inline-block h-0.5 w-7 bg-[#B98A54]" aria-hidden="true"></span>
                        Build your day
                    </p>
                    <h2 class="m-0 text-[26px] font-extrabold leading-[1.15] tracking-[-.01em] xl:text-[34px]">Morning on the water,<br>afternoon in 1866.</h2>
                    <p class="m-0 max-w-[48ch] text-[15px] leading-relaxed text-[#C9DAE1] xl:text-[16.5px]">Tell us how much time you have and what you're into — we'll line up a sample day from real Clay County stops, right here in your browser.</p>
                    <span class="self-start rounded border border-[#9CC3D2]/50 px-2 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#9CC3D2] uppercase">Prototype itinerary · sample data</span>
                </div>

                <div class="w-full flex-none border-t border-[#9CC3D2]/25 p-6 md:p-10 xl:w-[560px] xl:border-l xl:border-t-0">
                    <form @submit.prevent="generate()" class="flex flex-col gap-5">
                        <fieldset class="m-0 border-0 p-0">
                            <legend class="mb-2 p-0 text-[13px] font-bold text-[#9CC3D2]">How much time do you have?</legend>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="t in ['2 hours', 'Half day', 'Full day']" :key="t">
                                    <button type="button" @click="time = t" :aria-pressed="time === t"
                                        class="min-h-10 rounded-full border-[1.5px] px-4 py-2 text-[13px] font-bold"
                                        :class="time === t ? 'border-[#E7C55C] bg-[#E7C55C] text-[#2A2000]' : 'border-[#6F97A8] text-[#FAF7F0]'"
                                        x-text="t"></button>
                                </template>
                            </div>
                        </fieldset>

                        <fieldset class="m-0 border-0 p-0">
                            <legend class="mb-2 p-0 text-[13px] font-bold text-[#9CC3D2]">What are you into?</legend>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="i in ['On the water', 'Trails', 'History', 'Family', 'Nature']" :key="i">
                                    <button type="button" @click="toggleInterest(i)" :aria-pressed="interests.includes(i)"
                                        class="min-h-10 rounded-full border-[1.5px] px-4 py-2 text-[13px] font-bold"
                                        :class="interests.includes(i) ? 'border-[#E7C55C] bg-[#FAF7F0]/10 text-[#FAF7F0]' : 'border-[#6F97A8] text-[#C9DAE1]'"
                                        x-text="i"></button>
                                </template>
                            </div>
                        </fieldset>

                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            <label class="flex min-h-10 cursor-pointer items-center gap-2.5 text-sm font-semibold text-[#C9DAE1]">
                                <input type="checkbox" x-model="accessibleOnly" class="h-4.5 w-4.5 accent-[#E7C55C]">
                                Accessible routes only
                            </label>
                            <label class="flex min-h-10 cursor-pointer items-center gap-2.5 text-sm font-semibold text-[#C9DAE1]">
                                <input type="checkbox" x-model="indoorPref" class="h-4.5 w-4.5 accent-[#E7C55C]">
                                Prefer indoors
                            </label>
                        </div>

                        <button type="submit" class="cc-hoverable self-start rounded-lg bg-[#E7C55C] px-6 py-3 text-[15px] font-extrabold text-[#2A2000] hover:bg-[#F0D276]">
                            Build my day
                        </button>
                    </form>

                    <div x-cloak x-show="itinerary.length" class="mt-6 flex flex-col" role="status" aria-label="Sample itinerary">
                        <template x-for="(stop, i) in itinerary" :key="stop.title">
                            <div class="flex items-stretch gap-3.5">
                                <div class="flex flex-col items-center">
                                    <span class="flex h-[30px] w-[30px] flex-none items-center justify-center rounded-full bg-[#E7C55C] text-sm font-extrabold text-[#2A2000]" x-text="i + 1"></span>
                                    <span x-show="i < itinerary.length - 1" class="w-0.5 flex-1 bg-[#9CC3D2]/40" aria-hidden="true"></span>
                                </div>
                                <div class="mb-3 flex-1 rounded-[10px] border border-[#9CC3D2]/30 bg-[#FAF7F0]/10 px-4 py-3">
                                    <p class="m-0 flex items-baseline justify-between gap-2 text-[15px] font-bold text-[#FAF7F0]">
                                        <span x-text="stop.title"></span>
                                        <span class="text-xs font-semibold text-[#9CC3D2]" x-text="stop.slot"></span>
                                    </p>
                                    <p class="m-0 text-[13px] text-[#9CC3D2]" x-text="stop.detail"></p>
                                </div>
                            </div>
                        </template>
                        <p x-show="itinerary.length" class="m-0 mt-1 text-xs text-[#9CC3D2]/80">Sample plan from local demo data — drive times and hours are illustrative.</p>
                    </div>

                    <p x-cloak x-show="attempted && ! itinerary.length" class="m-0 mt-5 rounded-[10px] border border-[#9CC3D2]/30 bg-[#FAF7F0]/10 px-4 py-3 text-sm text-[#C9DAE1]">
                        No stops match that mix — try removing "prefer indoors" or adding another interest.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ============ Upcoming events ============ --}}
    <section aria-label="Upcoming events" class="mx-auto max-w-[1440px] px-4 pb-10 pt-2 md:px-8 xl:px-12 xl:pb-14">
        <div class="mb-5 flex flex-wrap items-baseline justify-between gap-2">
            <div class="flex items-baseline gap-3.5">
                <h2 class="m-0 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[28px]">Coming up</h2>
                <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Demo events</span>
            </div>
            <a href="{{ route('clay-demo.events') }}" class="text-[15px] font-bold text-[#0E5A73]">Full events calendar</a>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ($upcoming as $event)
                <a href="{{ route('clay-demo.events') }}#event-{{ $event['slug'] }}"
                    class="cc-hoverable flex items-start gap-4 rounded-xl border border-[#E0D9CB] bg-white p-5 no-underline hover:border-[#B98A54] hover:shadow-[0_8px_24px_rgba(20,30,35,.12)]">
                    <span class="w-[62px] flex-none rounded-lg bg-[#F2ECDF] py-2.5 text-center">
                        <span class="block text-[11px] font-extrabold tracking-[.1em] text-[#93402A]">{{ $event['monthShort'] }}</span>
                        <span class="block text-2xl font-extrabold leading-tight text-[#0B3A4E]">{{ $event['day'] }}</span>
                    </span>
                    <span class="flex flex-col gap-1.5">
                        <span class="text-[11px] font-bold tracking-[.06em] text-[{{ $event['category'] === 'historic' ? '#93402A' : ($event['category'] === 'nature' ? '#35663C' : '#0E5A73') }}] uppercase">{{ $event['categoryLabel'] }}</span>
                        <span class="text-[17px] font-extrabold leading-snug text-[#0B3A4E]">{{ $event['title'] }}</span>
                        <span class="text-[13.5px] text-[#5A646C]">{{ $event['time'] }} · {{ $event['location'] }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============ Notification signup CTA ============ --}}
    <section aria-label="Alert notifications signup" class="mx-auto max-w-[1440px] px-4 pb-14 md:px-8 xl:px-12 xl:pb-16">
        <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-[#E0D9CB] bg-[#F2ECDF] p-7 md:flex-row md:items-center md:p-10">
            <div class="flex items-center gap-5">
                <svg class="hidden flex-none sm:block" width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3a6 6 0 0 0-6 6v3.5L4 16h16l-2-3.5V9a6 6 0 0 0-6-6Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M9.5 19a2.5 2.5 0 0 0 5 0" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/></svg>
                <div>
                    <h2 class="m-0 text-[21px] font-extrabold text-[#0B3A4E]">Know before you go</h2>
                    <p class="m-0 mt-1 text-[15px] text-[#5A646C]">Get lake conditions, closures, and event reminders by email or text. Choose only the topics you care about.</p>
                </div>
            </div>
            <button type="button" @click="openNotify()"
                class="cc-hoverable flex-none rounded-lg bg-[#0E5A73] px-6 py-3.5 text-[15px] font-bold text-white hover:bg-[#0C4A5F]">Sign up for alerts</button>
        </div>
    </section>

    @include('demo.clay.partials.sticky-reserve')
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ccPlanner', () => ({
                time: 'Full day',
                interests: ['On the water', 'History'],
                accessibleOnly: false,
                indoorPref: false,
                itinerary: [],
                attempted: false,

                stops: [
                    { title: 'Camp Branch Beach', detail: 'Morning swim · beach opens 8:30 AM', slot: 'Morning', tags: ['On the water', 'Family'], indoor: false, accessible: false },
                    { title: 'Pontoon rental, Camp Branch Marina', detail: 'Two hours on the water · rentals at the marina', slot: 'Morning', tags: ['On the water', 'Family'], indoor: false, accessible: true },
                    { title: 'Little Platte North Trail', detail: 'Easy 2.5-mile paved walk · lake views', slot: 'Morning', tags: ['Trails', 'Nature'], indoor: false, accessible: true },
                    { title: 'Smoke & Davey Trail System', detail: 'Single-track ride · 4.8 miles in three loops', slot: 'Afternoon', tags: ['Trails'], indoor: false, accessible: false },
                    { title: 'Smithville Lake Nature Center', detail: 'Hands-on exhibits · beside the park office', slot: 'Midday', tags: ['Nature', 'Family'], indoor: true, accessible: true },
                    { title: 'Picnic at Tryst Falls Park', detail: 'Shelters and playground by the waterfall', slot: 'Midday', tags: ['Family', 'Nature'], indoor: false, accessible: false },
                    { title: 'Jesse James Birthplace', detail: 'Museum, film, and paved trail to the farmhouse', slot: 'Afternoon', tags: ['History', 'Family'], indoor: true, accessible: true },
                    { title: 'Jesse James Bank Museum', detail: 'The 1866 bank on Liberty\'s historic square', slot: 'Afternoon', tags: ['History'], indoor: true, accessible: false },
                    { title: 'Mt. Gilead Church & School', detail: '1870s church and one-room school grounds', slot: 'Afternoon', tags: ['History'], indoor: false, accessible: false },
                ],

                toggleInterest(i) {
                    this.interests = this.interests.includes(i)
                        ? this.interests.filter((x) => x !== i)
                        : [...this.interests, i];
                },

                generate() {
                    this.attempted = true;
                    const max = this.time === '2 hours' ? 1 : (this.time === 'Half day' ? 2 : 3);
                    const slotOrder = { Morning: 0, Midday: 1, Afternoon: 2 };

                    let picks = this.stops.filter((s) =>
                        (! this.interests.length || s.tags.some((t) => this.interests.includes(t)))
                        && (! this.accessibleOnly || s.accessible)
                        && (! this.indoorPref || s.indoor)
                    );

                    // Prefer one stop per part of day (in day order), then backfill
                    // from the remaining matches until the time budget is spent.
                    picks = picks.sort((a, b) => slotOrder[a.slot] - slotOrder[b.slot]);
                    const seen = new Set();
                    const spread = [];
                    for (const stop of picks) {
                        if (! seen.has(stop.slot)) {
                            seen.add(stop.slot);
                            spread.push(stop);
                        }
                    }
                    for (const stop of picks) {
                        if (spread.length >= max) break;
                        if (! spread.includes(stop)) spread.push(stop);
                    }

                    this.itinerary = spread
                        .slice(0, max)
                        .sort((a, b) => slotOrder[a.slot] - slotOrder[b.slot]);
                },
            }));
        });
    </script>
@endpush
