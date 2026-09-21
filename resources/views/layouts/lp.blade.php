<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => html_entity_decode($__env->yieldContent('title', config('app.name', 'eRegister')), ENT_QUOTES)])
    {{-- Paid-traffic pages: one job, one call to action, and no search
         footprint — the SEO pages carry the same content with full chrome. --}}
    <meta name="robots" content="noindex, nofollow">
    @yield('meta')
</head>

<body class="min-h-screen bg-white antialiased">
    {{-- A slim menu, not the full site header. Google shows fewer ads for
         pages a visitor can't navigate (its Feb 2025 landing page model), so
         the page links to its own sections and the few pages that answer
         "who is this?". The page supplies the links in the "nav" section. --}}
    <header class="relative border-b border-zinc-200 bg-white" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center">
                <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" class="h-9" />
            </a>

            @hasSection('nav')
                <nav aria-label="Main" class="hidden items-center gap-7 text-sm font-medium text-zinc-600 md:flex">
                    @yield('nav')
                </nav>
            @endif

            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="px-2 text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                    Log in
                </a>

                @hasSection('nav')
                    <button @click="menuOpen = !menuOpen" type="button" :aria-expanded="menuOpen.toString()" aria-controls="lp-menu"
                        class="inline-flex items-center justify-center rounded-md p-2 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 md:hidden">
                        <span class="sr-only">Open menu</span>
                        <svg x-show="!menuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="menuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                @endif
            </div>
        </div>

        @hasSection('nav')
            <nav id="lp-menu" aria-label="Main" x-show="menuOpen" @click="menuOpen = false" @click.outside="menuOpen = false"
                class="absolute inset-x-0 top-full z-30 flex flex-col gap-1 border-b border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-600 shadow-lg md:hidden"
                style="display: none;">
                @yield('nav')
            </nav>
        @endif
    </header>

    <main>
        @yield('content')
    </main>

    {{-- Extra room at the bottom on phones so a page's fixed button bar never covers the links. --}}
    <footer class="border-t border-zinc-200 bg-zinc-50 pb-24 sm:pb-0">
        <div class="mx-auto max-w-6xl px-4 py-10 text-sm text-zinc-500 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-8 md:flex-row md:justify-between">
                <div>
                    <p class="font-semibold text-zinc-900">{{ config('app.name', 'eRegister') }}</p>
                    @if (filled(config('mail.postal_address')))
                        <p class="mt-1">{{ config('mail.postal_address') }}</p>
                    @endif
                </div>
                <nav aria-label="Footer" class="flex flex-wrap gap-x-6 gap-y-2">
                    @yield('footer_links')
                    <a href="{{ route('contact') }}" class="transition hover:text-zinc-900">Contact</a>
                    <a href="{{ route('privacy-policy') }}" class="transition hover:text-zinc-900">Privacy</a>
                    <a href="{{ route('terms-of-service') }}" class="transition hover:text-zinc-900">Terms</a>
                </nav>
            </div>
            <p class="mt-8">&copy; {{ date('Y') }} {{ config('app.name', 'eRegister') }}. All rights reserved.</p>
        </div>
    </footer>

    @fluxScripts
</body>

</html>
