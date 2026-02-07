@extends('layouts.landing')

@section('title', $pageTitle ?? 'Mechanics Lien Filing Services | File a Construction Lien on Property')

@section('meta')
@if (!empty($noIndex))
<meta name="robots" content="noindex, nofollow" />
@endif
@if (!empty($canonicalUrl))
<link rel="canonical" href="{{ $canonicalUrl }}" />
@endif
<meta name="description"
    content="File a mechanics lien on property with our trusted construction lien filing services. Expert mechanics lien filing for contractors, subcontractors & suppliers. File a construction lien in all 50 states.">
<meta name="keywords"
    content="mechanics lien filing, file construction lien, mechanics lien on property, file a mechanics lien, contractor lien on property, file a construction lien, construction lien filing, construction lien services">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    {{-- Subtle grid pattern --}}
    <div class="absolute inset-0 opacity-[0.03]"
        style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')">
    </div>

    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div
            class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            <span class="relative flex h-2 w-2">
                <span
                    class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-400"></span>
            </span>
            Available in all 50 states
        </div>

        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
            File liens.<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Get paid.</span>
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400 sm:text-xl">
            @if (!empty($lead) && $lead->business_name)
            {{ $lead->business_name }}, file a mechanics lien on property with confidence. Our construction lien filing
            services help contractors, subcontractors, and suppliers protect their payment rights with state-compliant
            forms.
            @else
            File a mechanics lien on property with confidence. Our construction lien filing services help contractors,
            subcontractors, and suppliers protect their payment rights with state-compliant forms.
            @endif
        </p>

        {{-- Hero CTA --}}
        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('register') }}"
                class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C] hover:shadow-xl">
                Start free lien tracking
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>

        @if (!empty($lead))

        {{-- Project card --}}
        @if ($lead->property_address)
        <div
            class="mx-auto mt-10 max-w-md overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-800/60 text-left shadow-xl backdrop-blur-sm">
            <div class="border-b border-zinc-700 px-6 py-3">
                <p class="text-xs font-medium uppercase tracking-wider text-amber-400">Your recent job</p>
            </div>
            <div class="space-y-3 px-6 py-5">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-500/10">
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ $lead->property_address }}</p>
                        <p class="text-sm text-zinc-400">{{ $lead->property_city }}, {{ $lead->property_state }}</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2 border-t border-zinc-700 px-6 py-4 sm:flex-row">
                <a href="{{ route('register') }}"
                    class="group inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-zinc-900 transition hover:bg-amber-400">
                    Track this job free
                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
                <a href="{{ route('register') }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg border border-zinc-600 px-4 py-2.5 text-sm font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white">
                    File a lien / send notice
                </a>
            </div>
        </div>
        @endif
        @endif
    </div>
</section>

