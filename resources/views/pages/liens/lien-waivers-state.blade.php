@extends('layouts.landing')

@section('title', $pageTitle)

@section('meta')
<link rel="canonical" href="{{ $canonicalUrl }}" />
<meta name="description" content="{{ $metaDescription }}">
@endsection

@section('content')
@php
    // Everything on this page is driven by the WaiverStateRegistry rules for
    // $code. The state data files are still being authored, so every key is
    // read defensively: a missing landing block or kind entry falls back to
    // generic copy rather than blowing up the page.
    $landing = $rules['landing'] ?? [];
    $headline = $landing['headline'] ?? null;
    $summary = $landing['summary'] ?? null;
    $statute = $rules['statute'] ?? null;
    $hasStatutoryForm = ($rules['compliance_standard'] ?? 'generic') !== 'generic' && $statute;
    $esignAllowed = $rules['esign_allowed'] ?? true;
    $deemedDays = $rules['deemed_effective_days'] ?? null;

    $enabledKinds = collect($rules['kinds'] ?? [])->filter(fn ($entry) => $entry['enabled'] ?? false);

    // Plain-English gloss for each canonical kind; the card heading itself is
    // the state's statutory/house title from the registry.
    $kindDescriptions = [
        'conditional_progress' => 'Signed when a progress payment is promised: the waiver only takes effect once that payment actually arrives.',
        'unconditional_progress' => 'Signed after a progress payment has been received: immediately waives lien rights for work through the covered date.',
        'conditional_final' => 'Signed when the final payment is promised: waives all remaining lien rights, but only once the payment clears.',
        'unconditional_final' => 'Signed after the final payment is in hand: a complete, immediate waiver of lien rights on the project.',
    ];
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-28">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            @if ($hasStatutoryForm)
            Statutory forms, {{ $statute }}
            @else
            Free {{ $stateName }} generator, no credit card required
            @endif
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            {{ $stateName }}<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Lien Waiver Forms</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">
            @if ($headline)
            {{ $headline }}
            @else
            Generate the correct {{ $stateName }} lien waiver in minutes: free download, with e-signature and signed-copy storage when you need them.
            @endif
        </p>
        <div class="mt-10">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Create a free {{ $code }} lien waiver
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- State overview --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">State Rules</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">How {{ $stateName }} treats lien waivers</h2>
        @if ($summary)
        <p class="mt-4 text-lg text-zinc-600">{{ $summary }}</p>
        @else
        <p class="mt-4 text-lg text-zinc-600">
            {{ $stateName }} does not prescribe a mandatory statutory lien waiver form, so the format is up to the parties. Our attorney-reviewed conditional and unconditional waivers for progress and final payments apply, pre-filled with your project details and ready to exchange at every payment.
        </p>
        <p class="mt-4 text-zinc-600">
            The rule of thumb everywhere: sign a conditional waiver when payment is promised, and an unconditional waiver only after the money has actually arrived.
        </p>
        @endif
    </div>
</section>

