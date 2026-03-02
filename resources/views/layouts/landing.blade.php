<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => $__env->yieldContent('title', config('app.name', 'eRegister'))])
    @yield('meta')
</head>

<body class="min-h-screen bg-white antialiased">
    <!-- Header -->
    <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/80 backdrop-blur-sm"
        x-data="{ mobileMenuOpen: false }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                    <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" class="h-9 brightness-0" />
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
                            <div class="space-y-1">
                                <a href="{{ route('liens') }}#tracking" class="flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Lien Tracking Portal
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Free</span>
                                </a>
                                <a href="{{ route('liens') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Mechanics / Construction Lien
                                </a>
                                <a href="{{ route('liens.preliminary-notice') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Preliminary Notice
                                </a>
                                <a href="{{ route('liens.notice-of-intent-to-lien') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Notice of Intent to Lien
                                </a>
                                <a href="{{ route('liens.lien-release') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Lien Release
                                </a>
                                <a href="{{ route('liens.payment-demand-letter') }}" class="block rounded-lg px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-50 hover:text-zinc-900">
                                    Payment Demand Letter
                                </a>
                            </div>
                        </div>
                    </div>

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
                            class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800"
                            wire:navigate>
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
                            class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900" wire:navigate>
                            Log in
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800"
                            wire:navigate>
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
                        <div class="ml-3 mt-1 space-y-1 border-l-2 border-zinc-100 pl-3">
                            <a href="{{ route('liens') }}#tracking" class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                                Lien Tracking Portal
                                <span class="rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700">Free</span>
                            </a>
                            <a href="{{ route('liens') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Mechanics / Construction Lien</a>
                            <a href="{{ route('liens.preliminary-notice') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Preliminary Notice</a>
                            <a href="{{ route('liens.notice-of-intent-to-lien') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Notice of Intent to Lien</a>
                            <a href="{{ route('liens.lien-release') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Lien Release</a>
                            <a href="{{ route('liens.payment-demand-letter') }}" class="block rounded-lg px-3 py-1.5 text-sm text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">Payment Demand Letter</a>
                        </div>
                    </details>

                    {{-- Contact --}}
                    <a href="{{ route('contact') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 {{ request()->routeIs('contact') ? 'bg-zinc-100 text-zinc-900' : '' }}">
                        Contact
                    </a>

                    {{-- Auth --}}
                    <div class="mt-3 flex flex-col gap-2 border-t border-zinc-200 pt-3">
                        @auth
                        <a href="{{ auth()->user()->roles->isNotEmpty() ? route('admin.home') : url('/portal') }}"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-center text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800"
                            wire:navigate>
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
                            class="rounded-lg px-3 py-2 text-center text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900"
                            wire:navigate>
                            Log in
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="rounded-lg bg-zinc-900 px-4 py-2 text-center text-sm font-medium text-white shadow-sm transition hover:bg-zinc-800"
                            wire:navigate>
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
                    <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                        <img src="/img/logo/eregister-logo-light-svg.svg" alt="eRegister" class="h-10 brightness-0 invert" />
                    </a>
                    <p class="mt-4 text-sm text-zinc-400">
                        Business formation, compliance, and payment protection across all 50 states.
                    </p>
                </div>

                {{-- Form a Business --}}
                <div>
                    <h4 class="font-semibold text-white">Form a Business</h4>
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
                    <h4 class="font-semibold text-white">Payment Protection</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('liens') }}" class="text-sm text-zinc-400 transition hover:text-white">Mechanics Lien</a></li>
                        <li><a href="{{ route('liens.preliminary-notice') }}" class="text-sm text-zinc-400 transition hover:text-white">Preliminary Notice</a></li>
                        <li><a href="{{ route('liens.notice-of-intent-to-lien') }}" class="text-sm text-zinc-400 transition hover:text-white">Notice of Intent</a></li>
                        <li><a href="{{ route('liens.lien-release') }}" class="text-sm text-zinc-400 transition hover:text-white">Lien Release</a></li>
                        <li><a href="{{ route('liens.payment-demand-letter') }}" class="text-sm text-zinc-400 transition hover:text-white">Demand Letter</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h4 class="font-semibold text-white">Company</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('contact') }}" class="text-sm text-zinc-400 transition hover:text-white">Contact</a></li>
                        <li><a href="{{ route('sales-tax-registration') }}" class="text-sm text-zinc-400 transition hover:text-white">Sales Tax</a></li>
                        <li><a href="{{ route('resale-certificates') }}" class="text-sm text-zinc-400 transition hover:text-white">Resale Certificates</a></li>
                    </ul>
                </div>

                {{-- Legal --}}
                <div>
                    <h4 class="font-semibold text-white">Legal</h4>
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
                    <p class="text-sm text-zinc-500">&copy; {{ date('Y') }} {{ config('app.name', 'eRegister') }}. All
                        rights reserved.</p>
                    <div class="flex gap-6">
                        <a href="#" class="text-zinc-400 transition hover:text-white">
                            <span class="sr-only">Twitter</span>
                            <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                            </svg>
                        </a>
                        <a href="#" class="text-zinc-400 transition hover:text-white">
                            <span class="sr-only">LinkedIn</span>
                            <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @fluxScripts
</body>

</html>