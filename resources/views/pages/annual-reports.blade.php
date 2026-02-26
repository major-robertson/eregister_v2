@extends('layouts.landing')

@section('title', 'Annual Report Filing Service | Stay Compliant in Every State')

@section('meta')
<meta name="description"
    content="Annual report filing service for LLCs and corporations. Meet state deadlines, maintain good standing, and avoid penalties. We file your annual reports in every state on time.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                    Never Miss an
                    <span class="text-blue-600">Annual Report</span>
                </h1>

                <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                    Stay compliant in every state. We track deadlines, file your annual reports on time, and help you maintain good standing. Avoid late fees, penalties, and administrative dissolution.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-blue-700">
                        File Your Report
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-blue-50 to-sky-50 opacity-60 blur-xl"></div>
                <div class="relative space-y-4">
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Deadline Tracking</h3>
                            <p class="text-sm text-zinc-500">We remind you before every due date</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Good Standing</h3>
                            <p class="text-sm text-zinc-500">Keep your entity in compliance</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">50-State Coverage</h3>
                            <p class="text-sm text-zinc-500">File in any state where you're registered</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- What Are Annual Reports --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-blue-600">Overview</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                What Are Annual Reports?
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                Annual reports (also called annual statements, franchise tax reports, or biennial reports in some states) are periodic filings required by state governments to keep your LLC, corporation, or nonprofit in good standing. They typically update your business address, registered agent, officers, members, and basic business information. Each state sets its own deadlines—often tied to the anniversary of your formation or the calendar year—and charges a filing fee. Missing a deadline can result in late fees, penalties, loss of good standing, and in severe cases, administrative dissolution.
            </p>
        </div>

        {{-- Why They Matter --}}
        <div class="mt-20">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">Why Annual Reports Matter</h2>
            <div class="mt-12 grid gap-8 md:grid-cols-2">
                <div class="flex gap-6 rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-zinc-900">Maintain Good Standing</h3>
                        <p class="mt-2 text-zinc-600">Banks, partners, and customers often verify your entity's good standing. Lenders may decline financing if you're not in compliance. Timely annual reports keep your business legitimate and credible.</p>
                    </div>
                </div>
                <div class="flex gap-6 rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-zinc-900">Avoid Penalties</h3>
                        <p class="mt-2 text-zinc-600">Late filings often incur penalties ranging from $50 to hundreds of dollars. Some states impose escalating fees the longer you're overdue. Filing on time saves money and prevents administrative headaches.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- How It Works --}}
        <div class="mt-24">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">How We Help</h2>
            <div class="mx-auto mt-12 max-w-2xl">
                <ul class="space-y-4 text-zinc-600">
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>We track your state-specific deadline (annual, biennial, or custom)</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>We send you reminder emails before the due date</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>We prepare and file the report with the state</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>We confirm filing and send you the state's confirmation</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- How It Works (3 Steps) --}}
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
                    <h3 class="text-xl font-bold text-zinc-900">Add Your Entity</h3>
                    <p class="mt-3 text-zinc-600">Provide your business name, state of registration, and EIN. We'll look up your record and identify your next filing deadline.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We Remind You</h3>
                    <p class="mt-3 text-zinc-600">You'll receive email reminders 60, 30, and 14 days before your deadline. No more last-minute rushes or forgotten filings.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We File & Confirm</h3>
                    <p class="mt-3 text-zinc-600">Submit your information, and we'll prepare, file, and pay the state fee. You'll get confirmation once the report is accepted.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
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
                    What happens if I miss my annual report deadline?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Most states impose late fees and penalties. If you remain non-compliant for an extended period, the state may revoke your entity's good standing or administratively dissolve it. Reinstatement often requires paying back fees, penalties, and filing a reinstatement application. It's best to file on time or as soon as possible after a missed deadline.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do all states require annual reports?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Most states require annual or biennial reports for LLCs and corporations. A few states (like Ohio for some entities) have different requirements. The due date varies by state—some use the anniversary of formation, others use the end of the calendar or fiscal year. We track deadlines for every state.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do I need to file annual reports in every state where I'm registered?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes. If you're registered as a foreign LLC or corporation in multiple states, you must file annual (or biennial) reports in each of those states. Each state has its own deadline and fee. We can manage filings across all states where you do business.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What information is typically required on an annual report?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Common information includes: principal address, registered agent name and address, officers or managers, number of members or shares, and sometimes gross receipts or franchise tax calculations. Requirements vary by state and entity type. We'll gather what's needed for your specific filing.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can you file my annual report if I'm already past due?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes. We can file overdue annual reports. You'll need to pay the normal filing fee plus any late fees or penalties imposed by the state. Filing as soon as possible limits additional penalties and helps restore your good standing.
                </div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-sky-500/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>

            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Stay Compliant, Stay in Good Standing
                </h2>
                <p class="mt-6 text-lg text-zinc-300">
                    Let us handle your annual reports. Never miss a deadline again.
                </p>
                <div class="mt-10">
                    <a href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-blue-600/25 transition-all hover:bg-blue-500 hover:shadow-xl sm:w-auto">
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
