<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => 'State of Florida — Public Information Website (Demo)'])
    <meta name="robots" content="noindex, nofollow" />
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="min-h-screen bg-white font-sans text-slate-900 antialiased"
    x-data="{
        modalOpen: false,
        modalTarget: '',
        mobileMenuOpen: false,
        showPage(name) {
            this.modalTarget = name;
            this.modalOpen = true;
            this.mobileMenuOpen = false;
        }
    }"
    @keydown.escape.window="modalOpen = false">

    {{-- ============================================================ --}}
    {{-- eRegister demo banner                                          --}}
    {{-- ============================================================ --}}
    <div class="bg-slate-900 text-slate-100">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-1 px-4 py-2 text-xs sm:flex-row sm:items-center sm:px-6 lg:px-8">
            <div class="flex items-center gap-2">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                <span>Demo prepared for <strong class="font-semibold text-white">EOG–RFQ–26-03</strong> by eRegister</span>
            </div>
            <a href="{{ route('government.home') }}"
                class="inline-flex items-center gap-1 text-slate-300 transition hover:text-white">
                Exit demo
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Mock State of Florida header (NO seal, NO EOG logo)            --}}
    {{-- ============================================================ --}}
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between gap-6">
                {{-- Wordmark --}}
                <a href="#" @click.prevent="showPage('Home')" class="block">
                    <p class="font-serif text-2xl font-semibold text-slate-900">
                        State of Florida<span class="ml-1 inline-block h-1 w-8 align-middle bg-amber-400"></span>
                    </p>
                    <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                        Official Public Information Website
                    </p>
                </a>

                {{-- Desktop nav --}}
                <nav class="hidden items-center gap-1 lg:flex">
                    @foreach ([
                        ['label' => 'Home', 'active' => true],
                        ['label' => 'About', 'active' => false],
                        ['label' => 'Programs', 'active' => false],
                        ['label' => 'Resources', 'active' => false],
                        ['label' => 'News', 'active' => false],
                        ['label' => 'Contact', 'active' => false],
                    ] as $item)
                        <a href="#" @click.prevent="showPage('{{ $item['label'] }}')"
                            class="relative rounded-md px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'text-slate-900' : 'text-slate-600 hover:text-slate-900' }}">
                            {{ $item['label'] }}
                            @if ($item['active'])
                                <span class="absolute -bottom-0.5 left-3 right-3 h-0.5 bg-amber-400"></span>
                            @endif
                        </a>
                    @endforeach
                </nav>

                {{-- Mobile toggle --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 lg:hidden">
                    <span class="sr-only">Open menu</span>
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileMenuOpen" x-cloak x-transition.opacity
            class="border-t border-slate-200 bg-white lg:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 sm:px-6 lg:px-8">
                @foreach (['Home', 'About', 'Programs', 'Resources', 'News', 'Contact'] as $label)
                    <a href="#" @click.prevent="showPage('{{ $label }}')"
                        class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 hover:text-slate-900">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
    </header>

    {{-- ============================================================ --}}
    {{-- Hero                                                            --}}
    {{-- ============================================================ --}}
    <section class="relative overflow-hidden bg-white">
        {{-- Florida outline watermark (right side, low opacity) --}}
        <div class="pointer-events-none absolute -right-12 top-1/2 hidden -translate-y-1/2 opacity-[0.06] lg:block"
            aria-hidden="true">
            <svg class="h-[460px] w-auto text-slate-900" viewBox="0 0 500 360" fill="currentColor">
                {{-- Simplified Florida outline (panhandle + peninsula + keys hint) --}}
                <path d="M 20 60
                         L 250 55
                         L 290 60
                         L 330 70
                         L 365 85
                         L 395 105
                         L 415 130
                         L 425 165
                         L 425 200
                         L 415 240
                         L 395 280
                         L 365 310
                         L 330 325
                         L 305 320
                         L 290 300
                         L 280 270
                         L 270 235
                         L 255 200
                         L 235 175
                         L 210 158
                         L 175 145
                         L 140 135
                         L 105 122
                         L 75 105
                         L 50 90
                         L 30 75
                         Z" />
                {{-- Florida Keys (small dots curving southwest) --}}
                <circle cx="295" cy="335" r="4" />
                <circle cx="280" cy="342" r="3" />
                <circle cx="263" cy="346" r="3" />
                <circle cx="248" cy="348" r="2.5" />
                <circle cx="234" cy="350" r="2" />
            </svg>
        </div>

        {{-- Sun + palm + water decorative cluster (top-right corner) --}}
        <div class="pointer-events-none absolute right-6 top-8 hidden lg:block" aria-hidden="true">
            <svg class="h-24 w-24" viewBox="0 0 120 120" fill="none">
                {{-- Sun --}}
                <circle cx="92" cy="28" r="10" class="fill-amber-400" />
                <g class="stroke-amber-400" stroke-width="2" stroke-linecap="round">
                    <line x1="92" y1="28" x2="110" y2="28" />
                    <line x1="92" y1="28" x2="104.73" y2="40.73" />
                    <line x1="92" y1="28" x2="92" y2="46" />
                    <line x1="92" y1="28" x2="79.27" y2="40.73" />
                    <line x1="92" y1="28" x2="74" y2="28" />
                    <line x1="92" y1="28" x2="79.27" y2="15.27" />
                    <line x1="92" y1="28" x2="92" y2="10" />
                    <line x1="92" y1="28" x2="104.73" y2="15.27" />
                </g>
                {{-- Palm trunk --}}
                <path d="M 38 100 Q 36 78 42 58" class="stroke-slate-900" stroke-width="3" stroke-linecap="round"
                    fill="none" />
                {{-- Palm fronds --}}
                <path d="M 42 58 Q 22 50 12 60" class="stroke-slate-900" stroke-width="2.5" stroke-linecap="round"
                    fill="none" />
                <path d="M 42 58 Q 28 38 20 30" class="stroke-slate-900" stroke-width="2.5" stroke-linecap="round"
                    fill="none" />
                <path d="M 42 58 Q 50 38 60 32" class="stroke-slate-900" stroke-width="2.5" stroke-linecap="round"
                    fill="none" />
                <path d="M 42 58 Q 60 50 70 56" class="stroke-slate-900" stroke-width="2.5" stroke-linecap="round"
                    fill="none" />
            </svg>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-3xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">An official Florida website</p>
                <h1 class="mt-4 font-serif text-4xl font-semibold leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    Official public information for Florida residents
                </h1>
                <p class="mt-6 max-w-2xl text-lg leading-relaxed text-slate-600">
                    This site provides residents, businesses, and stakeholders with timely, accurate, and accessible
                    information about state programs and public services. Find the resources you need, learn about
                    initiatives across the state, and stay informed.
                </p>
                <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
                    <button type="button" @click="showPage('Programs')"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Browse programs &amp; services
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </button>
                    <button type="button" @click="showPage('About')"
                        class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-slate-700 underline-offset-4 transition hover:text-slate-900 hover:underline">
                        About this site
                    </button>
                </div>

                {{-- Water lines accent under hero --}}
                <div class="mt-14 hidden items-center gap-3 text-slate-300 sm:flex" aria-hidden="true">
                    <svg class="h-4 w-32" viewBox="0 0 128 16" fill="none">
                        <path d="M 0 8 Q 8 2 16 8 T 32 8 T 48 8 T 64 8 T 80 8 T 96 8 T 112 8 T 128 8" stroke="currentColor"
                            stroke-width="1.5" />
                    </svg>
                    <svg class="h-4 w-32 text-slate-200" viewBox="0 0 128 16" fill="none">
                        <path d="M 0 8 Q 8 14 16 8 T 32 8 T 48 8 T 64 8 T 80 8 T 96 8 T 112 8 T 128 8" stroke="currentColor"
                            stroke-width="1.5" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Quick links grid                                                --}}
    {{-- ============================================================ --}}
    <section class="border-t border-slate-200 bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Quick Links</p>
                <h2 class="mt-3 font-serif text-3xl font-semibold text-slate-900 sm:text-4xl">
                    Find what you need
                </h2>
                <p class="mt-4 text-base text-slate-600">
                    Direct access to the most-requested information, services, and updates from across the state.
                </p>
            </div>

            <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $quickLinks = [
                        ['title' => 'Programs &amp; Services', 'description' => 'Explore state-supported programs available to Florida residents and businesses.', 'icon' => 'sun'],
                        ['title' => 'Public Resources', 'description' => 'Forms, guides, and reference materials published for public use.', 'icon' => 'palm'],
                        ['title' => 'News &amp; Updates', 'description' => 'Recent announcements, press releases, and program updates.', 'icon' => 'water'],
                        ['title' => 'Contact &amp; Offices', 'description' => 'Office locations, contact directories, and constituent services.', 'icon' => 'state'],
                    ];
                @endphp

                @foreach ($quickLinks as $link)
                    <button type="button" @click="showPage('{{ strip_tags(html_entity_decode($link['title'])) }}')"
                        class="group flex flex-col items-start rounded-xl border border-slate-200 border-t-2 border-t-transparent bg-white p-6 text-left shadow-sm transition hover:border-t-amber-400 hover:shadow-md">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100 text-slate-900 transition group-hover:bg-slate-900 group-hover:text-amber-400">
                            @switch($link['icon'])
                                @case('sun')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round">
                                        <circle cx="12" cy="12" r="4" />
                                        <line x1="12" y1="2" x2="12" y2="5" />
                                        <line x1="12" y1="19" x2="12" y2="22" />
                                        <line x1="2" y1="12" x2="5" y2="12" />
                                        <line x1="19" y1="12" x2="22" y2="12" />
                                        <line x1="4.93" y1="4.93" x2="7.05" y2="7.05" />
                                        <line x1="16.95" y1="16.95" x2="19.07" y2="19.07" />
                                        <line x1="4.93" y1="19.07" x2="7.05" y2="16.95" />
                                        <line x1="16.95" y1="7.05" x2="19.07" y2="4.93" />
                                    </svg>
                                @break

                                @case('palm')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round">
                                        <path d="M 12 22 Q 11 16 13 10" />
                                        <path d="M 13 10 Q 6 7 3 11" />
                                        <path d="M 13 10 Q 9 4 5 3" />
                                        <path d="M 13 10 Q 17 4 21 3" />
                                        <path d="M 13 10 Q 20 7 21 12" />
                                    </svg>
                                @break

                                @case('water')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round">
                                        <path d="M 2 8 Q 6 4 10 8 T 18 8 T 22 8" />
                                        <path d="M 2 14 Q 6 10 10 14 T 18 14 T 22 14" />
                                        <path d="M 2 20 Q 6 16 10 20 T 18 20 T 22 20" />
                                    </svg>
                                @break

                                @case('state')
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M 2 5 L 14 5 L 16 6 L 18 7 L 20 9 L 21 12 L 21 14 L 20 17 L 18 19 L 16 20 L 15 19 L 14 17 L 13 14 L 12 12 L 10 11 L 8 10 L 5 9 L 3 7 Z" />
                                    </svg>
                                @break
                            @endswitch
                        </div>
                        <h3 class="mt-5 text-base font-semibold text-slate-900">{!! $link['title'] !!}</h3>
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">{!! $link['description'] !!}</p>
                        <span class="mt-4 inline-flex items-center text-sm font-semibold text-slate-900">
                            Learn more
                            <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-0.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- About preview                                                   --}}
    {{-- ============================================================ --}}
    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                {{-- Copy --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">About This Site</p>
                    <h2 class="mt-3 font-serif text-3xl font-semibold text-slate-900 sm:text-4xl">
                        Information from the State of Florida
                    </h2>
                    <p class="mt-6 text-base leading-relaxed text-slate-600">
                        This is the official public information website for the initiative. It is intended to provide
                        residents, community partners, and the press with reliable details on programs, eligibility,
                        and how to participate. All published materials follow state communication standards and
                        accessibility guidelines.
                    </p>
                    <p class="mt-4 text-base leading-relaxed text-slate-600">
                        Content is reviewed and updated by authorized staff. For media inquiries, agency contacts, or
                        constituent services, please use the contact information below.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <button type="button" @click="showPage('About')"
                            class="inline-flex items-center justify-center rounded-md bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Learn more
                        </button>
                        <button type="button" @click="showPage('Resources')"
                            class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                            View resources
                        </button>
                    </div>
                </div>

                {{-- Photo placeholder slot --}}
                {{-- TODO: replace this <div> with <img src="/img/demos/florida-eog/about.jpg" alt="..." class="aspect-[4/3] w-full rounded-xl object-cover" /> when client provides a Florida photo (capitol, coastline, etc.) --}}
                <div class="relative aspect-[4/3] w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                    {{-- Decorative inline SVG until photo is provided --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="h-3/4 w-3/4 text-slate-300" viewBox="0 0 200 150" fill="none"
                            stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                            {{-- Horizon line --}}
                            <line x1="0" y1="100" x2="200" y2="100" />
                            {{-- Sun --}}
                            <circle cx="150" cy="50" r="14" fill="currentColor" class="text-amber-200" stroke="none" />
                            {{-- Palm --}}
                            <path d="M 50 130 Q 48 110 52 90" stroke-width="2" />
                            <path d="M 52 90 Q 38 80 30 84" />
                            <path d="M 52 90 Q 42 72 36 64" />
                            <path d="M 52 90 Q 60 72 70 66" />
                            <path d="M 52 90 Q 64 80 74 84" />
                            {{-- Water --}}
                            <path d="M 0 115 Q 20 110 40 115 T 80 115 T 120 115 T 160 115 T 200 115" />
                            <path d="M 0 125 Q 20 120 40 125 T 80 125 T 120 125 T 160 125 T 200 125" />
                        </svg>
                    </div>
                    <div class="absolute bottom-3 left-3 rounded-md bg-slate-900/80 px-2.5 py-1 text-[11px] font-medium text-white">
                        Photo placeholder &mdash; replace with Florida coastline or capitol image
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- News preview                                                    --}}
    {{-- ============================================================ --}}
    <section class="bg-white py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Latest News</p>
                    <h2 class="mt-3 font-serif text-3xl font-semibold text-slate-900 sm:text-4xl">
                        Updates &amp; announcements
                    </h2>
                </div>
                <button type="button" @click="showPage('News')"
                    class="inline-flex items-center gap-1 self-start text-sm font-semibold text-slate-700 transition hover:text-slate-900 sm:self-auto">
                    View all news
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @php
                    $news = [
                        ['date' => 'May 5, 2026', 'tag' => 'Announcement', 'title' => 'New public information resources available statewide', 'icon' => 'sun'],
                        ['date' => 'April 28, 2026', 'tag' => 'Program Update', 'title' => 'Updated program eligibility guidance for Florida residents', 'icon' => 'palm'],
                        ['date' => 'April 14, 2026', 'tag' => 'Notice', 'title' => 'Office hours and contact information for the upcoming season', 'icon' => 'water'],
                    ];
                @endphp

                @foreach ($news as $article)
                    <article class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                        {{-- Illustration thumbnail --}}
                        <button type="button" @click="showPage('News article')"
                            class="relative flex aspect-[16/9] w-full items-center justify-center bg-slate-100 transition group-hover:bg-slate-50">
                            @switch($article['icon'])
                                @case('sun')
                                    <svg class="h-16 w-16 text-amber-400" viewBox="0 0 24 24" fill="currentColor"
                                        aria-hidden="true">
                                        <circle cx="12" cy="12" r="5" />
                                        <g stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none">
                                            <line x1="12" y1="2" x2="12" y2="5" />
                                            <line x1="12" y1="19" x2="12" y2="22" />
                                            <line x1="2" y1="12" x2="5" y2="12" />
                                            <line x1="19" y1="12" x2="22" y2="12" />
                                            <line x1="4.93" y1="4.93" x2="7.05" y2="7.05" />
                                            <line x1="16.95" y1="16.95" x2="19.07" y2="19.07" />
                                            <line x1="4.93" y1="19.07" x2="7.05" y2="16.95" />
                                            <line x1="16.95" y1="7.05" x2="19.07" y2="4.93" />
                                        </g>
                                    </svg>
                                @break

                                @case('palm')
                                    <svg class="h-16 w-16 text-slate-700" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                                        <path d="M 12 22 Q 11 16 13 10" />
                                        <path d="M 13 10 Q 6 7 3 11" />
                                        <path d="M 13 10 Q 9 4 5 3" />
                                        <path d="M 13 10 Q 17 4 21 3" />
                                        <path d="M 13 10 Q 20 7 21 12" />
                                    </svg>
                                @break

                                @case('water')
                                    <svg class="h-16 w-16 text-slate-700" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true">
                                        <path d="M 2 8 Q 6 4 10 8 T 18 8 T 22 8" />
                                        <path d="M 2 14 Q 6 10 10 14 T 18 14 T 22 14" />
                                        <path d="M 2 20 Q 6 16 10 20 T 18 20 T 22 20" />
                                    </svg>
                                @break
                            @endswitch
                        </button>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-center gap-3 text-xs">
                                <span class="font-semibold uppercase tracking-wider text-amber-600">{{ $article['tag'] }}</span>
                                <span class="text-slate-400">&middot;</span>
                                <time class="text-slate-500">{{ $article['date'] }}</time>
                            </div>
                            <h3 class="mt-3 text-lg font-semibold leading-snug text-slate-900">
                                <button type="button" @click="showPage('News article')"
                                    class="text-left transition hover:underline">
                                    {{ $article['title'] }}
                                </button>
                            </h3>
                            <p class="mt-2 flex-1 text-sm leading-relaxed text-slate-600">
                                Brief summary of the announcement, with a short preview of the article content
                                published for the public record.
                            </p>
                            <button type="button" @click="showPage('News article')"
                                class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-slate-900 transition hover:text-slate-700">
                                Read more
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Contact block                                                   --}}
    {{-- ============================================================ --}}
    <section class="bg-slate-50 py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-3 lg:gap-16">
                <div class="lg:col-span-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-600">Contact</p>
                    <h2 class="mt-3 font-serif text-3xl font-semibold text-slate-900 sm:text-4xl">
                        Get in touch
                    </h2>
                    <div class="mt-3 inline-block h-1 w-12 bg-amber-400"></div>
                    <p class="mt-6 text-base leading-relaxed text-slate-600">
                        Reach out to the appropriate office for general inquiries, public records requests, or media
                        questions.
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2 lg:col-span-2">
                    @foreach ([
                        ['title' => 'Mailing Address', 'lines' => ['Office Name', '000 Street Address', 'Tallahassee, FL 32399']],
                        ['title' => 'Phone', 'lines' => ['(000) 000-0000', 'Mon–Fri, 8:00 AM – 5:00 PM ET']],
                        ['title' => 'Email', 'lines' => ['public.info@example.fl.gov', 'Response within 2 business days']],
                        ['title' => 'Public Records', 'lines' => ['Submit a public records request', 'in accordance with state law.']],
                    ] as $card)
                        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full bg-amber-400"></span>
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-900">
                                    {{ $card['title'] }}
                                </h3>
                            </div>
                            <div class="mt-4 space-y-1 text-sm leading-relaxed text-slate-600">
                                @foreach ($card['lines'] as $line)
                                    <p>{{ $line }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Mock state footer                                               --}}
    {{-- ============================================================ --}}
    <footer class="relative overflow-hidden bg-slate-900 text-slate-300">
        {{-- Florida outline accent in footer --}}
        <div class="pointer-events-none absolute -bottom-12 -right-12 opacity-[0.05]" aria-hidden="true">
            <svg class="h-72 w-auto text-white" viewBox="0 0 500 360" fill="currentColor">
                <path d="M 20 60 L 250 55 L 290 60 L 330 70 L 365 85 L 395 105 L 415 130 L 425 165 L 425 200 L 415 240 L 395 280 L 365 310 L 330 325 L 305 320 L 290 300 L 280 270 L 270 235 L 255 200 L 235 175 L 210 158 L 175 145 L 140 135 L 105 122 L 75 105 L 50 90 L 30 75 Z" />
            </svg>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-4">
                {{-- Brand --}}
                <div>
                    <p class="font-serif text-xl font-semibold text-white">
                        State of Florida<span class="ml-1 inline-block h-1 w-6 align-middle bg-amber-400"></span>
                    </p>
                    <p class="mt-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                        Official Public Information Website
                    </p>
                    <p class="mt-6 text-sm leading-relaxed text-slate-400">
                        Information published by the State of Florida for residents, businesses, and the press.
                    </p>
                </div>

                {{-- Footer link columns --}}
                @php
                    $footerColumns = [
                        ['heading' => 'Navigate', 'links' => ['Home', 'About', 'Programs', 'Resources', 'News', 'Contact']],
                        ['heading' => 'Public Records', 'links' => ['Records request', 'Open data', 'Notices', 'Public meetings']],
                        ['heading' => 'Accessibility', 'links' => ['Accessibility statement', 'Site policies', 'Privacy', 'Translate (Español)']],
                    ];
                @endphp

                @foreach ($footerColumns as $column)
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-white">{{ $column['heading'] }}</h3>
                        <ul class="mt-4 space-y-3">
                            @foreach ($column['links'] as $link)
                                <li>
                                    <a href="#" @click.prevent="showPage('{{ $link }}')"
                                        class="text-sm text-slate-400 transition hover:text-white">
                                        {{ $link }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="mt-14 border-t border-slate-800 pt-8">
                <div class="flex flex-col gap-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} State of Florida. All rights reserved.</p>
                    <p class="max-w-xl text-slate-500">
                        Demonstration site prepared by eRegister. Not affiliated with the State of Florida or the
                        Executive Office of the Governor.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    {{-- ============================================================ --}}
    {{-- Modal overlay                                                   --}}
    {{-- ============================================================ --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
        @click.self="modalOpen = false" role="dialog" aria-modal="true" aria-labelledby="demo-modal-title">
        {{-- Backdrop --}}
        <div x-show="modalOpen" x-cloak x-transition.opacity class="absolute inset-0 bg-slate-900/70"></div>

        {{-- Panel --}}
        <div x-show="modalOpen" x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="relative w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="border-b-4 border-amber-400 bg-slate-900 px-6 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-amber-400">Demo Preview</p>
                <h2 id="demo-modal-title" class="mt-1 font-serif text-xl font-semibold text-white">
                    <span x-text="modalTarget"></span> page
                </h2>
            </div>
            <div class="px-6 py-6">
                <p class="text-sm leading-relaxed text-slate-600">
                    Only the homepage was built for this preview. In a full engagement, the
                    <span class="font-semibold text-slate-900" x-text="modalTarget"></span>
                    page would be designed and built per the <strong class="text-slate-900">EOG–RFQ–26-03</strong>
                    scope of services.
                </p>
                <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
                    <a href="{{ route('government.home') }}"
                        class="text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                        Exit demo
                    </a>
                    <button type="button" @click="modalOpen = false"
                        class="inline-flex items-center justify-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Back to homepage
                    </button>
                </div>
            </div>
        </div>
    </div>

    @fluxScripts
</body>

</html>
