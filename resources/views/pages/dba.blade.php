@extends('layouts.landing')

@section('title', 'File a DBA Name | Register Doing Business As (DBA) Online')

@section('meta')
<meta name="description"
    content="File a DBA, fictitious business name, or trade name online with eRegister. Register your Doing Business As name in all 50 states. Fast, simple, and compliant.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                    Register Your DBA
                    <span class="text-indigo-600">The Easy Way</span>
                </h1>

                <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                    File your fictitious business name, trade name, or Doing Business As (DBA) online. Operate under a different name than your legal entity—quickly and compliantly.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-indigo-700">
                        File Your DBA
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-indigo-50 to-purple-50 opacity-60 blur-xl"></div>
                <div class="relative space-y-4">
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">DBA Registration</h3>
                            <p class="text-sm text-zinc-500">File with county or state as required</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Trade Name Protection</h3>
                            <p class="text-sm text-zinc-500">Publicly register your business name</p>
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
                            <h3 class="font-bold text-zinc-900">All 50 States</h3>
                            <p class="text-sm text-zinc-500">County and state filing support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- What is a DBA --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">Learn</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                    What is a DBA?
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    A DBA (Doing Business As), also known as a fictitious business name or trade name, lets you operate under a name different from your legal entity name.
                </p>

                <p class="mt-6 text-zinc-600">
                    For example, if your LLC is "Smith Enterprises LLC" but you want to run a store called "Mountain Outdoor Gear," you file a DBA for "Mountain Outdoor Gear." Sole proprietors and partnerships often use DBAs to brand their businesses professionally without forming a separate entity.
                </p>

                <dl class="mt-10 space-y-6">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Bank Accounts & Contracts</dt>
                            <dd class="mt-1 text-zinc-600">Most banks require a filed DBA to open an account in your business name. Vendors and clients expect a registered trade name for contracts.</dd>
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <dt class="font-bold text-zinc-900">Legal Compliance</dt>
                            <dd class="mt-1 text-zinc-600">In most states and counties, operating under a name other than your legal name without filing is illegal. A DBA keeps you compliant.</dd>
                        </div>
                    </div>
                </dl>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-indigo-100 via-purple-50 to-pink-100 blur-2xl"></div>
                <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-zinc-900">Why You Need a DBA</h3>
                    <ul class="mt-6 space-y-3 text-zinc-600">
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Open a business bank account in your chosen name
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Build brand recognition with a memorable name
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Meet state and county legal requirements
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Run multiple brands under one entity
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-indigo-600">Simple Process</p>
            <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                File Your DBA in 3 Easy Steps
            </h2>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-lg font-bold text-white">1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Choose Your Location</h3>
                    <p class="mt-3 text-zinc-600">Select your state and county (or state-level filing). DBA requirements vary—we'll guide you to the correct jurisdiction.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Submit Your DBA Name</h3>
                    <p class="mt-3 text-zinc-600">Enter your desired trade name and business details. We'll check availability and prepare your filing.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We File & You're Set</h3>
                    <p class="mt-3 text-zinc-600">We file with the county clerk or state agency and provide your filed DBA. Some jurisdictions require newspaper publication—we'll help with that too.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-indigo-600">FAQ</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Frequently Asked Questions
            </h2>
        </div>

        <div class="mt-12 space-y-4">
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What is the difference between a DBA, fictitious name, and trade name?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    They mean the same thing. "DBA" (Doing Business As), "fictitious business name" (FBN), and "trade name" all refer to registering a name you do business under that differs from your legal entity name. Different states and counties use different terms.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do I file a DBA at the state or county level?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    It depends on your state. Some states file at the county level (e.g., California), others at the state level (e.g., Arizona, Florida), and some require both. We'll determine the correct filing location for your jurisdiction.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Does a DBA provide liability protection?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    No. A DBA only registers a name; it does not create a separate legal entity or provide liability protection. Sole proprietors and general partners remain personally liable. For asset protection, consider forming an LLC or corporation.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do I need to renew my DBA?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Many jurisdictions require DBA renewal every few years (often 5 years). We track renewal deadlines and can help you renew when the time comes.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can an LLC or corporation have a DBA?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes. LLCs and corporations often use DBAs to operate multiple brands or product lines under one entity. For example, "ABC Holdings LLC" might file DBAs for "Sunny Coffee Shop" and "Mountain Gear Outfitters."
                </div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-purple-500/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>

            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Ready to Register Your DBA?
                </h2>
                <p class="mt-6 text-lg text-zinc-300">
                    File your fictitious business name online. Fast, simple, and compliant in all 50 states.
                </p>
                <div class="mt-10">
                    <a href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-indigo-600/25 transition-all hover:bg-indigo-500 hover:shadow-xl sm:w-auto">
                        Get Started Now
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
