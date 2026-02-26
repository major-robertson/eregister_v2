@extends('layouts.landing')

@section('title', 'Lien Release | File a Mechanics Lien Release or Cancellation')

@section('meta')
<meta name="description" content="Release or cancel a mechanics lien after payment. Our lien release service handles the paperwork to remove construction liens from property records in all 50 states.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            Available in all 50 states
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Release a Mechanics Lien<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Remove Liens From Property</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">Remove a mechanics lien from property records after payment or settlement. Our lien release service prepares and files the paperwork to clear construction liens in all 50 states.</p>
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

{{-- What is a lien release --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Lien Removal</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">What Is a Lien Release?</h2>
                <p class="mt-4 text-lg text-zinc-600">
                    A lien release (or lien waiver release, lien discharge, or mechanics lien release) is a document that removes or cancels a previously filed mechanics lien from property records. Once you've been paid, settled a dispute, or resolved the underlying debt, you typically need to file a lien release to clear the property's title.
                </p>
                <p class="mt-4 text-zinc-600">
                    Property owners, lenders, and buyers need clear title to sell or refinance. A lien that remains on the books after payment can block closings and create legal headaches. Our service prepares state-compliant lien release documents and handles the filing process so the property is cleared properly.
                </p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="font-semibold text-zinc-900">When to File a Lien Release</h3>
                <ul class="mt-4 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        After full payment for work or materials
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        After a settlement or negotiated agreement
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Pursuant to court order or legal resolution
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        When lien was filed in error and needs cancellation
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- When to file one --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">When to File a Lien Release</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Timing matters. File a lien release when the underlying obligation is satisfied—or when required by law or agreement.</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">After Payment</h3>
                <p class="mt-2 text-zinc-600">Once you receive full payment for the work or materials underlying the lien, you should release the mechanics lien so the property owner can clear title.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">After Settlement</h3>
                <p class="mt-2 text-zinc-600">If you reach a negotiated settlement or agreement with the property owner or other party, a lien release is often part of the deal—releasing the lien in exchange for payment or other terms.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M3 6l6.063.75M3 6v12a2 2 0 002 2h12a2 2 0 002-2V6" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Court Order</h3>
                <p class="mt-2 text-zinc-600">In some cases, a court may order a lien release (e.g., when a lien is invalid or the claim is dismissed). We can help prepare the documents to clear the lien from the records.</p>
            </div>
        </div>
    </div>
</section>

{{-- Types of releases --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Types of Lien Releases</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Different situations call for different release documents. We prepare the right one for your circumstances.</p>
        </div>
        <div class="mt-12 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <h3 class="font-semibold text-zinc-900">Full Lien Release</h3>
                <p class="mt-2 text-zinc-600">Releases the entire mechanics lien after full payment. Use when you've received 100% of what you're owed and are releasing all claims against the property.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <h3 class="font-semibold text-zinc-900">Partial Lien Release</h3>
                <p class="mt-2 text-zinc-600">Releases a portion of the lien amount when partial payment is received. Common on large projects with progress payments.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <h3 class="font-semibold text-zinc-900">Lien Cancellation</h3>
                <p class="mt-2 text-zinc-600">Used when a lien was filed in error or is no longer valid. Cancels the lien without implying payment was received.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-6">
                <h3 class="font-semibold text-zinc-900">Conditional Release</h3>
                <p class="mt-2 text-zinc-600">Becomes effective upon receipt of payment (e.g., upon check clearing). Protects both parties in the release process.</p>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">How It Works</h2>
            <p class="mt-3 text-zinc-600">File your lien release in three simple steps</p>
        </div>
        <div class="relative mt-16">
            <div class="absolute top-7 hidden h-0.5 bg-zinc-300 lg:block" style="left: calc(16.67% + 1.75rem); right: calc(16.67% + 1.75rem);"></div>
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">1</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">Provide lien details</h3>
                    <p class="mt-2 text-zinc-600">Give us the recorded lien information (county, document number, date) and confirmation that payment or settlement has occurred.</p>
                </div>
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">2</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We prepare the release</h3>
                    <p class="mt-2 text-zinc-600">Our team drafts the correct lien release document for your state—full release, partial release, or cancellation—matching the original lien filing.</p>
                </div>
                <div class="text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-white">3</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We file it for you</h3>
                    <p class="mt-2 text-zinc-600">We record the lien release with the same county recorder where the mechanics lien was filed, clearing the property's title.</p>
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
                    Am I required to release a mechanics lien after payment?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes, in most cases. Once you're paid, you're generally obligated to release the lien. Contract terms often require it, and many states impose penalties (including liability for damages) if you unreasonably refuse to release a paid lien. Releasing promptly also maintains your professional reputation.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Where do I file a lien release?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">You file a lien release with the same county recorder's office where the original mechanics lien was recorded. The release references the original lien's recording number and clears it from the property's title. We handle the filing for you in the correct jurisdiction.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between a lien release and a lien waiver?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">A lien waiver is typically signed before or at the time of payment—you waive your right to file or maintain a lien in exchange for payment. A lien release is filed after a lien has already been recorded—it removes the lien from the county records. Both clear your claim; the lien release is used when a mechanics lien was previously filed.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long does it take to release a mechanics lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Once we have the lien details and confirmation of payment, we typically prepare and file the lien release within 1–3 business days. County recording times vary, but the release is usually recorded and reflected in public records within a few days. We provide proof of filing when complete.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I release a lien if I was only partially paid?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes. You can file a partial lien release that reduces the lien amount by the amount paid, or that releases the lien entirely if you've agreed to accept partial payment in full satisfaction. Be careful with partial payments—ensure your release language matches your intent. We can prepare a partial release that reflects the correct amount and protects your remaining claim if applicable.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Need to Release a Mechanics Lien?</h2>
            <p class="mt-4 text-lg text-zinc-400">Remove construction liens from property records in all 50 states. Fast, state-compliant lien release service.</p>
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
