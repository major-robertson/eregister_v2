@extends('layouts.landing')

@section('title', 'Lien Waiver Pricing | Free Generation, Affordable E-Signature')

@section('meta')
<link rel="canonical" href="{{ route('liens.lien-waivers.pricing') }}" />
<meta name="description" content="Lien waiver pricing: generate and download waivers for all 50 states free. Upgrade for e-signature, automatic reminders, and signed-copy storage at $99/month or $990/year.">
@endsection

@php
    $freeSaves = config('lien_waivers.free_saved_waivers_per_month', 4);
    $monthlyPrice = number_format(config('lien_waivers.prices.monthly.amount_cents', 9900) / 100);
    $yearlyPrice = number_format(config('lien_waivers.prices.yearly.amount_cents', 99000) / 100);
@endphp

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            Free to generate, no credit card required
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Lien Waiver Pricing<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Free to Generate, Affordable to Automate</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">
            Generating and downloading the correct waiver for any of the 50 states is always free. Pay only when you want e-signature, automatic reminders, and signed-copy storage handling the follow-up for you.
        </p>
        <div class="mt-10">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Create a free lien waiver
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Plans --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Two plans, one flat price to upgrade</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">No per-waiver fees and no hourly billing. Start free and upgrade to Pro whenever you're ready to automate.</p>
        </div>
        <div class="mx-auto mt-12 grid max-w-4xl gap-8 lg:grid-cols-2">
            {{-- Free --}}
            <div class="flex flex-col rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="text-lg font-semibold text-zinc-900">Free</h3>
                <div class="mt-4 flex items-baseline gap-1">
                    <span class="text-5xl font-bold tracking-tight text-zinc-900">$0</span>
                    <span class="text-zinc-500">/month</span>
                </div>
                <p class="mt-3 text-sm text-zinc-600">Everything you need to generate a correct waiver and get it out the door.</p>
                <ul class="mt-6 flex-1 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> {{ $freeSaves }} waivers per month with every Pro feature, no watermark</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Correct forms for all 50 states, statutory text included</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> E-signature send &amp; collect, reminders, signed-copy storage</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Free project &amp; deadline tracking</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-6 py-3 font-semibold text-zinc-900 transition hover:border-zinc-400">
                    Start free
                </a>
            </div>
            {{-- Pro --}}
            <div class="relative flex flex-col rounded-2xl border-2 border-amber-500 bg-white p-8 shadow-lg">
                <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-zinc-900">Most popular</span>
                <h3 class="text-lg font-semibold text-zinc-900">Pro</h3>
                <div class="mt-4 flex items-baseline gap-1">
                    <span class="text-5xl font-bold tracking-tight text-zinc-900">${{ $monthlyPrice }}</span>
                    <span class="text-zinc-500">/month</span>
                </div>
                <p class="mt-3 text-sm text-zinc-600">Or ${{ $yearlyPrice }}/year, two months free. For teams exchanging waivers on every draw.</p>
                <ul class="mt-6 flex-1 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Everything in Free</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Unlimited waivers every month, no cap</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> One flat price for your whole team</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> No per-waiver or per-signature charges</li>
                </ul>
                <a href="{{ route('register') }}" class="group mt-8 inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                    Start with Pro
                    <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
        <p class="mx-auto mt-8 max-w-2xl text-center text-sm text-zinc-500">Pro is a single flat subscription for your whole team, with no per-waiver or per-signature charges. Cancel anytime.</p>
    </div>
</section>

{{-- Cross-reference: lien filing --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-zinc-200 bg-white p-8 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-xl font-bold text-zinc-900">Need to file a lien or send a notice?</h3>
                <p class="mt-2 text-zinc-600">Lien waivers get you paid without a fight. When a job goes sideways, preliminary notices, notices of intent, mechanics liens, and lien releases are priced separately, one flat fee per filing with state fees included.</p>
            </div>
            <a href="{{ route('liens.pricing') }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-zinc-900 px-6 py-3 font-semibold text-white transition hover:bg-zinc-800">
                See lien filing pricing
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Pricing FAQ --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Pricing FAQ</h2>
        </div>
        <div class="mt-12 space-y-4">
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is generating a lien waiver really free?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes. Generating and downloading the correct waiver for your state is free, with no watermark and no credit card. Free accounts can also save up to {{ $freeSaves }} waivers to their projects each month. Pro is only for the automation: e-signature, reminders, and signed-copy storage.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do you charge per waiver or per signature?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">No. Pro is one flat ${{ $monthlyPrice }}/month (or ${{ $yearlyPrice }}/year) for your whole team, with unlimited waivers, unlimited e-signature requests, and unlimited storage. There are no per-document or per-signature fees to worry about.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What does the annual plan save me?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">The annual plan is ${{ $yearlyPrice }}/year, which works out to two months free compared with paying monthly. You can switch between monthly and annual at checkout.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is lien filing included in the waiver subscription?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">No. The waiver subscription covers lien waivers only. Filing documents like preliminary notices, notices of intent, mechanics liens, and lien releases are priced separately at one flat fee per filing. See <a href="{{ route('liens.pricing') }}" class="font-medium text-amber-600 underline hover:text-amber-700">lien filing pricing</a> for those. Lien tracking is free either way.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Start free, upgrade when you're ready</h2>
            <p class="mt-4 text-lg text-zinc-400">Generate your first waiver in about two minutes. No credit card required.</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50">
                    Create a free lien waiver
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
