<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>{{ $title ?? 'Pitt Community College — Redesign Concept (Demo)' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Concept demo for the Pitt Community College website redesign — audience-first navigation, program discovery, and clear next steps for every kind of student.' }}">
    <meta name="robots" content="noindex, nofollow" />

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Pitt Community College (Concept Demo)">
    <meta property="og:title" content="{{ $title ?? 'Pitt Community College — Redesign Concept (Demo)' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Concept demo for the Pitt Community College website redesign.' }}">
    <meta property="og:image" content="{{ asset('img/demos/pcc/'.($ogImage ?? 'hero-bruisers-crew.jpg')) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" type="image/png" href="{{ asset('img/demos/pcc/favicon-pcc.png') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=archivo:600,700,800,900|source-sans-3:400,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/css/demo/pcc.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @stack('head')
</head>

<body class="pcc-demo min-h-screen"
    x-data="pccApp"
    @keydown.escape.window="closeAll()">

    <a href="#main" class="pcc-skip-link">Skip to main content</a>

    @include('demo.shared.banner', ['demoLabel' => 'Pitt Community College RFP 115-6181'])

    @include('demo.pcc.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('demo.pcc.partials.footer')

    @include('demo.pcc.partials.stub-modal')

    <script>
        document.addEventListener('alpine:init', () => {
            // Minimal focus trap for the demo's modal and mobile menu (the
            // bundled Alpine build has no @alpinejs/focus): traps Tab, locks
            // scroll, autofocuses, and restores focus to the trigger on close.
            Alpine.directive('pcc-trap', (el, { expression }, { effect, evaluateLater, cleanup }) => {
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

            Alpine.data('pccApp', () => ({
                mobileOpen: false,

                // Name of the not-yet-built destination the visitor clicked,
                // shown in the "Demo preview" modal. Null = closed.
                stub: null,

                openStub(name) {
                    this.stub = name;
                    this.mobileOpen = false;
                },

                closeAll() {
                    this.mobileOpen = false;
                    this.stub = null;
                },
            }));
        });
    </script>

    @fluxScripts
    @stack('scripts')
</body>

</html>
