@extends('layouts.landing')

@section('title', 'eRegister - LLC Formation | All 50 States')

@section('meta')
<meta name="description"
    content="Form your LLC with eRegister. Everything included: LLC filing, registered agent, annual renewals, and compliance management. Available in all 50 states.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            {{-- Left Content --}}
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                    Form Your LLC
                    <span class="text-blue-600">The Right Way</span>
                </h1>

                <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                    LLC formation, registered agent, compliance management, and ongoing support. We handle the paperwork so you can focus on your business.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-blue-700">
                        Get Started
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="#pricing"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-700 transition hover:bg-zinc-50">
                        View Pricing
                    </a>
                </div>
            </div>

            {{-- Right - Feature Cards Stack --}}
            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-blue-50 to-indigo-50 opacity-60 blur-xl"></div>
                <div class="relative space-y-4">
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">LLC Formation & Filing</h3>
                            <p class="text-sm text-zinc-500">Articles of Organization filed with the state</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Registered Agent Service</h3>
                            <p class="text-sm text-zinc-500">We receive legal documents on your behalf</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Compliance Management</h3>
                            <p class="text-sm text-zinc-500">Annual reports & deadline reminders</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Ongoing Support</h3>
                            <p class="text-sm text-zinc-500">Expert help whenever you need it</p>
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
        <div class="flex flex-wrap items-center justify-center gap-x-12 gap-y-4">
            <p class="text-sm font-medium text-zinc-400">Trusted by businesses of all sizes</p>
            <div class="hidden h-4 w-px bg-zinc-700 sm:block"></div>
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium text-white">All 50 States</span>
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

{{-- Why LLC Section --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Benefits</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                    Why Form an LLC?
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    An LLC gives you personal asset protection and tax flexibility while keeping things simple.
                </p>

                <dl class="mt-10 space-y-6">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Personal Asset Protection</dt>
                            <dd class="mt-1 text-zinc-600">Separate your business debts from personal assets like your home and savings.</dd>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Tax Flexibility</dt>
                            <dd class="mt-1 text-zinc-600">Choose how your LLC is taxed - as a sole proprietor, partnership, or corporation.</dd>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Business Credibility</dt>
                            <dd class="mt-1 text-zinc-600">An LLC shows customers and partners that you're a legitimate, established business.</dd>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Simple Compliance</dt>
                            <dd class="mt-1 text-zinc-600">LLCs have fewer formalities than corporations - no board meetings or resolutions required.</dd>
                        </div>
                    </div>
                </dl>
            </div>

            {{-- Right side visual --}}
            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-blue-100 via-indigo-50 to-purple-100 blur-2xl"></div>
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
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">LLC Formation</span>
                                </div>
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Complete</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-green-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">Registered Agent</span>
                                </div>
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-green-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                                        <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">EIN Obtained</span>
                                </div>
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Complete</span>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-blue-50 p-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                                        <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-zinc-900">Annual Report</span>
                                </div>
                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700">Due Dec 2026</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section id="how-it-works" class="bg-white py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Simple Process</p>
            <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                Get Your LLC in 3 Easy Steps
            </h2>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Choose Your State</h3>
                    <p class="mt-3 text-zinc-600">Select the state where you want to form your LLC. We recommend your home state for most businesses.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Enter Your Details</h3>
                    <p class="mt-3 text-zinc-600">Provide your business information. Our smart form guides you through everything you need.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We Handle the Rest</h3>
                    <p class="mt-3 text-zinc-600">We file with the state, set up your registered agent, and deliver your documents. You're done!</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Pricing Section --}}
<section id="pricing" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Pricing</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                One Simple Price
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                Everything you need to form and maintain your LLC. No hidden fees.
            </p>
        </div>

        <div class="mx-auto mt-12 max-w-lg">
            <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-xl">
                <div class="bg-zinc-900 px-8 py-8 text-center text-white">
                    <p class="text-sm font-medium uppercase tracking-wider text-zinc-400">Complete LLC Package</p>
                    <div class="mt-4 flex items-baseline justify-center gap-2">
                        <span class="text-5xl font-extrabold">$297</span>
                        <span class="text-xl text-zinc-400">/year</span>
                    </div>
                    <p class="mt-2 text-sm text-zinc-400">Plus state filing fees</p>
                </div>
                <div class="p-8">
                    <ul class="space-y-4 text-zinc-600">
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            LLC Formation & State Filing
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Registered Agent Service (1 year)
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Operating Agreement Template
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            EIN / Tax ID Application
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Annual Report Filing
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Compliance Alerts & Reminders
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Secure Document Storage
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Unlimited Email & Chat Support
                        </li>
                    </ul>
                    <div class="mt-8">
                        <a href="{{ route('register') }}"
                            class="block w-full rounded-lg bg-blue-600 px-6 py-4 text-center text-base font-semibold text-white transition hover:bg-blue-700">
                            Get Started Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Why Choose eRegister --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Compare</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Why Choose eRegister?
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                See how our all-inclusive package compares to typical LLC formation services.
            </p>
        </div>

        <div class="mt-12 overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-zinc-200 bg-zinc-50">
                        <th class="px-6 py-4 text-left text-sm font-semibold text-zinc-900">Feature</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-zinc-500">Others</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-blue-600">eRegister</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    <tr>
                        <td class="px-6 py-4 text-sm text-zinc-900">LLC Formation & Filing</td>
                        <td class="px-6 py-4 text-center text-zinc-500">$99-$299</td>
                        <td class="px-6 py-4 text-center font-medium text-blue-600">Included</td>
                    </tr>
                    <tr class="bg-zinc-50/50">
                        <td class="px-6 py-4 text-sm text-zinc-900">Registered Agent (1 year)</td>
                        <td class="px-6 py-4 text-center text-zinc-500">$99-$199/yr</td>
                        <td class="px-6 py-4 text-center font-medium text-blue-600">Included</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-zinc-900">Operating Agreement</td>
                        <td class="px-6 py-4 text-center text-zinc-500">$30-$99</td>
                        <td class="px-6 py-4 text-center font-medium text-blue-600">Included</td>
                    </tr>
                    <tr class="bg-zinc-50/50">
                        <td class="px-6 py-4 text-sm text-zinc-900">EIN / Tax ID</td>
                        <td class="px-6 py-4 text-center text-zinc-500">$50-$99</td>
                        <td class="px-6 py-4 text-center font-medium text-blue-600">Included</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-zinc-900">Annual Report Filing</td>
                        <td class="px-6 py-4 text-center text-zinc-500">$50-$150/yr</td>
                        <td class="px-6 py-4 text-center font-medium text-blue-600">Included</td>
                    </tr>
                    <tr class="bg-zinc-50/50">
                        <td class="px-6 py-4 text-sm text-zinc-900">Compliance Alerts</td>
                        <td class="px-6 py-4 text-center text-zinc-500">$29-$99/yr</td>
                        <td class="px-6 py-4 text-center font-medium text-blue-600">Included</td>
                    </tr>
                    <tr class="border-t-2 border-blue-200 bg-blue-50">
                        <td class="px-6 py-4 text-sm font-semibold text-zinc-900">Total (first year)</td>
                        <td class="px-6 py-4 text-center font-semibold text-zinc-500">$400-$900+</td>
                        <td class="px-6 py-4 text-center text-xl font-bold text-blue-600">$297</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- FAQ Section --}}
