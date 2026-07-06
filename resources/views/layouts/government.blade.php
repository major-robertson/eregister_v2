<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head', ['title' => $__env->yieldContent('title', 'eRegister Government')])
    @yield('meta')
</head>

<body class="min-h-screen bg-white antialiased">
    <!-- Top Bar -->
    <div class="border-b border-slate-800 bg-slate-950 text-slate-300">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-1.5 text-xs sm:px-6 lg:px-8">
            <div class="flex items-center gap-2">
                <svg class="h-3.5 w-3.5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                        clip-rule="evenodd" />
                </svg>
                <span>A commercial digital services partner for state and local government teams</span>
            </div>
            <a href="{{ route('home') }}" class="hidden text-slate-400 transition hover:text-white sm:inline">
                Visit eRegister.com &rarr;
            </a>
        </div>
    </div>

    <!-- Header -->
    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/90 backdrop-blur"
        x-data="{ mobileMenuOpen: false }">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <!-- Logo -->
                <a href="{{ route('government.home') }}" class="flex items-center gap-3" wire:navigate>
                    <img src="/img/logo/eregister-logo-dark-svg.svg" alt="eRegister" class="h-9" />
                    <span
                        class="rounded-md bg-blue-100 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-blue-700">
                        Government
                    </span>
                </a>

                <!-- Main Navigation (Desktop) -->
                <nav class="hidden items-center gap-1 lg:flex">
                    <a href="{{ route('government.home') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 {{ request()->routeIs('government.home') ? 'text-slate-900' : '' }}">
                        Overview
                    </a>

                    {{-- Services Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click="open = !open" type="button"
                            class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 {{ request()->routeIs('government.website-redesign', 'government.accessibility', 'government.cms', 'government.hosting', 'government.maintenance', 'government.portals', 'government.integrations', 'government.implementation') ? 'text-slate-900' : '' }}">
                            Solutions
                            <svg class="h-4 w-4 transition" :class="open ? 'rotate-180' : ''" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 translate-y-1"
                            class="absolute left-1/2 top-full z-50 mt-1 w-[36rem] -translate-x-1/2 rounded-xl border border-slate-200 bg-white p-5 shadow-xl"
                            style="display: none;">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Build
                                    </p>
                                    <div class="space-y-1">
                                        <a href="{{ route('government.website-redesign') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            Website Redesign
                                        </a>
                                        <a href="{{ route('government.cms') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            Content Management (CMS)
                                        </a>
                                        <a href="{{ route('government.portals') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            Citizen &amp; Staff Portals
                                        </a>
                                        <a href="{{ route('government.integrations') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            System Integrations
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                                        Operate</p>
                                    <div class="space-y-1">
                                        <a href="{{ route('government.hosting') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            Hosting &amp; Infrastructure
                                        </a>
                                        <a href="{{ route('government.maintenance') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            Maintenance &amp; Support
                                        </a>
                                        <a href="{{ route('government.accessibility') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            Accessibility (Section 508)
                                        </a>
                                        <a href="{{ route('government.implementation') }}"
                                            class="block rounded-lg px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                            Implementation Services
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('contact') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900">
                        Contact
                    </a>
                </nav>

                <!-- CTA -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('contact') }}"
                        class="hidden items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 lg:inline-flex">
                        Request Consultation
                    </a>

                    <!-- Mobile menu button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button"
                        class="inline-flex items-center justify-center rounded-md p-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 lg:hidden">
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
            <div class="max-h-[80vh] overflow-y-auto border-t border-slate-200 bg-white px-4 pb-4 pt-2">
                <nav class="flex flex-col gap-1">
                    <a href="{{ route('government.home') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 {{ request()->routeIs('government.home') ? 'bg-slate-100 text-slate-900' : '' }}">
                        Overview
                    </a>

                    <details class="group">
                        <summary
                            class="flex cursor-pointer list-none items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 [&::-webkit-details-marker]:hidden">
                            Solutions
                            <svg class="h-4 w-4 shrink-0 text-slate-400 transition group-open:rotate-180" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <div class="ml-3 mt-1 space-y-1 border-l-2 border-slate-100 pl-3">
                            <p class="px-3 pt-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Build</p>
                            <a href="{{ route('government.website-redesign') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Website
                                Redesign</a>
                            <a href="{{ route('government.cms') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Content
                                Management (CMS)</a>
                            <a href="{{ route('government.portals') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Citizen
                                &amp; Staff Portals</a>
                            <a href="{{ route('government.integrations') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">System
                                Integrations</a>
                            <p class="px-3 pt-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Operate
                            </p>
                            <a href="{{ route('government.hosting') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Hosting
                                &amp; Infrastructure</a>
                            <a href="{{ route('government.maintenance') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Maintenance
                                &amp; Support</a>
                            <a href="{{ route('government.accessibility') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Accessibility
                                (Section 508)</a>
                            <a href="{{ route('government.implementation') }}"
                                class="block rounded-lg px-3 py-1.5 text-sm text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">Implementation
                                Services</a>
                        </div>
                    </details>

                    <a href="{{ route('contact') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-900">
                        Contact
                    </a>

                    <div class="mt-3 border-t border-slate-200 pt-3">
                        <a href="{{ route('contact') }}"
                            class="block rounded-lg bg-blue-700 px-4 py-2 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800">
                            Request Consultation
                        </a>
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
    <footer class="border-t border-slate-800 bg-slate-950">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-5">
                {{-- Brand --}}
                <div class="col-span-2 md:col-span-2">
                    <a href="{{ route('government.home') }}" class="flex items-center gap-3">
                        <img src="/img/logo/eregister-logo-light-svg.svg" alt="eRegister"
                            class="h-10" />
                        <span
                            class="rounded-md bg-blue-500/15 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider text-blue-300 ring-1 ring-inset ring-blue-500/30">
                            Government
                        </span>
                    </a>
                    <p class="mt-4 max-w-md text-sm text-slate-400">
                        Modern websites, accessible portals, and reliable infrastructure for federal, state, and local
                        agencies. Built by a U.S.-based team that understands public-sector procurement.
                    </p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center rounded-md bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-300 ring-1 ring-inset ring-slate-700">
                            U.S.-based team
                        </span>
                        <span
                            class="inline-flex items-center rounded-md bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-300 ring-1 ring-inset ring-slate-700">
                            WCAG 2.2 AA
                        </span>
                        <span
                            class="inline-flex items-center rounded-md bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-300 ring-1 ring-inset ring-slate-700">
                            Section 508
                        </span>
                    </div>
                </div>

                {{-- Build --}}
                <div>
                    <h4 class="font-semibold text-white">Build</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('government.website-redesign') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Website Redesign</a></li>
                        <li><a href="{{ route('government.cms') }}"
                                class="text-sm text-slate-400 transition hover:text-white">CMS</a></li>
                        <li><a href="{{ route('government.portals') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Portals</a></li>
                        <li><a href="{{ route('government.integrations') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Integrations</a></li>
                    </ul>
                </div>

                {{-- Operate --}}
                <div>
                    <h4 class="font-semibold text-white">Operate</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('government.hosting') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Hosting</a></li>
                        <li><a href="{{ route('government.maintenance') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Maintenance</a></li>
                        <li><a href="{{ route('government.accessibility') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Accessibility</a></li>
                        <li><a href="{{ route('government.implementation') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Implementation</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h4 class="font-semibold text-white">Company</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('contact') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Contact</a></li>
                        <li><a href="{{ route('home') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Main eRegister site</a></li>
                        <li><a href="{{ route('privacy-policy') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Privacy Policy</a></li>
                        <li><a href="{{ route('terms-of-service') }}"
                                class="text-sm text-slate-400 transition hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 border-t border-slate-800 pt-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <p class="text-sm text-slate-500">&copy; {{ date('Y') }} {{ config('app.name', 'eRegister') }}.
                        All rights reserved.</p>
                    <p class="text-xs text-slate-500">
                        Not a government agency. eRegister is a private commercial vendor of digital services.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    @fluxScripts
</body>

</html>
