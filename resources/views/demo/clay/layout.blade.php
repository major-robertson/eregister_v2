<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? 'Clay County Parks, Recreation & Historic Sites — Concept Demo' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Concept demo for the new Clay County Parks, Recreation & Historic Sites website — a virtual welcome center for Smithville Lake, trails, parks, and historic sites.' }}">
    <meta name="robots" content="noindex, nofollow" />

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Clay County Parks, Recreation & Historic Sites (Concept Demo)">
    <meta property="og:title" content="{{ $title ?? 'Clay County Parks, Recreation & Historic Sites — Concept Demo' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Concept demo for the new Clay County Parks, Recreation & Historic Sites website.' }}">
    <meta property="og:image" content="{{ asset('img/demos/clay-county/'.($ogImage ?? 'hero-kayaks-smithville.webp')) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" href="{{ asset('img/demos/clay-county/seal-clay-county.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=public-sans:400,600,700,800|source-serif-4:500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/demo/clay-county.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('head')
</head>

<body class="cc-demo min-h-screen"
    x-data="ccApp"
    @keydown.escape.window="closeAll()">

    <a href="#main" class="cc-skip-link">Skip to main content</a>

    {{-- eRegister demo banner (matches prior gov demo convention) --}}
    <div class="bg-slate-900 text-slate-100">
        <div class="mx-auto flex max-w-[1440px] flex-col items-start justify-between gap-1 px-4 py-2 text-xs sm:flex-row sm:items-center md:px-8 xl:px-12">
            <div class="flex items-center gap-2">
                <span class="inline-block h-1.5 w-1.5 flex-none rounded-full bg-amber-400"></span>
                <span>Concept demo for <strong class="font-semibold text-white">Clay County RFP 78-26</strong> by eRegister — front-end prototype, sample data only</span>
            </div>
            <a href="{{ route('government.home') }}" class="inline-flex items-center gap-1 font-medium text-slate-300 transition hover:text-white">
                Exit demo
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </div>

    @include('demo.clay.partials.header')

    @include('demo.clay.partials.alert-banner')

    <main id="main">
        @yield('content')
    </main>

    @include('demo.clay.partials.footer')

    @include('demo.clay.partials.overlays')

    <script>
        document.addEventListener('alpine:init', () => {
            // Minimal focus trap for the demo's modals and drawers (the bundled
            // Alpine build has no @alpinejs/focus): traps Tab, locks scroll,
            // autofocuses, and restores focus to the trigger on close.
            Alpine.directive('cc-trap', (el, { expression }, { effect, evaluateLater, cleanup }) => {
                const isOpen = evaluateLater(expression);
                let lastFocused = null;
                let active = false;

                const focusables = () => [...el.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])')]
                    .filter((f) => f.offsetParent !== null);

                const onKeydown = (e) => {
                    if (e.key !== 'Tab') return;
                    const items = focusables();
                    if (! items.length) return;
                    const first = items[0];
                    const last = items[items.length - 1];
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (! e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                };

                effect(() => isOpen((open) => {
                    if (open && ! active) {
                        active = true;
                        lastFocused = document.activeElement;
                        document.body.style.overflow = 'hidden';
                        el.addEventListener('keydown', onKeydown);
                        setTimeout(() => (el.querySelector('[data-autofocus]') || focusables()[0])?.focus(), 260);
                    } else if (! open && active) {
                        active = false;
                        document.body.style.overflow = '';
                        el.removeEventListener('keydown', onKeydown);
                        lastFocused?.focus?.();
                    }
                }));

                cleanup(() => {
                    document.body.style.overflow = '';
                    el.removeEventListener('keydown', onKeydown);
                });
            });

            Alpine.data('ccApp', () => ({
                // Global UI state
                mobileOpen: false,
                exploreOpen: false,
                searchOpen: false,
                alertsOpen: false,
                notifyOpen: false,
                notifySent: false,
                notifyTopics: ['Lake conditions'],
                webtracOpen: false,
                webtracContext: { name: 'Smithville Lake camping', detail: 'Campsites, shelters, and slips are reserved in WebTrac.' },

                // Search overlay
                query: '',
                activeIndex: 0,
                index: @js($searchIndex ?? []),

                get results() {
                    const q = this.query.trim().toLowerCase();
                    if (q.length < 2) return [];
                    return this.index
                        .filter((item) => item.keywords.toLowerCase().includes(q))
                        .slice(0, 8);
                },

                closeAll() {
                    this.mobileOpen = false;
                    this.exploreOpen = false;
                    this.searchOpen = false;
                    this.alertsOpen = false;
                    this.notifyOpen = false;
                    this.webtracOpen = false;
                },

                openSearch() {
                    this.closeAll();
                    this.searchOpen = true;
                    this.query = '';
                    this.activeIndex = 0;
                    // Focus the input after the open transition (and x-trap's own focus pass).
                    setTimeout(() => document.getElementById('cc-search-input')?.focus(), 220);
                },

                openNotify() {
                    this.closeAll();
                    this.notifySent = false;
                    this.notifyOpen = true;
                },

                openWebtrac(name = null, detail = null) {
                    this.closeAll();
                    if (name) this.webtracContext = { name, detail };
                    this.webtracOpen = true;
                },

                toggleTopic(topic) {
                    this.notifyTopics = this.notifyTopics.includes(topic)
                        ? this.notifyTopics.filter((t) => t !== topic)
                        : [...this.notifyTopics, topic];
                },

                searchMove(step) {
                    if (! this.results.length) return;
                    this.activeIndex = (this.activeIndex + step + this.results.length) % this.results.length;
                },

                searchGo() {
                    const hit = this.results[this.activeIndex];
                    if (hit) window.location.href = hit.url;
                },
            }));
        });
    </script>

    @fluxScripts
    @stack('scripts')
</body>

</html>
