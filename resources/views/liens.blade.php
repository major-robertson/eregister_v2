@extends('layouts.landing')

@section('title', 'eRegister - Mechanic Lien Filing Portal')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
        {{-- Subtle grid pattern --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')"></div>

        <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-amber-400"></span>
                </span>
                Available in all 50 states
            </div>

            <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-6xl lg:text-7xl">
                File liens.<br>
                <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Get paid.</span>
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400 sm:text-xl">
                The fastest way to file mechanic's liens, preliminary notices, and protect your payment rights. State-compliant forms for every state.
            </p>

            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 text-base font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50 hover:shadow-xl">
                    File a Lien Now
                    <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
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
                            <div class="text-xs font-medium uppercase tracking-wider text-zinc-400">Active Liens</div>
                            <div class="mt-1 text-3xl font-bold text-zinc-900">12</div>
                            <div class="mt-2 text-sm text-emerald-600">+3 this month</div>
                        </div>
                        <div class="rounded-xl bg-white p-4 shadow-sm">
                            <div class="text-xs font-medium uppercase tracking-wider text-zinc-400">Amount Protected</div>
                            <div class="mt-1 text-3xl font-bold text-zinc-900">$247K</div>
                        </div>
                        <div class="rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 p-4 text-white shadow-sm">
                            <div class="text-xs font-medium uppercase tracking-wider text-amber-100">Next Deadline</div>
                            <div class="mt-1 text-xl font-bold">Jan 28</div>
                            <div class="mt-1 text-sm text-amber-100">TX Mechanic's Lien</div>
                        </div>
                    </div>

                    {{-- Main content --}}
                    <div class="space-y-3 lg:col-span-2">
                        <div class="rounded-xl bg-white p-4 shadow-sm">
                            <div class="mb-4 flex items-center justify-between">
                                <div class="font-semibold text-zinc-900">Recent Filings</div>
                                <div class="rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600">View all</div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-medium text-zinc-900">Preliminary Notice - CA</div>
                                            <div class="text-sm text-zinc-500">Westfield Mall Project</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Sent</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-medium text-zinc-900">Mechanic's Lien - TX</div>
                                            <div class="text-sm text-zinc-500">Johnson Residence</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">Pending</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-medium text-zinc-900">Notice to Owner - FL</div>
                                            <div class="text-sm text-zinc-500">Sunrise Plaza</div>
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">Draft</span>
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
                        Everything you need to protect your payments
                    </h2>
                    <p class="mt-4 text-lg text-zinc-600">
                        Stop chasing payments. Our lien portal gives you the tools to file notices, track deadlines, and get paid for your work.
                    </p>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="group">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600 transition group-hover:bg-amber-500 group-hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="font-semibold text-zinc-900">Expert preparation</h3>
                        <p class="mt-1 text-sm text-zinc-600">Our team reviews and prepares your documents</p>
                    </div>

                    <div class="group">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 transition group-hover:bg-blue-500 group-hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="font-semibold text-zinc-900">Deadline tracking</h3>
                        <p class="mt-1 text-sm text-zinc-600">Never miss a filing deadline with smart reminders</p>
                    </div>

                    <div class="group">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition group-hover:bg-emerald-500 group-hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="font-semibold text-zinc-900">State-compliant</h3>
                        <p class="mt-1 text-sm text-zinc-600">Forms updated regularly to match current laws</p>
                    </div>

                    <div class="group">
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600 transition group-hover:bg-violet-500 group-hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="font-semibold text-zinc-900">Project tracking</h3>
                        <p class="mt-1 text-sm text-zinc-600">Manage all your liens in one place</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Document Types --}}
    <section class="border-y border-zinc-200 bg-zinc-50 py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-zinc-900">Every document you need</h2>
                <p class="mt-3 text-zinc-600">File any construction lien document in minutes</p>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                    <div class="text-2xl">📋</div>
                    <div class="mt-3 font-semibold text-zinc-900">Mechanic's Lien</div>
                    <div class="mt-1 text-sm text-zinc-500">Secure payment on any property</div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                    <div class="text-2xl">📨</div>
                    <div class="mt-3 font-semibold text-zinc-900">Preliminary Notice</div>
                    <div class="mt-1 text-sm text-zinc-500">Preserve your lien rights early</div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                    <div class="text-2xl">📬</div>
                    <div class="mt-3 font-semibold text-zinc-900">Notice to Owner</div>
                    <div class="mt-1 text-sm text-zinc-500">Notify owners of your involvement</div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                    <div class="text-2xl">⚠️</div>
                    <div class="mt-3 font-semibold text-zinc-900">Intent to Lien</div>
                    <div class="mt-1 text-sm text-zinc-500">Warn before filing</div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                    <div class="text-2xl">🔓</div>
                    <div class="mt-3 font-semibold text-zinc-900">Lien Waiver</div>
                    <div class="mt-1 text-sm text-zinc-500">Release rights upon payment</div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                    <div class="text-2xl">✅</div>
                    <div class="mt-3 font-semibold text-zinc-900">Lien Release</div>
                    <div class="mt-1 text-sm text-zinc-500">Remove filed liens</div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
                    <div class="text-2xl">🏛️</div>
                    <div class="mt-3 font-semibold text-zinc-900">Bond Claim</div>
                    <div class="mt-1 text-sm text-zinc-500">Public project payment claims</div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md">
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
                <h2 class="text-3xl font-bold text-zinc-900">Three steps to protect your payment</h2>
            </div>

            <div class="relative mt-16">
                {{-- Connector line behind circles --}}
                <div class="absolute top-7 hidden h-0.5 bg-zinc-300 lg:block" style="left: calc(16.67% + 1.75rem); right: calc(16.67% + 1.75rem);"></div>

                <div class="grid gap-8 lg:grid-cols-3">
                    <div class="relative text-center">
                        <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">1</div>
                        <h3 class="mt-6 text-xl font-semibold text-zinc-900">Enter project details</h3>
                        <p class="mt-2 text-zinc-600">Add the property address, owner info, and amount owed. Takes 2 minutes.</p>
                    </div>

                    <div class="relative text-center">
                        <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">2</div>
                        <h3 class="mt-6 text-xl font-semibold text-zinc-900">Select your state</h3>
                        <p class="mt-2 text-zinc-600">We automatically use the correct form for your state's requirements.</p>
                    </div>

                    <div class="text-center">
                        <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-white">3</div>
                        <h3 class="mt-6 text-xl font-semibold text-zinc-900">Download & file</h3>
                        <p class="mt-2 text-zinc-600">Receive your documents within 1-3 business days, or let us handle everything and file on your behalf.</p>
                    </div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-zinc-900 px-8 py-4 font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-zinc-800 hover:shadow-xl">
                    Start your first lien
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Stats --}}
    <section class="border-y border-zinc-200 bg-zinc-900 py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 text-center sm:grid-cols-2">
                <div>
                    <div class="text-4xl font-bold text-white">50</div>
                    <div class="mt-1 text-sm text-zinc-400">States covered</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-white">2 min</div>
                    <div class="mt-1 text-sm text-zinc-400">Average filing time</div>
                </div>
            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="py-24">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-3xl font-bold text-zinc-900">Common questions</h2>

            <div class="mt-12 space-y-4">
                <details class="group rounded-xl border border-zinc-200 bg-white">
                    <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                        What is a mechanic's lien?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                        A mechanic's lien is a legal claim against a property that secures payment for work performed or materials supplied. It gives you a security interest in the property you improved until you're paid.
                    </div>
                </details>

                <details class="group rounded-xl border border-zinc-200 bg-white">
                    <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                        Who can file a lien?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                        General contractors, subcontractors, material suppliers, equipment rental companies, and laborers who have provided work or materials to improve a property. Requirements vary by state.
                    </div>
                </details>

                <details class="group rounded-xl border border-zinc-200 bg-white">
                    <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                        What deadlines do I need to know?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                        Deadlines vary by state, typically 30-120 days after last furnishing labor or materials. Missing deadlines forfeits your lien rights. Our system tracks all deadlines automatically.
                    </div>
                </details>

                <details class="group rounded-xl border border-zinc-200 bg-white">
                    <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                        Are forms state-specific?
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">
                        Yes. Each state has different lien laws and requirements. We automatically use the correct form for your state and keep all forms updated with current laws.
                    </div>
                </details>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to protect your payment?</h2>
                <p class="mt-4 text-lg text-zinc-400">
                    File your first lien in under 5 minutes. No credit card required.
                </p>
                <a href="{{ route('register') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50 hover:shadow-xl">
                    File a Lien Now
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </section>
@endsection
