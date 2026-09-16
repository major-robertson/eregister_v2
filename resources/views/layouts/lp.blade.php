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
    <header class="border-b border-zinc-200 bg-white">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center">
                <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" class="h-9" />
            </a>
            <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                Log in
            </a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-zinc-200 bg-zinc-50">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-zinc-500 sm:flex-row sm:px-6 lg:px-8">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'eRegister') }}. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="{{ route('privacy-policy') }}" class="transition hover:text-zinc-900">Privacy</a>
                <a href="{{ route('terms-of-service') }}" class="transition hover:text-zinc-900">Terms</a>
                <a href="{{ route('contact') }}" class="transition hover:text-zinc-900">Contact</a>
            </div>
        </div>
    </footer>

    @fluxScripts
</body>

</html>
