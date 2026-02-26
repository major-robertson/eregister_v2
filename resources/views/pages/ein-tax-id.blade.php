@extends('layouts.landing')

@section('title', 'Get an EIN / Tax ID Number | Federal Tax ID Application')

@section('meta')
<meta name="description"
    content="Apply for an Employer Identification Number (EIN) or federal tax ID. Required for LLCs, corporations, and employers. Fast, simple application process. Get your EIN today.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                    Get Your EIN
                    <span class="text-emerald-600">Fast</span>
                </h1>

                <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                    Apply for your Employer Identification Number (EIN)—your business's federal tax ID. Required for opening bank accounts, hiring employees, and filing business taxes. Simple application, quick turnaround.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-emerald-700">
                        Apply for EIN
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-emerald-50 to-teal-50 opacity-60 blur-xl"></div>
                <div class="relative space-y-4">
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">IRS-Approved</h3>
                            <p class="text-sm text-zinc-500">Official federal tax ID from the IRS</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Quick Turnaround</h3>
                            <p class="text-sm text-zinc-500">Often received same day when applied online</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Open Accounts</h3>
                            <p class="text-sm text-zinc-500">Required for business banking & hiring</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- What Is an EIN --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">Overview</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                What Is an EIN?
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                An Employer Identification Number (EIN) is a nine-digit federal tax ID issued by the IRS. Also called a Federal Tax ID or FEIN, it identifies your business for tax purposes. An EIN functions like a Social Security number for your business—banks, vendors, and the IRS use it to track your entity. Sole proprietors can use their SSN for some purposes, but LLCs, corporations, partnerships, and nonprofits typically need an EIN. Getting one is free directly from the IRS; we simplify the application process and guide you through each step.
            </p>
        </div>

        {{-- Who Needs One --}}
        <div class="mt-20">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">Who Needs an EIN?</h2>
            <div class="mt-12 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-zinc-900">Required</h3>
                    <ul class="mt-3 space-y-2 text-zinc-600">
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> LLCs with multiple members</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Corporations (C Corp, S Corp)</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Partnerships</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Nonprofits</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Businesses with employees</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Trusts and estates</li>
                    </ul>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-zinc-900">Recommended</h3>
                    <ul class="mt-3 space-y-2 text-zinc-600">
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Single-member LLCs (for banking, privacy)</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Sole proprietors planning to hire</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Businesses opening a business bank account</li>
                        <li class="flex gap-2"><span class="text-emerald-600">•</span> Anyone wanting to avoid using SSN on business forms</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- How to Apply (3 Steps) --}}
        <div class="mt-24">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">How to Apply</h2>
            <div class="mt-12 grid gap-8 md:grid-cols-3">
                <div class="relative rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="absolute -top-4 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">1</div>
                    <div class="pt-6">
                        <h3 class="font-bold text-zinc-900">Gather Information</h3>
                        <p class="mt-2 text-zinc-600">You'll need your legal business name, address, responsible party (SSN/ITIN), entity type, and formation date. We'll guide you through each field.</p>
                    </div>
                </div>
                <div class="relative rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="absolute -top-4 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">2</div>
                    <div class="pt-6">
                        <h3 class="font-bold text-zinc-900">Complete the Application</h3>
                        <p class="mt-2 text-zinc-600">Answer our simple questions. We'll format your responses for the IRS and submit the application on your behalf—or you can apply directly with the IRS for free.</p>
                    </div>
                </div>
                <div class="relative rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="absolute -top-4 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">3</div>
                    <div class="pt-6">
                        <h3 class="font-bold text-zinc-900">Receive Your EIN</h3>
                        <p class="mt-2 text-zinc-600">Online applications are often processed immediately. You'll receive your EIN confirmation and can start using it for banking, hiring, and tax filings right away.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">Simple Process</p>
            <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                Get Your EIN in 3 Steps
            </h2>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Provide Business Details</h3>
                    <p class="mt-3 text-zinc-600">Enter your legal name, address, entity type, and responsible party information. Our form is designed to match IRS requirements.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We Submit to the IRS</h3>
                    <p class="mt-3 text-zinc-600">We process your application and submit it to the IRS. You'll get updates at each step so you know exactly where things stand.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Receive Your Number</h3>
                    <p class="mt-3 text-zinc-600">Once approved, you'll receive your EIN immediately. Save it securely—you'll need it for taxes, banking, and business operations.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">FAQ</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Frequently Asked Questions
            </h2>
        </div>

        <div class="mt-12 space-y-4">
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is an EIN the same as a tax ID?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes. EIN (Employer Identification Number), Federal Tax ID, and FEIN (Federal Employer Identification Number) all refer to the same nine-digit number issued by the IRS. Some people also use "business tax ID" loosely to mean the same thing.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long does it take to get an EIN?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Online applications through the IRS are typically processed immediately—you can receive your EIN the same day. Fax and mail applications take longer, often 4–6 weeks. We focus on the fastest path so you can start using your EIN quickly.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Does a single-member LLC need an EIN?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    The IRS does not require single-member LLCs (disregarded entities) to have an EIN—you can use your SSN for tax purposes. However, banks usually require an EIN to open a business account, and having one keeps your SSN off vendor forms and contracts. We recommend getting an EIN for any LLC.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I get a new EIN if I already have one?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Generally, the IRS assigns one EIN per entity. You may get a new EIN if you incorporate an LLC, change entity type, or have certain ownership changes. If you're unsure whether you need a new EIN, consult a tax professional or the IRS guidelines.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is there a fee to get an EIN?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    The IRS does not charge a fee for EIN applications. You can apply directly at irs.gov for free. Our service provides guidance, ensures accuracy, and streamlines the process—there may be a convenience fee for our assistance.
                </div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-emerald-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-teal-500/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>

            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Get Your EIN Today
                </h2>
                <p class="mt-6 text-lg text-zinc-300">
                    Fast, simple federal tax ID application. Start banking, hiring, and operating with confidence.
                </p>
                <div class="mt-10">
                    <a href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-emerald-600/25 transition-all hover:bg-emerald-500 hover:shadow-xl sm:w-auto">
                        Apply Now
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
