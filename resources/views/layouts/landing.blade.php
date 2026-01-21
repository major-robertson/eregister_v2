<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    <title>@yield('title', config('app.name', 'eRegister'))</title>
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
                    <span class="text-xl font-semibold text-zinc-900"
                        style="font-family: 'Inter', sans-serif;">eRegister</span>
                </a>

                <!-- Main Navigation -->
                <nav class="hidden items-center gap-8 md:flex">
                    <a href="{{ route('liens') }}"
                        class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900 {{ request()->routeIs('liens') ? 'text-zinc-900' : '' }}">
                        Liens
                    </a>
                    <a href="{{ route('home') }}"
                        class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900 {{ request()->routeIs('home') ? 'text-zinc-900' : '' }}">
                        Sales Tax
                    </a>
                    <a href="#" class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                        Resale Certificates
                    </a>
                    <a href="#" class="text-sm font-medium text-zinc-600 transition hover:text-zinc-900">
                        LLC
                    </a>
                </nav>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-4">
                    <nav class="hidden items-center gap-4 md:flex">
                        @auth
                        <a href="{{ url('/portal') }}"
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
                        class="inline-flex items-center justify-center rounded-md p-2 text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 md:hidden">
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
            x-transition:leave-end="opacity-0 scale-95" class="md:hidden" style="display: none;">
            <div class="border-t border-zinc-200 bg-white px-4 pb-4 pt-2">
                <nav class="flex flex-col gap-3">
                    <a href="{{ route('liens') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 {{ request()->routeIs('liens') ? 'bg-zinc-100 text-zinc-900' : '' }}">
                        Liens
                    </a>
                    <a href="{{ route('home') }}"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900 {{ request()->routeIs('home') ? 'bg-zinc-100 text-zinc-900' : '' }}">
                        Sales Tax
                    </a>
                    <a href="#"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                        Resale Certificates
                    </a>
                    <a href="#"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-900">
                        LLC
                    </a>
                    <div class="mt-3 flex flex-col gap-2 border-t border-zinc-200 pt-3">
                        @auth
                        <a href="{{ url('/portal') }}"
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
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                {{-- Company Info --}}
                <div class="col-span-2 md:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center" wire:navigate>
                        <span class="text-xl font-semibold text-white"
                            style="font-family: 'Inter', sans-serif;">eRegister</span>
                    </a>
                    <p class="mt-4 text-sm text-zinc-400">
                        Simplifying business registrations across all 50 states. Sales tax, LLC formation, and
                        compliance made easy.
                    </p>
                </div>

                {{-- Services --}}
                <div>
                    <h4 class="font-semibold text-white">Services</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="{{ route('register') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">Sales Tax Permits</a></li>
                        <li><a href="{{ route('register') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">LLC Formation</a></li>
                        <li><a href="{{ route('register') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">Use Tax Registration</a></li>
                        <li><a href="{{ route('register') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">Annual Reports</a></li>
                    </ul>
                </div>

                {{-- Company --}}
                <div>
                    <h4 class="font-semibold text-white">Company</h4>
                    <ul class="mt-4 space-y-3">
                        <li><a href="#" class="text-sm text-zinc-400 transition hover:text-white">About Us</a></li>
                        <li><a href="#faq" class="text-sm text-zinc-400 transition hover:text-white">FAQ</a></li>
                        <li><a href="{{ route('contact') }}"
                                class="text-sm text-zinc-400 transition hover:text-white">Contact</a></li>
                        <li><a href="#" class="text-sm text-zinc-400 transition hover:text-white">Blog</a></li>
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