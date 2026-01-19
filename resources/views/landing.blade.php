@extends('layouts.landing')

@section('title', 'eRegister - Business Registrations Made Simple')

@section('meta')
<meta name="description"
    content="eRegister simplifies business registrations. Apply for sales tax permits, form LLCs, and handle compliance across multiple states from one platform.">
@endsection

@section('content')
{{-- Hero Section from landing2 (modified) --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            {{-- Left Content --}}
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                    Register Your Business
                    <span class="text-blue-600">Across All 50 States</span>
                </h1>

                <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                    Sales tax permits, LLC formation, use tax registration, and more. One application, multiple states,
                    zero hassle.
                </p>

                <div class="mt-8">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-blue-700">
                        Get Started
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Right - Feature Cards Stack --}}
            <div class="relative">
                <div
                    class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-blue-50 to-indigo-50 opacity-60 blur-xl">
                </div>
                <div class="relative space-y-4">
                    {{-- Card 1 --}}
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Sales Tax Permits</h3>
                            <p class="text-sm text-zinc-500">Multi-state registration in minutes</p>
                        </div>
                        <span
                            class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Popular</span>
                    </div>

                    {{-- Card 2 --}}
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">LLC Formation</h3>
                            <p class="text-sm text-zinc-500">Form your LLC in any state</p>
                        </div>
                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">Fast</span>
                    </div>

                    {{-- Card 3 --}}
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Compliance Management</h3>
                            <p class="text-sm text-zinc-500">Annual reports & renewals</p>
                        </div>
                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Auto</span>
                    </div>

                    {{-- Card 4 --}}
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
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

