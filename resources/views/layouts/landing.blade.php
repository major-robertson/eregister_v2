<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    {{-- Inline @section('title', ...) values arrive already escaped; decode so the head partial's {{ }} does not double-escape "&". --}}
    {{-- Marketing pages run the Alpine-only marketing.js bundle. A page that renders a
         Livewire component sets @section('livewire', true) to get Livewire and Flux instead
         (their Livewire build ships its own Alpine, so never load both). --}}
    @php($usesLivewire = $__env->hasSection('livewire'))
    @include('partials.head', [
        'title' => html_entity_decode($__env->yieldContent('title', config('app.name', 'eRegister')), ENT_QUOTES),
        'headScript' => $usesLivewire ? 'resources/js/app.js' : 'resources/js/marketing.js',
    ])
    @include('partials.seo')
    @yield('meta')
</head>

<body class="min-h-screen bg-white antialiased">
    <!-- Header -->
    <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/80 backdrop-blur-sm"
        x-data="{ mobileMenuOpen: false }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" width="1538" height="520" class="h-9 w-auto" />
                </a>

                <!-- Main Navigation (Desktop) -->
                <nav class="hidden items-center gap-1 lg:flex">
                    {{-- Form a Business Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 {{ request()->routeIs('llc', 'corporation', 'dba', 'nonprofit', 'sole-proprietorship', 'registered-agent', 'annual-reports', 'ein-tax-id', 'operating-agreement') ? 'text-zinc-900' : '' }}">
                            Form a Business
                            <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-1/2 top-full z-50 mt-1 w-[32rem] -translate-x-1/2 rounded-xl border border-zinc-200 bg-white p-5 shadow-xl" style="display: none;">
                            <div class="grid grid-cols-2 gap-6">
                                {{-- Column 1: Register Your Business --}}
                                <div>
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Register Your Business</p>
                                    <div class="space-y-1">
                                        <a href="{{ route('llc') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Limited Liability Company (LLC)
                                        </a>
                                        <a href="{{ route('corporation') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Corporation (C Corp, S Corp)
                                        </a>
                                        <a href="{{ route('dba') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Doing Business As (DBA)
                                        </a>
                                        <a href="{{ route('nonprofit') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Nonprofit
                                        </a>
                                        <a href="{{ route('sole-proprietorship') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Sole Proprietorship
                                        </a>
                                    </div>
                                </div>
                                {{-- Column 2: Run Your Business --}}
                                <div>
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Run Your Business</p>
                                    <div class="space-y-1">
                                        <a href="{{ route('registered-agent') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Registered Agent
                                        </a>
                                        <a href="{{ route('annual-reports') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Annual Reports
                                        </a>
                                        <a href="{{ route('ein-tax-id') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            EIN / Tax ID
                                        </a>
                                        <a href="{{ route('operating-agreement') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                            Operating Agreement
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Compliance & Tax Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 {{ request()->routeIs('sales-tax-registration', 'resale-certificates') ? 'text-zinc-900' : '' }}">
                            Compliance & Tax
                            <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-1/2 top-full z-50 mt-1 w-64 -translate-x-1/2 rounded-xl border border-zinc-200 bg-white p-3 shadow-xl" style="display: none;">
                            <div class="space-y-1">
                                <a href="{{ route('sales-tax-registration') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Sales & Use Tax Registration
                                </a>
                                <a href="{{ route('resale-certificates') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Resale Certificates
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Protection Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 {{ request()->routeIs('liens', 'liens.*') ? 'text-zinc-900' : '' }}">
                            Payment Protection
                            <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-1/2 top-full z-50 mt-1 w-72 -translate-x-1/2 rounded-xl border border-zinc-200 bg-white p-3 shadow-xl" style="display: none;">
                            <div>
                                <p class="px-3 pb-1 pt-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Track &amp; File</p>
                                <a href="{{ route('liens') }}#tracking" class="flex items-center justify-between rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Lien Tracking Portal
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Free</span>
                                </a>
                                <a href="{{ route('liens') }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">Mechanics / Construction Lien</a>
                                <a href="{{ route('liens.preliminary-notice') }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">Preliminary Notice</a>
                                <a href="{{ route('liens.notice-of-intent-to-lien') }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">Notice of Intent to Lien</a>
                                <a href="{{ route('liens.lien-release') }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">Lien Release</a>
                                <a href="{{ route('liens.payment-demand-letter') }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">Payment Demand Letter</a>
                                <a href="{{ route('liens.lien-waivers') }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">Lien Waiver Generator</a>

                                <div class="mt-2 border-t border-zinc-100 pt-1">
                                    <a href="{{ route('liens.pricing') }}" class="block rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">Pricing</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Government (flat link) --}}
                    <a href="{{ route('government.home') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 {{ request()->routeIs('government.*') ? 'text-zinc-900' : '' }}">
                        Government
                    </a>

                    {{-- Contact (flat link) --}}
                    <a href="{{ route('contact') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-50 hover:text-zinc-900 {{ request()->routeIs('contact') ? 'text-zinc-900' : '' }}">
                        Contact
                    </a>
                </nav>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-4">
                    <nav class="hidden items-center gap-4 lg:flex">
                        @auth
                        <a href="{{ auth()->user()->roles->isNotEmpty() ? route('admin.home') : url('/portal') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                                Log out
                            </button>
                        </form>
                        @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                            Log in
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800">
                            Sign up
                        </a>
                        @endif
                        @endauth
                    </nav>

                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 lg:hidden">
                        <span class="sr-only">Open menu</span>
                        <svg x-show="!mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" class="lg:hidden" style="display: none;">
            <div class="max-h-[80vh] overflow-y-auto border-t border-zinc-200 bg-white px-4 pb-4 pt-2">
                <nav class="flex flex-col gap-1">
                    {{-- Form a Business --}}
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 [&::-webkit-details-marker]:hidden">
                            Form a Business
                            <svg class="h-4 w-4 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="ml-3 mt-1 space-y-1 border-l-2 border-zinc-100 pl-3">
                            <p class="px-3 pt-2 text-xs font-semibold uppercase tracking-wider text-zinc-400">Register</p>
                            <a href="{{ route('llc') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">LLC</a>
                            <a href="{{ route('corporation') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Corporation (C Corp, S Corp)</a>
                            <a href="{{ route('dba') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">DBA</a>
                            <a href="{{ route('nonprofit') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Nonprofit</a>
                            <a href="{{ route('sole-proprietorship') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Sole Proprietorship</a>
                            <p class="px-3 pt-3 text-xs font-semibold uppercase tracking-wider text-zinc-400">Run Your Business</p>
                            <a href="{{ route('registered-agent') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Registered Agent</a>
                            <a href="{{ route('annual-reports') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Annual Reports</a>
                            <a href="{{ route('ein-tax-id') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">EIN / Tax ID</a>
                            <a href="{{ route('operating-agreement') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Operating Agreement</a>
                        </div>
                    </details>

                    {{-- Compliance & Tax --}}
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 [&::-webkit-details-marker]:hidden">
                            Compliance & Tax
                            <svg class="h-4 w-4 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="ml-3 mt-1 space-y-1 border-l-2 border-zinc-100 pl-3">
                            <a href="{{ route('sales-tax-registration') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Sales & Use Tax Registration</a>
                            <a href="{{ route('resale-certificates') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Resale Certificates</a>
                        </div>
                    </details>

                    {{-- Payment Protection --}}
                    <details class="group">
                        <summary class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 [&::-webkit-details-marker]:hidden">
                            Payment Protection
                            <svg class="h-4 w-4 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="ml-3 mt-1 border-l-2 border-zinc-100 pl-3">
                            <p class="px-3 pb-1 pt-1 text-[10px] font-semibold uppercase tracking-wide text-zinc-400">Track &amp; File</p>
                            <a href="{{ route('liens') }}#tracking" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                                Lien Tracking Portal
                                <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">Free</span>
                            </a>
                            <a href="{{ route('liens') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Mechanics / Construction Lien</a>
                            <a href="{{ route('liens.preliminary-notice') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Preliminary Notice</a>
                            <a href="{{ route('liens.notice-of-intent-to-lien') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Notice of Intent to Lien</a>
                            <a href="{{ route('liens.lien-release') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Lien Release</a>
                            <a href="{{ route('liens.payment-demand-letter') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Payment Demand Letter</a>
                            <a href="{{ route('liens.lien-waivers') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Lien Waiver Generator</a>

                            <a href="{{ route('liens.pricing') }}" class="mt-2 block rounded-lg border-t border-zinc-100 px-3 pb-1.5 pt-2 text-sm font-medium text-zinc-900 transition hover:bg-zinc-100">Pricing</a>
                        </div>
                    </details>

                    {{-- Government --}}
                    <a href="{{ route('government.home') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                        Government
                    </a>

                    {{-- Contact --}}
                    <a href="{{ route('contact') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 {{ request()->routeIs('contact') ? 'bg-zinc-100 text-zinc-900' : '' }}">
                        Contact
                    </a>

                    {{-- Auth --}}
                    <div class="mt-3 flex flex-col gap-2 border-t border-zinc-200 pt-3">
                        @auth
                        <a href="{{ auth()->user()->roles->isNotEmpty() ? route('admin.home') : url('/portal') }}"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-center text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full rounded-lg px-3 py-2 text-center text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                                Log out
                            </button>
                        </form>
                        @else
                        <a href="{{ route('login') }}"
                            class="rounded-lg px-3 py-2 text-center text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                            Log in
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-center text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800">
                            Sign up
                        </a>
                        @endif
                        @endauth
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 bg-zinc-900">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-5">
                {{-- Company Info --}}
                <div class="col-span-2 md:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <img src="/img/logo/eregister-logo-light-svg.svg" alt="eRegister" width="1538" height="520" class="h-10 w-auto" />
                    </a>
                    <p class="mt-4 text-sm text-zinc-400">
                        Business formation, compliance, and payment protection across all 50 states.
                    </p>
                </div>

                {{-- Form a Business --}}
                <div>
                    <h2 class="font-semibold text-white">Form a Business</h2>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('llc') }}" class="text-sm text-zinc-400 transition hover:text-white">LLC</a></li>
                        <li><a href="{{ route('corporation') }}" class="text-sm text-zinc-400 transition hover:text-white">Corporation</a></li>
                        <li><a href="{{ route('dba') }}" class="text-sm text-zinc-400 transition hover:text-white">DBA</a></li>
                        <li><a href="{{ route('nonprofit') }}" class="text-sm text-zinc-400 transition hover:text-white">Nonprofit</a></li>
                        <li><a href="{{ route('registered-agent') }}" class="text-sm text-zinc-400 transition hover:text-white">Registered Agent</a></li>
                        <li><a href="{{ route('ein-tax-id') }}" class="text-sm text-zinc-400 transition hover:text-white">EIN / Tax ID</a></li>
                    </ul>
                </div>

                {{-- Payment Protection --}}
                <div>
                    <h2 class="font-semibold text-white">Payment Protection</h2>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('liens') }}" class="text-sm text-zinc-400 transition hover:text-white">Mechanics Lien</a></li>
                        <li><a href="{{ route('liens.preliminary-notice') }}" class="text-sm text-zinc-400 transition hover:text-white">Preliminary Notice</a></li>
                        <li><a href="{{ route('liens.notice-of-intent-to-lien') }}" class="text-sm text-zinc-400 transition hover:text-white">Notice of Intent</a></li>
                        <li><a href="{{ route('liens.lien-release') }}" class="text-sm text-zinc-400 transition hover:text-white">Lien Release</a></li>
                        <li><a href="{{ route('liens.payment-demand-letter') }}" class="text-sm text-zinc-400 transition hover:text-white">Demand Letter</a></li>
                        <li><a href="{{ route('liens.lien-waivers') }}" class="text-sm text-zinc-400 transition hover:text-white">Lien Waivers</a></li>
                        <li><a href="{{ route('liens.lien-waivers.pricing') }}" class="text-sm text-zinc-400 transition hover:text-white">Lien Waiver Pricing</a></li>
                        <li><a href="{{ route('liens.pricing') }}" class="text-sm text-zinc-400 transition hover:text-white">Lien Filing Pricing</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h2 class="font-semibold text-white">Company</h2>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('contact') }}" class="text-sm text-zinc-400 transition hover:text-white">Contact</a></li>
                        <li><a href="{{ route('sales-tax-registration') }}" class="text-sm text-zinc-400 transition hover:text-white">Sales Tax</a></li>
                        <li><a href="{{ route('resale-certificates') }}" class="text-sm text-zinc-400 transition hover:text-white">Resale Certificates</a></li>
                        <li><a href="{{ route('government.home') }}" class="text-sm text-zinc-400 transition hover:text-white">Government</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h2 class="font-semibold text-white">Legal</h2>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('privacy-policy') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">Privacy Policy</a></li>
                        <li><a href="{{ route('terms-of-service') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">Terms of Service</a></li>
                        <li><a href="{{ route('refund-policy') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">Refund Policy</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 border-t border-zinc-800 pt-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div>
                        <p class="text-sm text-zinc-400">&copy; {{ date('Y') }} {{ config('app.name', 'eRegister') }}. All
                            rights reserved.</p>
                        @if ($footerAddress = config('company.address'))
                            <p class="mt-2 text-sm text-zinc-400">{{ $footerAddress['street'] }}, {{ $footerAddress['locality'] }}, {{ $footerAddress['region'] }} {{ $footerAddress['postal_code'] }}</p>
                        @endif
                        <p class="mt-2 max-w-xl text-xs text-zinc-400">{{ config('app.name', 'eRegister') }} is a private document preparation and filing service. It is not a government agency and is not affiliated with or endorsed by any government agency.</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @if ($usesLivewire)
        @fluxScripts
    @endif
</body>

</html>