{{-- Dashboard Preview --}}
<section id="demo" class="relative -mt-16 px-4 pb-20 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-2xl shadow-zinc-900/10">
            {{-- Browser chrome --}}
            <div class="flex items-center gap-2 border-b border-zinc-100 bg-zinc-50 px-4 py-3">
                <div class="flex gap-1.5">
                    <div class="h-3 w-3 rounded-full bg-zinc-300"></div>
                    <div class="h-3 w-3 rounded-full bg-zinc-300"></div>
                    <div class="h-3 w-3 rounded-full bg-zinc-300"></div>
                </div>
                <div class="ml-4 flex-1 rounded-md bg-zinc-100 px-3 py-1.5 text-xs text-zinc-400">
                    eregister.com/liens
                </div>
            </div>

            {{-- Dashboard content placeholder --}}
            <div class="grid gap-6 bg-zinc-50/50 p-6 lg:grid-cols-3">
                {{-- Sidebar --}}
                <div class="space-y-3">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wider text-zinc-400">Active Mechanics Liens
                        </div>
                        <div class="mt-1 text-3xl font-bold text-zinc-900">12</div>
                        <div class="mt-2 text-sm text-emerald-600">+3 this month</div>
                    </div>
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wider text-zinc-400">Amount Protected</div>
                        <div class="mt-1 text-3xl font-bold text-zinc-900">$247K</div>
                    </div>
                    <div class="rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-wider text-amber-100">Construction Lien
                            Deadline</div>
                        <div class="mt-1 text-xl font-bold">Jan 28</div>
                        <div class="mt-1 text-sm text-amber-100">TX Mechanics Lien Filing</div>
                    </div>
                </div>

                {{-- Main content --}}
                <div class="space-y-3 lg:col-span-2">
                    <div class="rounded-xl bg-white p-4 shadow-sm">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="font-semibold text-zinc-900">Recent Construction Lien Filings</div>
                            <div class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600">View all
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900">Preliminary Notice - CA</div>
                                        <div class="text-sm text-zinc-500">Westfield Mall Project</div>
                                    </div>
                                </div>
                                <span
                                    class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Sent</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900">Mechanics Lien Filing - TX</div>
                                        <div class="text-sm text-zinc-500">Johnson Residence</div>
                                    </div>
                                </div>
                                <span
                                    class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Pending</span>
                            </div>
                            <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900">Contractor Lien - FL</div>
                                        <div class="text-sm text-zinc-500">Sunrise Plaza</div>
                                    </div>
                                </div>
                                <span
                                    class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">Draft</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <h2 class="text-3xl font-bold text-zinc-900 sm:text-4xl">
                    Professional construction lien filing services
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    Stop chasing payments. Whether you need to file a mechanics lien on property or file a construction
                    lien for unpaid work, our platform gives you everything you need to protect your rights and get
                    paid.
                </p>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div class="group">
                    <div
                        class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 transition group-hover:bg-amber-500 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-zinc-900">Expert lien preparation</h3>
                    <p class="mt-1 text-sm text-zinc-600">Our team reviews and prepares your mechanics lien filing
                        documents</p>
                </div>

                <div class="group">
                    <div
                        class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 transition group-hover:bg-blue-500 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-zinc-900">Construction lien deadlines</h3>
                    <p class="mt-1 text-sm text-zinc-600">Never miss a construction lien filing deadline with smart
                        reminders</p>
                </div>

                <div class="group">
                    <div
                        class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition group-hover:bg-emerald-500 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-zinc-900">State-compliant forms</h3>
                    <p class="mt-1 text-sm text-zinc-600">Contractor lien on property forms updated to match current
                        laws</p>
                </div>

                <div class="group">
                    <div
                        class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600 transition group-hover:bg-violet-500 group-hover:text-white">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="font-semibold text-zinc-900">Project & lien tracking</h3>
                    <p class="mt-1 text-sm text-zinc-600">Manage all your mechanics lien filings in one place</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Document Types --}}