{{-- Trust Bar --}}
<section class="bg-zinc-900 py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-4">`
            <p class="text-sm font-medium text-zinc-400">Trusted by businesses of all sizes</p>
            <div class="hidden h-4 w-px bg-zinc-700 sm:block"></div>
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium text-white">10,000+ Businesses</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span class="text-sm font-medium text-white">256-bit SSL</span>
            </div>
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium text-white">24/7 Support</span>
            </div>
        </div>
    </div>
</section>

{{-- Services Section from landing1 --}}
<section id="services" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Our Services</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Everything You Need to Stay Compliant
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                From formation to ongoing compliance, we've got you covered in every state.
            </p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
            {{-- Sales Tax Permits --}}
            <div
                class="group rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div
                    class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Sales Tax Permits</h3>
                <p class="mt-3 text-zinc-600">
                    Register for sales and use tax permits in multiple states simultaneously. We handle the paperwork,
                    you focus on selling.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Multi-state applications
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Nexus determination
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Fast processing
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 transition-colors hover:text-blue-700">
                    Get your permits
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            {{-- LLC Formation --}}
            <div
                class="group rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div
                    class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">LLC Formation</h3>
                <p class="mt-3 text-zinc-600">
                    Form your LLC in any state with ease. We prepare and file your articles of organization and provide
                    all required documents.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        All 50 states
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Operating agreement included
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        EIN assistance
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 transition-colors hover:text-indigo-700">
                    Start your LLC
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            {{-- Use Tax Registration --}}
            <div
                class="group rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div
                    class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Use Tax Registration</h3>
                <p class="mt-3 text-zinc-600">
                    Stay compliant with use tax requirements. We help you understand and register for use tax
                    obligations in applicable states.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Compliance guidance
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        State-specific requirements
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Deadline tracking
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 transition-colors hover:text-emerald-700">
                    Learn more
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            {{-- Foreign Qualification --}}
            <div
                class="group rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div
                    class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Foreign Qualification</h3>
                <p class="mt-3 text-zinc-600">
                    Expanding to new states? We handle foreign qualification filings so your business can legally
                    operate across state lines.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Certificate of good standing
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Registered agent services
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Multi-state expansion
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-violet-600 transition-colors hover:text-violet-700">
                    Expand your business
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            {{-- Annual Reports --}}
            <div
                class="group rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div
                    class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Annual Reports</h3>
                <p class="mt-3 text-zinc-600">
                    Never miss a filing deadline. We track and file your annual reports to keep your business in good
                    standing.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Deadline reminders
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Automated filings
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Compliance monitoring
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-amber-600 transition-colors hover:text-amber-700">
                    Stay compliant
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>

            {{-- Business Licenses --}}
            <div
                class="group rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                <div
                    class="mb-5 inline-flex h-14 w-14 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Business Licenses</h3>
                <p class="mt-3 text-zinc-600">
                    Get the licenses you need to operate legally. We research and apply for required business licenses
                    in your jurisdiction.
                </p>
                <ul class="mt-6 space-y-3">
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        License research
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Application preparation
                    </li>
                    <li class="flex items-center gap-3 text-sm text-zinc-600">
                        <svg class="h-5 w-5 shrink-0 text-rose-600" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Renewal tracking
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-1 text-sm font-semibold text-rose-600 transition-colors hover:text-rose-700">
                    Get licensed
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- How It Works from landing2 --}}
<section id="how-it-works" class="bg-white py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Simple Process</p>
            <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                Get Registered in 3 Easy Steps
            </h2>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div
                    class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">
                    1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Select Your States</h3>
                    <p class="mt-3 text-zinc-600">Choose which states you need to register in. We support all 50 states
                        and multiple registration types.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div
                    class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">
                    2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Answer Questions</h3>
                    <p class="mt-3 text-zinc-600">Fill out our smart questionnaire once. We'll adapt your answers to
                        each state's specific requirements.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div
                    class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">
                    3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We File Everything</h3>
                    <p class="mt-3 text-zinc-600">Sit back and relax. We submit your applications and deliver your
                        registrations to your dashboard.</p>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('register') }}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-blue-700">
                Get Started Now
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Why eRegister from landing1 --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Why eRegister</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                    Save Time, Reduce Errors, Stay Compliant
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    Manual registration is tedious and error-prone. eRegister automates the process so you can focus on
                    growing your business.
                </p>

                <dl class="mt-10 space-y-6">
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">10x Faster Than DIY</dt>
                            <dd class="mt-1 text-zinc-600">What takes hours of research and form-filling takes minutes
                                with eRegister.</dd>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">99.9% Accuracy Rate</dt>
                            <dd class="mt-1 text-zinc-600">Built-in validation catches errors before submission. No more
                                rejected applications.</dd>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Transparent Pricing</dt>
                            <dd class="mt-1 text-zinc-600">Know exactly what you'll pay upfront. No hidden fees, no
                                surprises.</dd>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Expert Support</dt>
                            <dd class="mt-1 text-zinc-600">Our compliance specialists are here to help. Real experts,
                                not bots.</dd>
                        </div>
                    </div>
                </dl>
            </div>

            {{-- Dashboard Preview --}}
            <div class="relative">
                <div
                    class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-blue-100 via-indigo-50 to-purple-100 blur-2xl">
                </div>
                <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl">
                    <div class="flex items-center gap-2 border-b border-zinc-100 bg-zinc-50 px-4 py-3">
                        <div class="h-3 w-3 rounded-full bg-red-400"></div>
                        <div class="h-3 w-3 rounded-full bg-amber-400"></div>
                        <div class="h-3 w-3 rounded-full bg-green-400"></div>
                        <span class="ml-2 text-xs text-zinc-400">eRegister Dashboard</span>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between rounded-xl bg-green-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">California Sales Tax</span>
                                </div>
                                <span
                                    class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Approved</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-green-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">Texas Sales Tax</span>
                                </div>
                                <span
                                    class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Approved</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-amber-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100">
                                        <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">New York Sales Tax</span>
                                </div>
                                <span
                                    class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-700">Processing</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-green-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">Delaware LLC</span>
                                </div>
                                <span
                                    class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Formed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Stats Section from landing1 --}}
<section class="bg-zinc-900 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-8 lg:grid-cols-4">
            <div class="text-center">
                <div class="text-4xl font-bold text-white sm:text-5xl">50</div>
                <div class="mt-2 text-zinc-400">States Covered</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-white sm:text-5xl">10K+</div>
                <div class="mt-2 text-zinc-400">Businesses Served</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-white sm:text-5xl">99.9%</div>
                <div class="mt-2 text-zinc-400">Approval Rate</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-white sm:text-5xl">24hr</div>
                <div class="mt-2 text-zinc-400">Avg. Processing</div>
            </div>
        </div>
    </div>
</section>

{{-- Testimonials from landing1 --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Testimonials</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Loved by Business Owners
            </h2>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-8 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                <div class="flex gap-1 text-amber-400">
                    @for ($i = 0; $i < 5; $i++) <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                        @endfor
                </div>
                <p class="mt-4 text-zinc-600">
                    "eRegister saved us weeks of work. We needed sales tax permits in 12 states and they handled
                    everything. The multi-state form was a game changer."
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-600">
                        JM
                    </div>
                    <div>
                        <div class="font-semibold text-zinc-900">Jessica Martinez</div>
                        <div class="text-sm text-zinc-500">E-commerce Founder</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                <div class="flex gap-1 text-amber-400">
                    @for ($i = 0; $i < 5; $i++) <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                        @endfor
                </div>
                <p class="mt-4 text-zinc-600">
                    "Formed my LLC in Delaware and registered in 3 other states. The process was seamless and their
                    support team answered all my questions quickly."
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                        DK
                    </div>
                    <div>
                        <div class="font-semibold text-zinc-900">David Kim</div>
                        <div class="text-sm text-zinc-500">SaaS Startup CEO</div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                <div class="flex gap-1 text-amber-400">
                    @for ($i = 0; $i < 5; $i++) <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                        @endfor
                </div>
                <p class="mt-4 text-zinc-600">
                    "Finally, a registration service that doesn't nickel and dime you. Transparent pricing, fast
                    service, and everything was done right the first time."
                </p>
                <div class="mt-6 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-600">
                        SB
                    </div>
                    <div>
                        <div class="font-semibold text-zinc-900">Sarah Brooks</div>
                        <div class="text-sm text-zinc-500">Retail Business Owner</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ Section from landing1 --}}
<section id="faq" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">FAQ</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Frequently Asked Questions
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                Got questions? We've got answers.
            </p>
        </div>

        <div class="mt-12 space-y-4">
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary
                    class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What is a sales tax permit and do I need one?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    A sales tax permit (also called a seller's permit or resale certificate) allows you to collect sales
                    tax from customers. You need one in each state where you have "nexus" – typically where you have
                    physical presence, employees, or significant sales volume. If you sell products online and ship to
                    multiple states, you likely need permits in those states.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary
                    class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long does it take to form an LLC?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Processing times vary by state. Some states like Delaware can process in 24 hours, while others may
                    take 1-2 weeks. We offer expedited processing options where available. Once you submit your
                    application, we file immediately and provide tracking updates throughout the process.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary
                    class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I register in multiple states at once?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes! That's one of our specialties. Our multi-state application lets you select all the states you
                    need and fill out your business information just once. We automatically adapt the information to
                    each state's specific requirements, saving you hours of repetitive form-filling.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary
                    class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between sales tax and use tax?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Sales tax is collected by sellers at the point of sale. Use tax is owed by buyers when sales tax
                    wasn't collected – for example, on out-of-state purchases or items bought for resale but used by the
                    business. Both are typically the same rate. We can help you understand and comply with both
                    obligations.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary
                    class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's included in the service fee?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Our service fee covers application preparation, filing with the state, progress tracking, and
                    delivery of your registration documents. State filing fees are separate and clearly listed during
                    checkout. There are no hidden fees – what you see is what you pay.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary
                    class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do you offer support if my application is rejected?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Absolutely. While rejections are rare (less than 1%), if it happens, we work with you to correct the
                    issue and resubmit at no additional service charge. Our team reviews every application before
                    submission to catch potential problems, which is why our approval rate is so high.
                </div>
            </details>
        </div>
    </div>
</section>

{{-- Final CTA Section from landing1 --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            {{-- Background decoration --}}
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0"
                    style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;">
                </div>
            </div>

            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Ready to Simplify Your Business Registrations?
                </h2>
                <p class="mt-6 text-lg text-zinc-300">
                    Join thousands of businesses that trust eRegister for their compliance needs. Get started today – no
                    credit card required.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-blue-600/25 transition-all hover:bg-blue-500 hover:shadow-xl sm:w-auto">
                        Create Free Account
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="#services"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-600 px-8 py-4 text-base font-semibold text-white transition-all hover:border-zinc-500 hover:bg-zinc-800 sm:w-auto">
                        View All Services
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection