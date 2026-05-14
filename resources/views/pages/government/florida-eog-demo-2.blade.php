<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => 'State of Florida — Public Information Website (Demo 2)'])
    <meta name="robots" content="noindex, nofollow" />
    <style>
        [x-cloak] { display: none !important; }

        /* Subtle radial highlight used behind hero & section dividers */
        .gov2-radial {
            background: radial-gradient(ellipse at top, rgba(255, 255, 255, 0.06), rgba(255, 255, 255, 0) 60%);
        }
    </style>
</head>

{{--
    DEMO 2 — Same content as Demo 1, restyled with the official .gov
    color palette referenced by the client (dark navy / gold / red).
    Brand tokens used here:
      - Deep navy   : #0e1f3d   (header, hero, section base)
      - Lighter navy: #15294f   (cards, hovers)
      - Darkest navy: #070f24   (footer)
      - Gold accent : amber-400
      - Red accent  : red-700
--}}
<body class="min-h-screen bg-[#0e1f3d] font-sans text-white antialiased"
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
    <div class="bg-black text-slate-200">
        <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-2 px-4 py-2 text-xs sm:flex-row sm:items-center sm:px-6 lg:px-8">
            <div class="flex items-center gap-2">
                <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                <span>
                    Demo prepared for <strong class="font-semibold text-white">EOG–RFQ–26-03</strong> by eRegister
                    <span class="ml-1 text-slate-500">·</span>
                    <span class="ml-1 font-semibold text-amber-400">Demo 2</span>
                </span>
            </div>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <a href="{{ route('government.florida-eog-demo-1') }}"
                    class="inline-flex items-center gap-1 text-slate-300 transition hover:text-white">
                    View Demo 1
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <a href="{{ route('government.home') }}"
                    class="inline-flex items-center gap-1 text-slate-300 transition hover:text-white">
                    Exit demo
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- Mock State of Florida header (NO seal, NO EOG logo)            --}}
    {{-- ============================================================ --}}
    <header class="relative bg-[#0e1f3d]">
        <div class="gov2-radial absolute inset-0 pointer-events-none"></div>

        <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-6 py-5">
                {{-- Wordmark --}}
                <a href="#" @click.prevent="showPage('Home')" class="block text-white">
                    <p class="font-serif text-2xl font-semibold">
                        State of Florida<span class="ml-1 inline-block h-1 w-8 align-middle bg-amber-400"></span>
                    </p>
                    <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-300">
                        Official Public Information Website
                    </p>
                </a>

                {{-- Right utility (.gov style flag + socials) --}}
                <div class="hidden items-center gap-5 text-[11px] uppercase tracking-wider text-slate-200 lg:flex">
                    <div class="flex items-center gap-2">
                        <svg class="h-3 w-5" viewBox="0 0 20 12" fill="none" aria-hidden="true">
                            <rect width="20" height="12" fill="#dc2626" />
                            <rect width="20" height="2" y="0" fill="#fff" />
                            <rect width="20" height="2" y="4" fill="#fff" />
                            <rect width="20" height="2" y="8" fill="#fff" />
                            <rect width="9" height="6" fill="#1e3a8a" />
                        </svg>
                        <span class="text-slate-400">Flag Status</span>
                        <span class="font-semibold text-white">Full Staff</span>
                    </div>
                    <span class="h-3 w-px bg-slate-600"></span>
                    <button type="button" @click="showPage('Social')" aria-label="Facebook"
                        class="text-slate-300 transition hover:text-white">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 12.06C22 6.49 17.52 2 12 2S2 6.49 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.83c0-2.51 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.45 2.91h-2.33V22c4.78-.76 8.44-4.92 8.44-9.94z" />
                        </svg>
                    </button>
                    <button type="button" @click="showPage('Social')" aria-label="X"
                        class="text-slate-300 transition hover:text-white">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2H21l-6.52 7.45L22 22h-6.84l-4.79-6.28L4.8 22H2l7-7.99L1.6 2h6.99l4.34 5.74L18.244 2zm-1.2 18h1.65L7.05 4H5.3l11.744 16z" />
                        </svg>
                    </button>
                </div>

                {{-- Mobile toggle --}}
                <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-slate-200 transition hover:bg-white/10 lg:hidden">
                    <span class="sr-only">Open menu</span>
                    <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Right-aligned primary nav (matches Demo 1) --}}
            <nav class="hidden items-center justify-end gap-1 border-t border-white/10 pb-3 pt-3 lg:flex">
                @php
                    $nav2 = [
                        ['label' => 'Home', 'active' => true],
                        ['label' => 'About', 'active' => false],
                        ['label' => 'Programs', 'active' => false],
                        ['label' => 'Resources', 'active' => false],
                        ['label' => 'News', 'active' => false],
                        ['label' => 'Contact', 'active' => false],
                    ];
                @endphp
                @foreach ($nav2 as $item)
                    <a href="#" @click.prevent="showPage('{{ $item['label'] }}')"
                        class="relative rounded-md px-3 py-2 text-sm font-medium transition {{ $item['active'] ? 'text-white' : 'text-slate-300 hover:text-white' }}">
                        {{ $item['label'] }}
                        @if ($item['active'])
                            <span class="absolute -bottom-0.5 left-3 right-3 h-0.5 bg-amber-400"></span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileMenuOpen" x-cloak x-transition.opacity
            class="border-t border-white/10 bg-[#0a182f] lg:hidden">
            <nav class="mx-auto flex max-w-7xl flex-col gap-1 px-4 py-3 sm:px-6">
                @foreach (['Home', 'About', 'Programs', 'Resources', 'News', 'Contact'] as $label)
                    <a href="#" @click.prevent="showPage('{{ $label }}')"
                        class="rounded-md px-3 py-2 text-sm font-semibold text-slate-100 transition hover:bg-white/5 hover:text-amber-400">
                        {{ $label }}
                    </a>
                @endforeach
                <div class="mt-2 flex items-center gap-2 border-t border-white/10 px-3 pt-3 text-[11px] uppercase tracking-wider text-slate-300">
                    <svg class="h-3 w-5" viewBox="0 0 20 12" fill="none" aria-hidden="true">
                        <rect width="20" height="12" fill="#dc2626" />
                        <rect width="20" height="2" y="0" fill="#fff" />
                        <rect width="20" height="2" y="4" fill="#fff" />
                        <rect width="20" height="2" y="8" fill="#fff" />
                        <rect width="9" height="6" fill="#1e3a8a" />
                    </svg>
                    <span class="text-slate-400">Flag Status</span>
                    <span class="font-semibold text-white">Full Staff</span>
                </div>
            </nav>
        </div>
    </header>

    {{-- ============================================================ --}}
    {{-- Hero — split two-card layout with photo placeholders          --}}
    {{-- (Same headline + CTAs as Demo 1, restyled for dark theme.)    --}}
    {{-- ============================================================ --}}
    <section class="relative bg-[#0e1f3d]">
        <div class="grid grid-cols-1 lg:grid-cols-5">
            {{-- Left card (primary hero, larger) --}}
            <div class="relative col-span-1 overflow-hidden lg:col-span-3 lg:min-h-[520px]">
                {{-- Photo placeholder: upload to public/img/demos/florida-eog/hero-primary.jpg --}}
                <img src="/img/demos/florida-eog/hero-primary.jpg"
                    alt="Florida coastline, capitol, or community photo"
                    class="absolute inset-0 h-full w-full bg-[#15294f] object-cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/55 to-black/30"></div>

                <div class="relative px-8 py-16 sm:px-12 sm:py-20 lg:px-16 lg:py-24">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-400">An official Florida website</p>
                    <h1 class="mt-4 max-w-2xl font-serif text-4xl font-semibold leading-tight text-white sm:text-5xl lg:text-6xl">
                        Official public information for Florida residents
                    </h1>
                    <p class="mt-6 max-w-xl text-base leading-relaxed text-slate-200 sm:text-lg">
                        This site provides residents, businesses, and stakeholders with timely, accurate, and accessible
                        information about state programs and public services. Find the resources you need, learn about
                        initiatives across the state, and stay informed.
                    </p>
                    <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:items-center">
                        <button type="button" @click="showPage('Programs')"
                            class="inline-flex items-center justify-center gap-2 rounded-sm bg-red-700 px-6 py-3 text-sm font-semibold text-white shadow-lg transition hover:bg-red-600">
                            Browse programs &amp; services
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </button>
                        <button type="button" @click="showPage('About')"
                            class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-amber-400 underline-offset-4 transition hover:text-amber-300 hover:underline">
                            About this site
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right card (secondary hero, narrower) --}}
            <button type="button" @click="showPage('About')"
                class="group relative col-span-1 block aspect-[5/4] w-full overflow-hidden text-left lg:col-span-2 lg:aspect-auto lg:min-h-[520px]">
                {{-- Photo placeholder: upload to public/img/demos/florida-eog/hero-secondary.jpg --}}
                <img src="/img/demos/florida-eog/hero-secondary.jpg"
                    alt="Florida community, residents, or landmark photo"
                    class="absolute inset-0 h-full w-full bg-[#15294f] object-cover" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent transition group-hover:from-black/85"></div>

                <div class="absolute bottom-0 left-0 right-0 p-8 sm:p-10 lg:p-12">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-400">About this site</p>
                    <h2 class="mt-3 font-serif text-3xl font-light leading-tight text-white sm:text-4xl">
                        Information from<br />the State of Florida
                    </h2>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-200">
                        Reliable details on programs, eligibility, and how to participate &mdash; reviewed and updated
                        by authorized staff.
                    </p>
                    <span class="mt-6 inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wider text-amber-400 transition group-hover:text-amber-300">
                        Learn more
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </span>
                </div>
            </button>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Quick links grid (same content as Demo 1, dark theme)         --}}
    {{-- ============================================================ --}}
    <section class="bg-[#0a182f] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-amber-400">Quick Links</p>
                <h2 class="mt-3 font-serif text-3xl font-semibold text-white sm:text-4xl">
                    Find what you need
                </h2>
                <p class="mt-4 text-base text-slate-300">
                    Direct access to the most-requested information, services, and updates from across the state.
                </p>
                <div class="mt-5 h-px w-16 bg-red-500"></div>
            </div>

            <div class="mt-12 grid gap-px overflow-hidden rounded-sm bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $quickLinks = [
                        ['title' => 'Programs &amp; Services', 'description' => 'Explore state-supported programs available to Florida residents and businesses.'],
                        ['title' => 'Public Resources', 'description' => 'Forms, guides, and reference materials published for public use.'],
                        ['title' => 'News &amp; Updates', 'description' => 'Recent announcements, press releases, and program updates.'],
                        ['title' => 'Contact &amp; Offices', 'description' => 'Office locations, contact directories, and constituent services.'],
                    ];
                @endphp

                @foreach ($quickLinks as $link)
                    <button type="button" @click="showPage('{{ strip_tags(html_entity_decode($link['title'])) }}')"
                        class="group flex flex-col items-start gap-3 bg-[#0e1f3d] p-7 text-left transition hover:bg-[#15294f]">
                        <span class="inline-block h-1 w-10 bg-amber-400 transition group-hover:w-16"></span>
                        <h3 class="font-serif text-lg font-semibold text-white">{!! $link['title'] !!}</h3>
                        <p class="text-sm leading-relaxed text-slate-300">{!! $link['description'] !!}</p>
                        <span class="mt-auto inline-flex items-center gap-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-amber-400 transition group-hover:text-amber-300">
                            Learn more
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- About preview (Demo 1 content + Demo 2 styling)                --}}
    {{-- ============================================================ --}}
    <section class="bg-[#0e1f3d] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                {{-- Copy --}}
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-amber-400">About This Site</p>
                    <h2 class="mt-3 font-serif text-3xl font-semibold text-white sm:text-4xl">
                        Information from the State of Florida
                    </h2>
                    <div class="mt-5 h-px w-16 bg-red-500"></div>
                    <p class="mt-6 text-base leading-relaxed text-slate-300">
                        This is the official public information website for the initiative. It is intended to provide
                        residents, community partners, and the press with reliable details on programs, eligibility,
                        and how to participate. All published materials follow state communication standards and
                        accessibility guidelines.
                    </p>
                    <p class="mt-4 text-base leading-relaxed text-slate-300">
                        Content is reviewed and updated by authorized staff. For media inquiries, agency contacts, or
                        constituent services, please use the contact information below.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <button type="button" @click="showPage('About')"
                            class="inline-flex items-center justify-center rounded-sm bg-red-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600">
                            Learn more
                        </button>
                        <button type="button" @click="showPage('Resources')"
                            class="inline-flex items-center justify-center rounded-sm border border-white/20 bg-transparent px-5 py-2.5 text-sm font-semibold text-white transition hover:border-amber-400 hover:text-amber-400">
                            View resources
                        </button>
                    </div>
                </div>

                {{-- Photo placeholder: upload to public/img/demos/florida-eog/about.jpg --}}
                <div class="aspect-[4/3] w-full overflow-hidden rounded-sm ring-1 ring-white/10">
                    <img src="/img/demos/florida-eog/about.jpg"
                        alt="Florida coastline, capitol, or community photo"
                        class="h-full w-full bg-[#15294f] object-cover" />
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Featured news red strip                                        --}}
    {{-- ============================================================ --}}
    <div class="bg-red-700">
        <div class="mx-auto max-w-7xl px-4 py-3 text-center sm:px-6 lg:px-8">
            <p class="text-[11px] font-semibold uppercase tracking-[0.5em] text-white sm:text-xs">
                Latest News
            </p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- News list (same content as Demo 1, lead + 2-up layout)         --}}
    {{-- ============================================================ --}}
    <section class="bg-[#0e1f3d] py-14 sm:py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-2xl">
                    <h2 class="font-serif text-3xl font-semibold text-white sm:text-4xl">
                        Updates &amp; announcements
                    </h2>
                </div>
                <button type="button" @click="showPage('News')"
                    class="inline-flex items-center gap-1 self-start text-xs font-semibold uppercase tracking-wider text-amber-400 transition hover:text-amber-300 sm:self-auto">
                    View all news
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </button>
            </div>

            @php
                $news = [
                    [
                        'date' => 'May 5, 2026',
                        'tag' => 'Announcement',
                        'title' => 'New public information resources available statewide',
                        'image' => '/img/demos/florida-eog/news-1.jpg',
                    ],
                    [
                        'date' => 'April 28, 2026',
                        'tag' => 'Program Update',
                        'title' => 'Updated program eligibility guidance for Florida residents',
                        'image' => '/img/demos/florida-eog/news-2.jpg',
                    ],
                    [
                        'date' => 'April 14, 2026',
                        'tag' => 'Notice',
                        'title' => 'Office hours and contact information for the upcoming season',
                        'image' => '/img/demos/florida-eog/news-3.jpg',
                    ],
                ];
            @endphp

            {{-- Lead story (first article) --}}
            <article class="mt-12 grid gap-8 border-b border-white/10 pb-12 lg:grid-cols-5 lg:gap-12">
                <button type="button" @click="showPage('News article')"
                    class="group relative block aspect-[16/10] w-full overflow-hidden rounded-sm lg:col-span-3">
                    <img src="{{ $news[0]['image'] }}"
                        alt="News article photo placeholder"
                        class="h-full w-full bg-[#15294f] object-cover transition group-hover:scale-[1.02]" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                </button>
                <div class="flex flex-col justify-center lg:col-span-2">
                    <div class="flex items-center gap-3 text-[11px] uppercase tracking-wider">
                        <span class="font-semibold text-amber-400">{{ $news[0]['tag'] }}</span>
                        <span class="text-slate-500">·</span>
                        <time class="text-slate-300">{{ $news[0]['date'] }}</time>
                    </div>
                    <h3 class="mt-4 font-serif text-2xl font-semibold leading-snug text-white sm:text-3xl">
                        <button type="button" @click="showPage('News article')"
                            class="text-left transition hover:text-amber-400">
                            {{ $news[0]['title'] }}
                        </button>
                    </h3>
                    <p class="mt-4 text-sm leading-relaxed text-slate-300">
                        Brief summary of the announcement, with a short preview of the article content published for
                        the public record.
                    </p>
                    <button type="button" @click="showPage('News article')"
                        class="mt-6 inline-flex items-center gap-1 self-start text-xs font-semibold uppercase tracking-wider text-amber-400 transition hover:text-amber-300">
                        Read more
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </button>
                </div>
            </article>

            {{-- Secondary stories (remaining articles) --}}
            <div class="mt-12 grid gap-10 sm:grid-cols-2">
                @foreach (array_slice($news, 1) as $article)
                    <article class="group">
                        <button type="button" @click="showPage('News article')"
                            class="relative block aspect-[16/9] w-full overflow-hidden rounded-sm">
                            <img src="{{ $article['image'] }}"
                                alt="News article photo placeholder"
                                class="h-full w-full bg-[#15294f] object-cover transition group-hover:scale-[1.02]" />
                            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                        </button>
                        <div class="mt-5 flex items-center gap-3 text-[11px] uppercase tracking-wider">
                            <span class="font-semibold text-amber-400">{{ $article['tag'] }}</span>
                            <span class="text-slate-500">·</span>
                            <time class="text-slate-300">{{ $article['date'] }}</time>
                        </div>
                        <h3 class="mt-3 font-serif text-xl font-semibold leading-snug text-white">
                            <button type="button" @click="showPage('News article')"
                                class="text-left transition hover:text-amber-400">
                                {{ $article['title'] }}
                            </button>
                        </h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-300">
                            Brief summary of the announcement, with a short preview of the article content published
                            for the public record.
                        </p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================ --}}
    {{-- Contact block (same content as Demo 1, dark theme)             --}}
    {{-- ============================================================ --}}
    <section class="bg-[#0a182f] py-20">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 lg:grid-cols-3 lg:gap-16">
                <div class="lg:col-span-1">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-amber-400">Contact</p>
                    <h2 class="mt-3 font-serif text-3xl font-semibold text-white sm:text-4xl">
                        Get in touch
                    </h2>
                    <div class="mt-3 inline-block h-1 w-12 bg-red-500"></div>
                    <p class="mt-6 text-base leading-relaxed text-slate-300">
                        Reach out to the appropriate office for general inquiries, public records requests, or media
                        questions.
                    </p>
                </div>

                <div class="grid gap-px overflow-hidden rounded-sm bg-white/10 sm:grid-cols-2 lg:col-span-2">
                    @foreach ([
                        ['title' => 'Mailing Address', 'lines' => ['Office Name', '000 Street Address', 'Tallahassee, FL 32399']],
                        ['title' => 'Phone', 'lines' => ['(000) 000-0000', 'Mon–Fri, 8:00 AM – 5:00 PM ET']],
                        ['title' => 'Email', 'lines' => ['public.info@example.fl.gov', 'Response within 2 business days']],
                        ['title' => 'Public Records', 'lines' => ['Submit a public records request', 'in accordance with state law.']],
                    ] as $card)
                        <div class="bg-[#0e1f3d] p-6">
                            <div class="flex items-center gap-2">
                                <span class="inline-block h-2 w-2 rounded-full bg-amber-400"></span>
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">
                                    {{ $card['title'] }}
                                </h3>
                            </div>
                            <div class="mt-4 space-y-1 text-sm leading-relaxed text-slate-300">
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
    {{-- Mock state footer (NO seal SVG)                                 --}}
    {{-- ============================================================ --}}
    <footer class="relative overflow-hidden bg-[#070f24] text-slate-300">
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
                    <div class="mt-6 flex items-center gap-4 text-slate-400">
                        <button type="button" @click="showPage('Social')" aria-label="Facebook"
                            class="transition hover:text-white">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12.06C22 6.49 17.52 2 12 2S2 6.49 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.83c0-2.51 1.49-3.89 3.77-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.45 2.91h-2.33V22c4.78-.76 8.44-4.92 8.44-9.94z" />
                            </svg>
                        </button>
                        <button type="button" @click="showPage('Social')" aria-label="X"
                            class="transition hover:text-white">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2H21l-6.52 7.45L22 22h-6.84l-4.79-6.28L4.8 22H2l7-7.99L1.6 2h6.99l4.34 5.74L18.244 2zm-1.2 18h1.65L7.05 4H5.3l11.744 16z" />
                            </svg>
                        </button>
                    </div>
                </div>

                @php
                    $footerColumns = [
                        ['heading' => 'Navigate', 'links' => ['Home', 'About', 'Programs', 'Resources', 'News', 'Contact']],
                        ['heading' => 'Public Records', 'links' => ['Records request', 'Open data', 'Notices', 'Public meetings']],
                        ['heading' => 'Accessibility', 'links' => ['Accessibility statement', 'Site policies', 'Privacy', 'Translate (Español)']],
                    ];
                @endphp

                @foreach ($footerColumns as $column)
                    <div>
                        <h3 class="text-[11px] font-semibold uppercase tracking-[0.25em] text-amber-400">
                            {{ $column['heading'] }}
                        </h3>
                        <ul class="mt-5 space-y-3">
                            @foreach ($column['links'] as $link)
                                <li>
                                    <a href="#" @click.prevent="showPage('{{ $link }}')"
                                        class="text-sm text-slate-300 transition hover:text-white">
                                        {{ $link }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="mt-14 border-t border-white/10 pt-8">
                <div class="flex flex-col gap-4 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                    <p>&copy; {{ date('Y') }} Demonstration website &mdash; sample content for evaluation only.</p>
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
    {{-- (Click backdrop or press Esc to close.)                        --}}
    {{-- ============================================================ --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
        role="dialog" aria-modal="true" aria-labelledby="demo-modal-title-2">
        {{-- Backdrop (clickable to close) --}}
        <div x-show="modalOpen" x-cloak x-transition.opacity
            @click="modalOpen = false"
            class="absolute inset-0 bg-black/75"></div>

        <div x-show="modalOpen" x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="relative w-full max-w-md overflow-hidden rounded-sm bg-white text-slate-900 shadow-2xl">
            <div class="border-b-4 border-red-700 bg-[#0e1f3d] px-6 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.3em] text-amber-400">Demo Preview</p>
                <h2 id="demo-modal-title-2" class="mt-1 font-serif text-xl font-semibold text-white">
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
                <div class="mt-6 flex items-center justify-end">
                    <button type="button" @click="modalOpen = false"
                        class="inline-flex items-center justify-center rounded-sm bg-[#0e1f3d] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#15294f]">
                        Back to homepage
                    </button>
                </div>
            </div>
        </div>
    </div>

    @fluxScripts
</body>

</html>