<section id="faq" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">FAQ</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Frequently Asked Questions
            </h2>
        </div>

        <div class="mt-12 space-y-4">
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long does it take to form an LLC?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Processing times vary by state. Some states like Delaware can process in 24 hours, while others may take 1-2 weeks. We file immediately after you complete your application and provide tracking updates throughout the process.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What is a registered agent and why do I need one?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    A registered agent receives legal and tax documents on behalf of your LLC. Every state requires LLCs to have a registered agent with a physical address in that state. We serve as your registered agent, so you don't have to use your personal address.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Which state should I form my LLC in?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    For most small businesses, we recommend forming in your home state (where you live and do business). States like Delaware and Wyoming are popular for larger companies or those seeking specific legal protections, but they may require additional registration in your home state.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Are state filing fees included?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    State filing fees are separate and vary by state (typically $50-$500). These fees are paid directly to the state and are clearly shown during checkout.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What happens after I form my LLC?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Once your LLC is formed, we continue to support you with registered agent service, annual report filing, compliance alerts, and document storage. We'll remind you of important deadlines and handle your annual report filings to keep your LLC in good standing.
                </div>
            </details>
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            {{-- Background decoration --}}
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>

            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Ready to Form Your LLC?
                </h2>
                <p class="mt-6 text-lg text-zinc-300">
                    Get started in minutes. We'll handle the paperwork so you can focus on your business.
                </p>
                <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-blue-600/25 transition-all hover:bg-blue-500 hover:shadow-xl sm:w-auto">
                        Get Started Now
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                    <a href="#pricing"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-600 px-8 py-4 text-base font-semibold text-white transition-all hover:border-zinc-500 hover:bg-zinc-800 sm:w-auto">
                        View Pricing
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
