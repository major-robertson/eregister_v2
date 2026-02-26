@extends('layouts.landing')

@section('title', 'Payment Demand Letter | Construction Payment Demand Notice')

@section('meta')
<meta name="description" content="Send a professional payment demand letter for construction work. Our service creates legally effective demand letters to help contractors, subcontractors, and suppliers collect overdue payments.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            Available in all 50 states
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Send a Payment Demand Letter<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Collect Overdue Construction Payments</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">Professional payment demand letters for construction work. Our service creates legally effective demand notices that help contractors, subcontractors, and suppliers collect overdue payments—often before escalating to liens or litigation.</p>
        <div class="mt-10">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Get Started
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- What is a payment demand letter --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Payment Collection</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">What Is a Payment Demand Letter?</h2>
                <p class="mt-4 text-lg text-zinc-600">
                    A payment demand letter is a formal written notice sent to a party who owes you money for construction work, materials, or services. It clearly states the amount owed, the work performed, and the deadline for payment. A professional demand letter documents your claim, puts the debtor on notice, and often triggers payment without the need for liens, lawsuits, or collections.
                </p>
                <p class="mt-4 text-zinc-600">
                    For contractors, subcontractors, and material suppliers, a well-drafted payment demand letter is often the first serious step in the collection process. It creates a written record, demonstrates professionalism, and signals that you're prepared to take further action—such as filing a mechanics lien or pursuing legal remedies—if payment isn't received.
                </p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="font-semibold text-zinc-900">Why It Works</h3>
                <ul class="mt-4 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Creates a formal record of your claim
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Often triggers payment without escalation
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Essential pre-litigation step in many cases
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Professional tone increases credibility
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Why it works --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Why a Payment Demand Letter Works</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">A professional demand letter is one of the most effective tools for collecting construction payments.</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Formal Record</h3>
                <p class="mt-2 text-zinc-600">A written demand creates a documented record of your claim, the amount owed, and when it was due. This is valuable if you later need to file a mechanics lien or lawsuit.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Pre-Litigation Step</h3>
                <p class="mt-2 text-zinc-600">Courts and many state laws expect you to make a formal demand before filing suit. A payment demand letter satisfies this requirement and shows you've given the debtor a chance to pay.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Often Triggers Payment</h3>
                <p class="mt-2 text-zinc-600">Many payers respond when they receive a professional demand. It signals seriousness and often prompts payment to avoid liens, legal action, or damage to their reputation.</p>
            </div>
        </div>
    </div>
</section>

{{-- What's included --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">What's Included in a Payment Demand Letter</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Our demand letters are comprehensive and legally effective.</p>
        </div>
        <div class="mt-12 grid gap-4 sm:grid-cols-2">
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Clear Amount Owed</h3>
                    <p class="mt-1 text-sm text-zinc-600">Detailed breakdown of the unpaid balance, including dates, invoices, and work description.</p>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Payment Deadline</h3>
                    <p class="mt-1 text-sm text-zinc-600">Reasonable deadline (typically 7–14 days) for payment before further action.</p>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Notice of Consequences</h3>
                    <p class="mt-1 text-sm text-zinc-600">Professional language indicating possible next steps—mechanics lien, intent to lien, or legal action—if payment isn't received.</p>
                </div>
            </div>
            <div class="flex items-start gap-4 rounded-xl border border-zinc-200 bg-zinc-50 p-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-zinc-900">Proof of Delivery</h3>
                    <p class="mt-1 text-sm text-zinc-600">We send your demand letter via certified mail or other verifiable method, with proof of delivery for your records.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">How It Works</h2>
            <p class="mt-3 text-zinc-600">Send your payment demand letter in three simple steps</p>
        </div>
        <div class="relative mt-16">
            <div class="absolute top-7 hidden h-0.5 bg-zinc-300 lg:block" style="left: calc(16.67% + 1.75rem); right: calc(16.67% + 1.75rem);"></div>
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">1</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">Provide invoice and project details</h3>
                    <p class="mt-2 text-zinc-600">Share the amount owed, work performed, debtor information, and any relevant contract or invoice details.</p>
                </div>
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">2</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We draft your demand letter</h3>
                    <p class="mt-2 text-zinc-600">Our team creates a professional, legally effective payment demand letter tailored to construction collections and your situation.</p>
                </div>
                <div class="text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-white">3</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We send it for you</h3>
                    <p class="mt-2 text-zinc-600">We deliver your payment demand letter to the debtor with proof of service. You'll receive confirmation and a copy for your records.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Frequently Asked Questions</h2>
        </div>
        <div class="mt-12 space-y-4">
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    When should I send a payment demand letter?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Send a payment demand letter when payment is past due and informal reminders haven't worked. It's often the logical next step before filing a mechanics lien, sending an intent to lien notice, or pursuing litigation. Sending early can prevent escalation and preserve business relationships while still protecting your rights.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is a payment demand letter required before filing a mechanics lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">It depends on the state. Some states require a notice of intent to lien (which is a type of demand) before filing a mechanics lien; a general payment demand letter may or may not satisfy that. Our service can prepare either a payment demand letter or an intent to lien notice, depending on your goals and state requirements.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Who can send a payment demand letter?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Anyone owed money for construction work can send a payment demand letter—general contractors, subcontractors, material suppliers, equipment rental companies, architects, engineers, and laborers. It's a universal collection tool that works across the construction payment chain.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long should I give the debtor to pay?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">A typical payment deadline is 7 to 14 days. This gives the debtor a reasonable opportunity to pay while maintaining urgency. If your state requires a notice of intent to lien before filing a mechanics lien, that notice may have a statutorily mandated waiting period (e.g., 10–30 days). We'll set the appropriate deadline for your situation.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What if they still don't pay after the demand letter?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">The next steps typically include sending a notice of intent to lien (if required by your state) and then filing a mechanics lien. You may also consider collections, arbitration, or litigation. Our platform can help you proceed with intent to lien notices and mechanics lien filings—your demand letter establishes the paper trail and demonstrates you gave the debtor a chance to pay.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to Send Your Payment Demand?</h2>
            <p class="mt-4 text-lg text-zinc-400">Professional payment demand letters for construction work. Collect overdue payments with a formal, legally effective demand notice.</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50">
                    Get Started
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