<section id="services" class="border-y border-zinc-200 bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Complete construction lien services</h2>
            <p class="mt-3 text-zinc-600">File a construction lien or any related document in minutes with our mechanics
                lien filing platform</p>
        </div>

        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">📋</div>
                <div class="mt-3 font-semibold text-zinc-900">Mechanics Lien Filing</div>
                <div class="mt-1 text-sm text-zinc-500">File a mechanics lien on property to secure payment</div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">📨</div>
                <div class="mt-3 font-semibold text-zinc-900">Preliminary Notice</div>
                <div class="mt-1 text-sm text-zinc-500">Preserve your construction lien rights early</div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">📬</div>
                <div class="mt-3 font-semibold text-zinc-900">Notice to Owner</div>
                <div class="mt-1 text-sm text-zinc-500">Notify property owners of your work</div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">⚠️</div>
                <div class="mt-3 font-semibold text-zinc-900">Intent to Lien</div>
                <div class="mt-1 text-sm text-zinc-500">Warn before you file a construction lien</div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">🔓</div>
                <div class="mt-3 font-semibold text-zinc-900">Lien Waiver</div>
                <div class="mt-1 text-sm text-zinc-500">Release contractor lien on property upon payment</div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">✅</div>
                <div class="mt-3 font-semibold text-zinc-900">Lien Release</div>
                <div class="mt-1 text-sm text-zinc-500">Remove filed mechanics liens from property</div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">🏛️</div>
                <div class="mt-3 font-semibold text-zinc-900">Bond Claim</div>
                <div class="mt-1 text-sm text-zinc-500">Public project payment claims</div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                <div class="text-2xl">🏁</div>
                <div class="mt-3 font-semibold text-zinc-900">Notice of Completion</div>
                <div class="mt-1 text-sm text-zinc-500">Document project completion</div>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">How to file a mechanics lien in three steps</h2>
            <p class="mt-3 text-zinc-600">Our construction lien filing process is simple and fast</p>
        </div>

        <div class="relative mt-16">
            {{-- Connector line behind circles --}}
            <div class="absolute top-7 hidden h-0.5 bg-zinc-300 lg:block"
                style="left: calc(16.67% + 1.75rem); right: calc(16.67% + 1.75rem);"></div>

            <div class="grid gap-8 lg:grid-cols-3">
                <div class="relative text-center">
                    <div
                        class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">
                        1</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">Enter property & project details</h3>
                    <p class="mt-2 text-zinc-600">Provide the property address, owner info, and amount owed for your
                        mechanics lien on property. Takes 2 minutes.</p>
                </div>

                <div class="relative text-center">
                    <div
                        class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">
                        2</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">Select your state</h3>
                    <p class="mt-2 text-zinc-600">We automatically use the correct construction lien filing form for
                        your state's specific requirements.</p>
                </div>

                <div class="text-center">
                    <div
                        class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-white">
                        3</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We file your construction lien</h3>
                    <p class="mt-2 text-zinc-600">Receive your documents within 1-3 business days, or let us handle
                        everything and file your contractor lien on property for you.</p>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('register') }}"
                class="inline-flex items-center gap-2 rounded-full bg-zinc-900 px-8 py-4 font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-zinc-800 hover:shadow-xl">
                File a Mechanics Lien Now
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="border-y border-zinc-200 bg-zinc-900 py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 text-center sm:grid-cols-4">
            <div>
                <div class="text-4xl font-bold text-white">50</div>
                <div class="mt-1 text-sm text-zinc-400">States covered</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-white">2 min</div>
                <div class="mt-1 text-sm text-zinc-400">Average filing time</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-white">10K+</div>
                <div class="mt-1 text-sm text-zinc-400">Liens filed</div>
            </div>
            <div>
                <div class="text-4xl font-bold text-white">99%</div>
                <div class="mt-1 text-sm text-zinc-400">Success rate</div>
            </div>
        </div>
    </div>
</section>

