@extends('layouts.landing')

@section('title', 'eRegister - Business Registrations Made Simple')

@section('meta')
    <meta name="description" content="eRegister simplifies business registrations. Apply for sales tax permits, form LLCs, and handle compliance across multiple states from one platform.">
@endsection

@section('content')
    {{-- Hero Section - Clean White with Accent --}}
    <section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
                {{-- Left Content --}}
                <div>
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700">
                        <span class="flex h-2 w-2 rounded-full bg-blue-500"></span>
                        Trusted by 10,000+ businesses nationwide
                    </div>

                    <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                        Register Your Business
                        <span class="text-blue-600">Across All 50 States</span>
                    </h1>

                    <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                        Sales tax permits, LLC formation, use tax registration, and more. One application, multiple states, zero hassle.
                    </p>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-4 text-base font-semibold text-white transition hover:bg-blue-700">
                            Start Free Registration
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="#services" class="inline-flex items-center justify-center gap-2 rounded-lg border-2 border-zinc-200 bg-white px-6 py-4 text-base font-semibold text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-50">
                            Explore Services
                        </a>
                    </div>

                    <div class="mt-10 flex items-center gap-8 border-t border-zinc-100 pt-8">
                        <div>
                            <div class="text-3xl font-bold text-zinc-900">50</div>
                            <div class="text-sm text-zinc-500">States</div>
                        </div>
                        <div class="h-12 w-px bg-zinc-200"></div>
                        <div>
                            <div class="text-3xl font-bold text-zinc-900">10K+</div>
                            <div class="text-sm text-zinc-500">Businesses</div>
                        </div>
                        <div class="h-12 w-px bg-zinc-200"></div>
                        <div>
                            <div class="text-3xl font-bold text-zinc-900">99.9%</div>
                            <div class="text-sm text-zinc-500">Approval</div>
                        </div>
                    </div>
                </div>

                {{-- Right - Feature Cards Stack --}}
                <div class="relative">
                    <div class="absolute -inset-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-3xl blur-xl opacity-60"></div>
                    <div class="relative space-y-4">
                        {{-- Card 1 --}}
                        <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-zinc-900">Sales Tax Permits</h3>
                                <p class="text-sm text-zinc-500">Multi-state registration in minutes</p>
                            </div>
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Popular</span>
                        </div>

                        {{-- Card 2 --}}
                        <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-zinc-900">LLC Formation</h3>
                                <p class="text-sm text-zinc-500">Form your LLC in any state</p>
                            </div>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">Fast</span>
                        </div>

                        {{-- Card 3 --}}
                        <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-zinc-900">Compliance Management</h3>
                                <p class="text-sm text-zinc-500">Annual reports & renewals</p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Auto</span>
                        </div>

                        {{-- Card 4 --}}
                        <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-zinc-900">Foreign Qualification</h3>
                                <p class="text-sm text-zinc-500">Expand to new states easily</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Logos / Trust Bar --}}
    <section class="border-y border-zinc-100 bg-zinc-50 py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <p class="mb-6 text-center text-sm font-medium text-zinc-500">Trusted by businesses of all sizes</p>
            <div class="flex flex-wrap items-center justify-center gap-x-10 gap-y-4">
                <div class="flex items-center gap-2 text-zinc-400">
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-medium text-zinc-600">SOC 2 Compliant</span>
                </div>
                <div class="flex items-center gap-2 text-zinc-400">
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-medium text-zinc-600">256-bit SSL</span>
                </div>
                <div class="flex items-center gap-2 text-zinc-400">
                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="font-medium text-zinc-600">BBB A+ Rated</span>
                </div>
                <div class="flex items-center gap-2 text-zinc-400">
                    <svg class="h-6 w-6 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <span class="font-medium text-zinc-600">4.9/5 on Trustpilot</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Services Grid --}}
    <section id="services" class="bg-white py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-blue-600">What We Offer</p>
                <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                    Complete Business Registration Services
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    From sales tax to LLC formation, we handle all the paperwork so you can focus on running your business.
                </p>
            </div>

            <div class="mt-16 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                {{-- Service 1 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition hover:border-blue-200 hover:shadow-xl">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900">Sales Tax Permits</h3>
                    <p class="mt-2 text-zinc-600">Register for sales and use tax permits across multiple states with a single application.</p>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Apply to all 50 states
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Nexus analysis included
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Fast-track processing
                        </li>
                    </ul>
                </div>

                {{-- Service 2 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition hover:border-blue-200 hover:shadow-xl">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900">LLC Formation</h3>
                    <p class="mt-2 text-zinc-600">Form your LLC quickly and correctly with our guided process and expert review.</p>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Articles of Organization
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Operating Agreement
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            EIN application help
                        </li>
                    </ul>
                </div>

                {{-- Service 3 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition hover:border-blue-200 hover:shadow-xl">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900">Use Tax Registration</h3>
                    <p class="mt-2 text-zinc-600">Stay compliant with use tax requirements across all states where you operate.</p>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Compliance assessment
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            State-specific guidance
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Filing support
                        </li>
                    </ul>
                </div>

                {{-- Service 4 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition hover:border-blue-200 hover:shadow-xl">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-violet-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900">Foreign Qualification</h3>
                    <p class="mt-2 text-zinc-600">Expand your business to new states with proper foreign qualification filings.</p>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Certificate of Good Standing
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Registered agent setup
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Multi-state filing
                        </li>
                    </ul>
                </div>

                {{-- Service 5 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition hover:border-blue-200 hover:shadow-xl">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900">Annual Reports</h3>
                    <p class="mt-2 text-zinc-600">Never miss a deadline with automated annual report tracking and filing.</p>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Deadline monitoring
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Auto-renewal option
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Good standing maintenance
                        </li>
                    </ul>
                </div>

                {{-- Service 6 --}}
                <div class="group rounded-2xl border border-zinc-200 bg-white p-8 transition hover:border-blue-200 hover:shadow-xl">
                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-rose-600 text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-zinc-900">Business Licenses</h3>
                    <p class="mt-2 text-zinc-600">Get the required licenses to operate legally in your city, county, and state.</p>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600">
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            License research
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Application prep
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Renewal reminders
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- How It Works --}}
    <section id="how-it-works" class="bg-zinc-50 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Simple Process</p>
                <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                    Get Registered in 3 Easy Steps
                </h2>
            </div>

            <div class="mt-16 grid gap-8 md:grid-cols-3">
                <div class="relative rounded-2xl bg-white p-8 shadow-sm">
                    <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">1</div>
                    <div class="pt-4">
                        <h3 class="text-xl font-bold text-zinc-900">Select Your States</h3>
                        <p class="mt-3 text-zinc-600">Choose which states you need to register in. We support all 50 states and multiple registration types.</p>
                    </div>
                </div>

                <div class="relative rounded-2xl bg-white p-8 shadow-sm">
                    <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">2</div>
                    <div class="pt-4">
                        <h3 class="text-xl font-bold text-zinc-900">Answer Questions</h3>
                        <p class="mt-3 text-zinc-600">Fill out our smart questionnaire once. We'll adapt your answers to each state's specific requirements.</p>
                    </div>
                </div>

                <div class="relative rounded-2xl bg-white p-8 shadow-sm">
                    <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">3</div>
                    <div class="pt-4">
                        <h3 class="text-xl font-bold text-zinc-900">We File Everything</h3>
                        <p class="mt-3 text-zinc-600">Sit back and relax. We submit your applications and deliver your registrations to your dashboard.</p>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-blue-700">
                    Get Started Now
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Why Choose Us --}}
    <section class="bg-white py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid items-center gap-16 lg:grid-cols-2">
                {{-- Stats Cards --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-blue-600 p-6 text-white">
                        <div class="text-4xl font-extrabold">50</div>
                        <div class="mt-1 text-blue-100">States Covered</div>
                    </div>
                    <div class="rounded-2xl bg-zinc-900 p-6 text-white">
                        <div class="text-4xl font-extrabold">10K+</div>
                        <div class="mt-1 text-zinc-400">Happy Customers</div>
                    </div>
                    <div class="rounded-2xl bg-zinc-100 p-6">
                        <div class="text-4xl font-extrabold text-zinc-900">99.9%</div>
                        <div class="mt-1 text-zinc-500">Approval Rate</div>
                    </div>
                    <div class="rounded-2xl bg-emerald-600 p-6 text-white">
                        <div class="text-4xl font-extrabold">24h</div>
                        <div class="mt-1 text-emerald-100">Avg. Turnaround</div>
                    </div>
                </div>

                {{-- Content --}}
                <div>
                    <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Why eRegister</p>
                    <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                        Built for Growing Businesses
                    </h2>
                    <p class="mt-4 text-lg text-zinc-600">
                        We've helped thousands of businesses navigate the complex world of state registrations. Here's why they choose us.
                    </p>

                    <div class="mt-8 space-y-6">
                        <div class="flex gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-zinc-900">Lightning Fast</h3>
                                <p class="text-zinc-600">What takes others weeks takes us days. Most registrations complete in 24-48 hours.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-zinc-900">Error-Free Guarantee</h3>
                                <p class="text-zinc-600">Our validation system catches mistakes before submission. If we make an error, we fix it free.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-zinc-900">Transparent Pricing</h3>
                                <p class="text-zinc-600">No hidden fees, ever. See exactly what you'll pay before you start, including all state fees.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Testimonials --}}
    <section class="bg-zinc-50 py-20 lg:py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Testimonials</p>
                <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                    What Our Customers Say
                </h2>
            </div>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <div class="flex gap-1 text-amber-400">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-zinc-600">"Registered in 12 states in one afternoon. The multi-state form is brilliant - I only had to enter my info once."</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-600">JM</div>
                        <div>
                            <div class="font-semibold text-zinc-900">Jessica M.</div>
                            <div class="text-sm text-zinc-500">E-commerce Owner</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <div class="flex gap-1 text-amber-400">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-zinc-600">"Formed my Delaware LLC and got registered in 3 additional states. Their support team was incredibly helpful throughout."</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-600">DK</div>
                        <div>
                            <div class="font-semibold text-zinc-900">David K.</div>
                            <div class="text-sm text-zinc-500">SaaS Founder</div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <div class="flex gap-1 text-amber-400">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="mt-4 text-zinc-600">"No more worrying about compliance deadlines. eRegister handles our annual reports across 8 states automatically now."</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-600">SB</div>
                        <div>
                            <div class="font-semibold text-zinc-900">Sarah B.</div>
                            <div class="text-sm text-zinc-500">Retail Business</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="bg-white py-20 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-blue-600">FAQ</p>
                <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                    Common Questions
                </h2>
            </div>

            <div class="mt-12 divide-y divide-zinc-200">
                <details class="group py-5">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                        What is a sales tax permit?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-3 text-zinc-600">A sales tax permit allows you to collect sales tax from customers. You need one in each state where you have nexus (physical presence or significant sales).</p>
                </details>

                <details class="group py-5">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                        How long does LLC formation take?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-3 text-zinc-600">It varies by state. Delaware can process in 24 hours, while other states may take 1-2 weeks. We offer expedited options where available.</p>
                </details>

                <details class="group py-5">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                        Can I register in multiple states at once?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-3 text-zinc-600">Yes! Our multi-state application lets you select all states you need and fill out your information once. We adapt it to each state's requirements.</p>
                </details>

                <details class="group py-5">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                        What's included in the service fee?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-3 text-zinc-600">Our fee covers application preparation, filing, progress tracking, and delivery of documents. State fees are separate and clearly listed at checkout.</p>
                </details>

                <details class="group py-5">
                    <summary class="flex cursor-pointer items-center justify-between font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                        What if my application is rejected?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <p class="mt-3 text-zinc-600">Rejections are rare (under 1%), but if it happens, we correct the issue and resubmit at no additional charge. Our validation catches most issues beforehand.</p>
                </details>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="bg-blue-600 py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                Ready to Get Registered?
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-blue-100">
                Join thousands of businesses that trust eRegister. Start your free registration today.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-white px-8 py-4 text-base font-semibold text-blue-600 transition hover:bg-blue-50 sm:w-auto">
                    Create Free Account
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="#services" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border-2 border-white/30 px-8 py-4 text-base font-semibold text-white transition hover:bg-white/10 sm:w-auto">
                    View Services
                </a>
            </div>
            <p class="mt-6 text-sm text-blue-200">No credit card required. Start in under 5 minutes.</p>
        </div>
    </section>
@endsection
