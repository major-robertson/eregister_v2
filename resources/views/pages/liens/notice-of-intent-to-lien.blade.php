@extends('layouts.landing')

@section('title', 'Notice of Intent to Lien | Send a Lien Intent Notice')

@section('meta')
<meta name="description" content="Send a notice of intent to lien before filing a mechanics lien. Our service prepares and delivers intent to lien notices that often get you paid before a lien needs to be filed. Available in all 50 states.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            Available in all 50 states
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Send a Notice of Intent to Lien<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Often Paid Before Filing a Lien</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">Send a formal intent to lien notice before filing a mechanics lien. We prepare and deliver state-compliant notices that often trigger payment without ever needing to file a construction lien.</p>
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

{{-- What is a notice of intent --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Lien Process</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">What Is a Notice of Intent to Lien?</h2>
                <p class="mt-4 text-lg text-zinc-600">
                    A notice of intent to lien (NOI) is a formal document sent to the property owner, general contractor, and sometimes the lender before you actually file a mechanics lien. It states that you haven't been paid, specifies the amount owed, and warns that you will file a construction lien if payment isn't received by a certain date.
                </p>
                <p class="mt-4 text-zinc-600">
                    In some states, sending a notice of intent to lien is legally required before you can file a mechanics lien. But even when it's optional, sending one is a smart strategy—many contractors and property owners pay up as soon as they receive it, avoiding the need to file a lien altogether.
                </p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="font-semibold text-zinc-900">Key Benefits</h3>
                <ul class="mt-4 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Often gets you paid without filing a lien
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Shows seriousness and protects your rights
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Required in some states before mechanics lien filing
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Professional, state-compliant format
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Why send one --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Why Send a Notice of Intent to Lien?</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">A well-crafted intent to lien notice is often the last step before payment—or before filing a mechanics lien.</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Often Gets You Paid</h3>
                <p class="mt-2 text-zinc-600">Many property owners and general contractors pay immediately upon receiving an intent to lien. It signals you're serious and will file a construction lien if they don't pay—avoiding the hassle and cost of a lien for everyone.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Required in Some States</h3>
                <p class="mt-2 text-zinc-600">Several states require a notice of intent to lien before you can file a mechanics lien. Sending one ensures compliance and protects your right to file a construction lien if payment still doesn't come.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Shows Seriousness</h3>
                <p class="mt-2 text-zinc-600">A formal intent to lien notice elevates your payment request. It demonstrates you understand construction lien law and are prepared to take the next step. Many payers respond quickly to avoid a lien on their property.</p>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">How It Works</h2>
            <p class="mt-3 text-zinc-600">Send your notice of intent to lien in three simple steps</p>
        </div>
        <div class="relative mt-16">
            <div class="absolute top-7 hidden h-0.5 bg-zinc-300 lg:block" style="left: calc(16.67% + 1.75rem); right: calc(16.67% + 1.75rem);"></div>
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">1</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">Provide project and amount owed</h3>
                    <p class="mt-2 text-zinc-600">Enter the property address, parties involved, work performed, and the unpaid balance. We'll use this to draft your intent to lien notice.</p>
                </div>
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">2</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We prepare your notice</h3>
                    <p class="mt-2 text-zinc-600">Our team creates a state-compliant notice of intent to lien with the correct format, deadlines, and required recipient information.</p>
                </div>
                <div class="text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-white">3</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We deliver it for you</h3>
                    <p class="mt-2 text-zinc-600">We send your intent to lien notice to the property owner, general contractor, and any other required parties with proof of delivery.</p>
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
                    Is a notice of intent to lien required before filing a mechanics lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">It depends on the state. Some states require a notice of intent to lien before you can file a mechanics lien; others don't. Our service checks your state's requirements and prepares the correct notice if needed. Even when not required, sending one is often effective at triggering payment.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long do I have to wait after sending an intent to lien before filing?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">States that require a notice of intent to lien typically mandate a waiting period—often 10 to 30 days—after sending it before you can file a mechanics lien. This gives the payer time to respond. Our notices include the appropriate deadline based on your state's laws.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Who should receive a notice of intent to lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Typically the property owner and the party who hired you (general contractor or subcontractor) must receive the notice. Some states also require service on the construction lender. We identify the correct recipients for your state and ensure proper delivery.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I send an intent to lien if I'm a subcontractor?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes. Subcontractors, material suppliers, and other lower-tier parties can and often must send notices of intent to lien. If you've sent a proper preliminary notice and still aren't paid, an intent to lien is typically the next step before filing a mechanics lien on the property.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Does an intent to lien create a lien on the property?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">No. A notice of intent to lien is a warning, not a lien. It doesn't create a lien on the property or get recorded in public records. Only when you file a mechanics lien with the county recorder does a lien attach to the property. The intent notice is the step before—it often results in payment so you never need to file.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Ready to Send Your Intent to Lien?</h2>
            <p class="mt-4 text-lg text-zinc-400">Get paid faster. Send a notice of intent to lien in any state—often the last step before payment or mechanics lien filing.</p>
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