{{-- Available forms --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">{{ $stateName }} lien waiver forms we generate</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">
                @if ($hasStatutoryForm)
                {{ $stateName }} prescribes the waiver language by statute; we generate the exact text of {{ $statute }}, filled in with your project details.
                @else
                Four house forms cover every payment on a {{ $stateName }} project: conditional or unconditional, progress or final.
                @endif
            </p>
        </div>
        @if ($enabledKinds->isNotEmpty())
        <div class="mt-12 grid gap-4 sm:grid-cols-2">
            @foreach ($enabledKinds as $kind => $entry)
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">{{ \Illuminate\Support\Str::headline($kind) }}</span>
                <h3 class="mt-3 font-semibold text-zinc-900">{{ $entry['title'] ?? \Illuminate\Support\Str::headline($kind) }}</h3>
                <p class="mt-2 text-zinc-600">{{ $kindDescriptions[$kind] ?? 'Generated with the correct '.$stateName.' wording and your project details filled in.' }}</p>
            </div>
            @endforeach
        </div>
        @else
        <div class="mx-auto mt-12 max-w-2xl rounded-xl border border-zinc-200 bg-white p-6 text-center text-zinc-600">
            We're finalizing the {{ $stateName }} form set. Create a free account and we'll have the correct forms ready for your project.
        </div>
        @endif
    </div>
</section>

{{-- Execution rules --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">Signing a lien waiver in {{ $stateName }}</h2>
                <p class="mt-4 text-lg text-zinc-600">
                    Execution rules (the form's wording, notarization, witnesses, and whether an electronic signature works) are set by state law. Here's what applies in {{ $stateName }}, built into every waiver we generate.
                </p>
                @if (!empty($rules['advance_waiver_note']))
                <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <p class="text-sm font-semibold uppercase tracking-wider text-amber-700">Advance waivers</p>
                    <p class="mt-2 text-sm text-amber-900">{{ $rules['advance_waiver_note'] }}</p>
                </div>
                @endif
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="font-semibold text-zinc-900">{{ $stateName }} execution rules</h3>
                <ul class="mt-4 space-y-4 text-zinc-600">
                    <li class="flex items-start gap-3">
                        <span class="mt-1 shrink-0 text-amber-500">&#9878;</span>
                        <span>
                            @if ($hasStatutoryForm)
                            <span class="font-medium text-zinc-900">Statutory form:</span>
                            @if (($rules['compliance_standard'] ?? null) === 'verbatim')
                            the waiver must match the form prescribed by {{ $statute }}; we generate that exact text.
                            @else
                            the waiver must substantially follow the form in {{ $statute }}; we generate the statutory text as written.
                            @endif
                            @else
                            <span class="font-medium text-zinc-900">No prescribed statutory form:</span> our attorney-reviewed house forms apply{{ $statute ? ', with the rules of '.$statute.' built in' : '' }}.
                            @endif
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 shrink-0 {{ ($rules['notarization_required'] ?? false) ? 'text-amber-500' : 'text-emerald-500' }}">{{ ($rules['notarization_required'] ?? false) ? '!' : '✓' }}</span>
                        <span>
                            <span class="font-medium text-zinc-900">Notarization:</span>
                            @if ($rules['notarization_required'] ?? false)
                            required. {{ $stateName }} waivers must be signed before a notary, so we generate a print-ready PDF with the notary block included.
                            @else
                            not required in {{ $stateName }}.
                            @endif
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 shrink-0 {{ ($rules['witness_required'] ?? false) ? 'text-amber-500' : 'text-emerald-500' }}">{{ ($rules['witness_required'] ?? false) ? '!' : '✓' }}</span>
                        <span>
                            <span class="font-medium text-zinc-900">Witness:</span>
                            @if ($rules['witness_required'] ?? false)
                            required. The form carries a witness signature line, and the claimant must sign in front of a witness.
                            @else
                            not required in {{ $stateName }}.
                            @endif
                        </span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="mt-1 shrink-0 {{ $esignAllowed ? 'text-emerald-500' : 'text-amber-500' }}">{{ $esignAllowed ? '✓' : '!' }}</span>
                        <span>
                            <span class="font-medium text-zinc-900">E-signature:</span>
                            @if ($esignAllowed)
                            available. Send {{ $stateName }} waivers for electronic signature and get the signed copy stored with a tamper-evident audit certificate.
                            @else
                            {{ $rules['esign_disabled_reason'] ?? 'not available for '.$stateName.' waivers. The statutory execution requirements need a paper signing, so we generate a print-ready PDF instead.' }}
                            @endif
                        </span>
                    </li>
                    @if ($deemedDays)
                    <li class="flex items-start gap-3">
                        <span class="mt-1 shrink-0 text-amber-500">!</span>
                        <span>
                            <span class="font-medium text-zinc-900">{{ $deemedDays }}-day deemed-effective rule:</span>
                            a signed {{ $stateName }} waiver conclusively becomes effective {{ $deemedDays }} days after execution even if payment never arrives{{ ($rules['affidavit_of_nonpayment'] ?? false) ? ', unless you first file an Affidavit of Nonpayment in the county where the property is located, which preserves your rights until payment is actually received' : '' }}. Calendar that deadline on every waiver you sign.
                        </span>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="border-t border-zinc-200 bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-16">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-white sm:text-4xl">Create a free {{ $stateName }} lien waiver</h2>
                <p class="mt-4 text-lg text-zinc-400">The correct {{ $stateName }} form, filled in and ready to download in about two minutes. Free to generate and download. Upgrade only when you want e-signature and automatic reminders.</p>
                <div class="mt-8">
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50">
                        Get started free
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Cross-links --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">Working in another state?</h2>
                <p class="mt-1 text-sm text-zinc-600">Every state's waiver rules are different. Check before you sign.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($nearbyStates as $nearbyCode => $nearbyName)
                <a href="{{ route('liens.lien-waivers.state', strtolower($nearbyCode)) }}" class="rounded-full border border-zinc-200 bg-zinc-50 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-amber-300 hover:text-amber-700">
                    {{ $nearbyName }}
                </a>
                @endforeach
                <a href="{{ route('liens.lien-waivers') }}" class="rounded-full bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-800">
                    All 50 states &rarr;
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
