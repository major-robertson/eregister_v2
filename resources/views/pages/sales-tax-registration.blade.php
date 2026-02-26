@extends('layouts.landing')

@section('title', 'Sales & Use Tax Registration | Register for Sales Tax in Any State')

@section('meta')
<meta name="description" content="Register for sales and use tax permits in all 50 states. Our sales tax registration service handles state tax permits, nexus analysis, and compliance so you can focus on your business.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                Sales & Use Tax <span class="text-blue-600">Registration</span>
            </h1>
            <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                Stay compliant across every state. We handle sales tax permits, nexus analysis, and multi-state registration—so you can focus on growing your business instead of navigating state tax requirements.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-blue-700">
                    Get Started
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- What Is Sales Tax Registration --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Overview</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                What Is Sales Tax Registration?
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                Sales tax registration is the process of obtaining a sales tax permit (also called a seller's permit or resale permit in some states) from each state where your business has nexus—a legal obligation to collect and remit sales tax. Once registered, you receive a permit number that authorizes you to collect sales tax from customers and file periodic returns. Use tax applies when you purchase goods without paying sales tax but later use them in a taxable manner—registration often covers both sales and use tax obligations. Navigating registration across multiple states can be complex; our service streamlines the process and ensures you meet each state's requirements.
            </p>
        </div>

        {{-- Who Needs to Register --}}
        <div class="mt-20">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">Who Needs to Register?</h2>
            <div class="mt-12 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-zinc-900">Economic Nexus</h3>
                    <p class="mt-2 text-zinc-600">If your business sells goods or services into a state and exceeds that state's economic threshold (often $100,000 in sales or 200 transactions), you likely have economic nexus and must register for sales tax there—even with no physical presence.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-zinc-900">Physical Nexus</h3>
                    <p class="mt-2 text-zinc-600">If your business has a physical presence in a state—office, warehouse, employees, inventory, or agents—you typically have physical nexus and must register and collect sales tax in that state regardless of sales volume.</p>
                </div>
            </div>
        </div>

        {{-- What's Included --}}
        <div class="mt-20">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">What's Included</h2>
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">Nexus Analysis</h3>
                    <p class="mt-2 text-zinc-600">We analyze your business activities to determine where you have sales tax nexus and identify which states require registration.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">State Registration</h3>
                    <p class="mt-2 text-zinc-600">We prepare and submit your sales tax registration applications to each required state, tracking status until permits are issued.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">Compliance Support</h3>
                    <p class="mt-2 text-zinc-600">Guidance on filing frequencies, due dates, and ongoing compliance so you stay current with each state's sales tax rules.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-blue-600">Simple Process</p>
            <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                How It Works
            </h2>
        </div>
        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Share Your Business Details</h3>
                    <p class="mt-3 text-zinc-600">Provide your business information, sales channels, and where you sell. We use this to perform a nexus analysis and identify which states require registration.</p>
                </div>
            </div>
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We Handle the Applications</h3>
                    <p class="mt-3 text-zinc-600">We prepare and submit your sales tax registration applications to each required state. You'll receive updates as permits are processed and issued.</p>
                </div>
            </div>
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Receive Your Permits</h3>
                    <p class="mt-3 text-zinc-600">Once approved, you'll receive your sales tax permit numbers. We provide compliance guidance so you can collect, report, and remit sales tax correctly.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">Frequently Asked Questions</h2>
        </div>
        <div class="mt-12 space-y-4">
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What is sales tax nexus?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">Nexus is the connection between your business and a state that creates a tax obligation. Physical nexus comes from having employees, offices, warehouses, or inventory in a state. Economic nexus comes from exceeding sales or transaction thresholds—often $100,000 in sales or 200 transactions per year—even with no physical presence. Once you have nexus in a state, you must register for sales tax and collect it from customers in that state.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What are economic nexus thresholds by state?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">Thresholds vary by state. Most states use $100,000 in sales or 200 transactions (South Dakota v. Wayfair standard), but some differ—for example, California uses $500,000, Texas uses $500,000, and a few states use different formulas. Nexus rules also change over time. Our nexus analysis keeps current with each state's thresholds and helps you determine where registration is required.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do I need to register in every state where I sell?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">No—only in states where you have nexus. If you sell occasionally into a state and stay below economic thresholds with no physical presence, you may not need to register there yet. Once you exceed a state's threshold or establish physical presence, registration becomes required. We help you identify exactly which states apply to your situation.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long does sales tax registration take?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">Processing times vary by state. Some states issue permits within a few days; others can take 2–4 weeks or longer. Online registration is usually faster than paper applications. We submit applications promptly and track status so you know when to expect your permits.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can you help with multi-state sales tax compliance?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">Yes. We specialize in multi-state sales tax registration. Whether you need permits in a handful of states or across all 50, we handle the applications and help you stay compliant. Each state has different rules, deadlines, and filing frequencies—we guide you through them so you can focus on your business.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Ready to Get Started?</h2>
                <p class="mt-6 text-lg text-zinc-300">Register for sales tax in any state. Nexus analysis, state permits, and compliance support—all handled for you.</p>
                <div class="mt-10">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition-all hover:bg-blue-500 hover:shadow-xl">
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