{{-- Who Can File a Mechanics Lien --}}
<section class="py-24 bg-white">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Who can file a mechanics lien on property?</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">
                Our construction lien services help anyone in the construction industry protect their payment rights by
                filing a mechanics lien.
            </p>
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <div
                    class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900">General Contractors</h3>
                <p class="mt-2 text-zinc-600">File a contractor lien on property when clients don't pay for completed
                    construction work.</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <div
                    class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900">Subcontractors</h3>
                <p class="mt-2 text-zinc-600">File a construction lien to secure payment even when working under a
                    general contractor.</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <div
                    class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900">Material Suppliers</h3>
                <p class="mt-2 text-zinc-600">Use mechanics lien filing to protect payment for materials supplied to
                    construction projects.</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <div
                    class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900">Equipment Rental</h3>
                <p class="mt-2 text-zinc-600">File a mechanics lien on property for unpaid equipment rentals used on
                    construction sites.</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <div
                    class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900">Architects & Engineers</h3>
                <p class="mt-2 text-zinc-600">Construction lien filing services for design professionals owed for plans
                    and specifications.</p>
            </div>

            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <div
                    class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-cyan-100 text-cyan-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-zinc-900">Laborers</h3>
                <p class="mt-2 text-zinc-600">Workers can file a mechanics lien for unpaid wages on construction
                    projects.</p>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-24 bg-zinc-50">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Frequently asked questions about mechanics lien filing</h2>
            <p class="mt-3 text-zinc-600">Everything you need to know about how to file a mechanics lien on property</p>
        </div>

        <div class="mt-12 space-y-4">
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What is a mechanics lien and how does mechanics lien filing work?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    A mechanics lien (also called a construction lien or contractor lien) is a legal claim against a
                    property that secures payment for work performed or materials supplied. When you file a mechanics
                    lien on property, it creates a security interest that remains until you're paid. Our mechanics lien
                    filing services handle the entire process, from document preparation to recording with the county.
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Who can file a mechanics lien on property?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    Anyone who provides labor, materials, or services to improve real property can file a mechanics
                    lien. This includes general contractors, subcontractors, material suppliers, equipment rental
                    companies, architects, engineers, and laborers. Our construction lien services help all construction
                    industry professionals file a contractor lien on property when payment is overdue.
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How do I file a construction lien? What's the process?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    To file a construction lien, you'll need to: (1) Send preliminary notices if required by your state,
                    (2) Prepare the mechanics lien document with property and project details, (3) Record the lien with
                    the county recorder, and (4) Serve notice to the property owner. Our construction lien filing
                    services handle all these steps for you, ensuring your mechanics lien filing is done correctly and
                    on time.
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What are the deadlines to file a mechanics lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    Construction lien filing deadlines vary by state, typically ranging from 30-120 days after you last
                    provided labor or materials. Missing these deadlines means losing your right to file a mechanics
                    lien on property. Our platform automatically tracks all deadlines for your mechanics lien filing to
                    ensure you never miss a critical date.
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Are construction lien forms different in each state?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    Yes, every state has different mechanics lien laws and requirements. Some states require specific
                    language, notarization, or preliminary notices before you can file a construction lien. Our
                    construction lien services automatically use the correct state-specific forms, so your mechanics
                    lien filing meets all legal requirements.
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How much does it cost to file a mechanics lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    The cost to file a mechanics lien on property includes our service fee plus county recording fees,
                    which vary by location. Our construction lien filing services provide transparent, upfront pricing
                    with no hidden fees. The cost is typically far less than the amount you'll recover by filing a
                    contractor lien on property.
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between a mechanics lien and a construction lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    Mechanics lien and construction lien are different terms for the same legal tool. Some states call
                    it a "mechanic's lien," others use "construction lien" or "contractor lien on property." Regardless
                    of the name, the purpose is the same: to secure payment for work performed on real property. Our
                    mechanics lien filing and construction lien services cover all variations.
                </div>
            </details>

            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary
                    class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I file a mechanics lien as a subcontractor?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                    Yes, subcontractors absolutely can file a mechanics lien on property, even if they don't have a
                    direct contract with the property owner. This is one of the most powerful protections in
                    construction law. Our construction lien filing services help subcontractors file a construction lien
                    to secure payment, even when working under a general contractor who hasn't paid.
                </div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to file a mechanics lien?</h2>
            <p class="mt-4 text-lg text-zinc-400">
                Use our construction lien filing services to protect your payment rights. File a mechanics lien on
                property in under 5 minutes.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50 hover:shadow-xl">
                    File a Construction Lien Now
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
            <p class="mt-6 text-sm text-zinc-500">No credit card required. Trusted by 10,000+ contractors and suppliers.
            </p>
        </div>
    </div>
</section>

{{-- Why Choose Us --}}
<section class="border-t border-zinc-200 bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Why choose our mechanics lien filing services?</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">
                When you need to file a mechanics lien on property, having the right construction lien services makes
                all the difference.
            </p>
        </div>

        <div class="mt-12 grid gap-8 lg:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center">
                <div
                    class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Expert Construction Lien Filing</h3>
                <p class="mt-3 text-zinc-600">
                    Whether you're a contractor looking to place a contractor lien on property or a subcontractor
                    needing construction lien filing assistance, our team has the expertise to help.
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center">
                <div
                    class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">All 50 States Covered</h3>
                <p class="mt-3 text-zinc-600">
                    File a construction lien in any state with confidence. Our mechanics lien filing platform uses
                    state-specific forms and follows each jurisdiction's unique requirements.
                </p>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8 text-center">
                <div
                    class="mx-auto mb-4 inline-flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Complete Lien Services</h3>
                <p class="mt-3 text-zinc-600">
                    Our construction lien services include document preparation, deadline tracking, and full filing
                    support to ensure your mechanics lien on property is valid and enforceable.
                </p>
            </div>
        </div>

        <div class="mt-12 rounded-2xl bg-zinc-900 p-8 text-center lg:p-12">
            <p class="text-lg text-zinc-300">
                Don't let unpaid invoices hurt your business. <span class="font-semibold text-white">File a mechanics
                    lien today</span> and secure the payment you've earned. Our platform helps you file a contractor
                lien on property quickly and correctly.
            </p>
            <a href="{{ route('register') }}"
                class="mt-6 inline-flex items-center gap-2 rounded-full bg-amber-500 px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-amber-400 hover:shadow-xl">
                Start Your Mechanics Lien Filing
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>
@endsection