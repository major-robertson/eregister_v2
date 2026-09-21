@extends('layouts.lp')

@section('title', $pageTitle)

@section('meta')
<meta name="description" content="{{ $metaDescription }}">
@endsection

{{-- The same links serve the desktop menu and the phone dropdown. --}}
@section('nav')
<a href="#how-it-works" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">How it works</a>
<a href="#pricing" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Pricing</a>
<a href="#questions" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Questions</a>
<a href="{{ route('liens.lien-waivers') }}#states" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Forms by state</a>
<a href="{{ route('contact') }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Contact</a>
@endsection

@section('footer_links')
<a href="{{ route('liens.lien-waivers') }}#states" class="transition hover:text-zinc-900">Lien waiver forms by state</a>
<a href="{{ route('liens.lien-waivers.pricing') }}" class="transition hover:text-zinc-900">Lien waiver pricing</a>
<a href="{{ route('liens') }}" class="transition hover:text-zinc-900">Mechanics lien filing</a>
@endsection

@section('content')
@php
    // Everything state-specific comes from the WaiverStateRegistry rules so
    // the page can never promise a form the wizard doesn't generate. With no
    // state (the generic ads page) the copy speaks for all 50.
    $statute = $rules['statute'] ?? null;
    $hasStatutoryForm = $rules !== null && ($rules['compliance_standard'] ?? 'generic') !== 'generic' && $statute;
    $esignAllowed = $rules['esign_allowed'] ?? true;
    $notaryRequired = $rules['notarization_required'] ?? false;
    $witnessRequired = $rules['witness_required'] ?? false;
    $monthlyPrice = number_format(config('lien_waivers.prices.monthly.amount_cents', 4900) / 100);
    $freeSaves = (int) config('lien_waivers.free_saved_waivers_per_month', 3);
    $from = request()->getPathInfo();
    $statutoryStates = ['Arizona', 'California', 'Florida', 'Georgia', 'Michigan', 'Mississippi', 'Nevada', 'Texas', 'Utah', 'Wyoming'];
@endphp

{{-- Hero: the promise on the left, the starter on the right --}}
<section class="bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-8 sm:py-12 lg:py-20">
    <div class="mx-auto grid max-w-6xl items-start gap-8 px-4 sm:gap-10 sm:px-6 lg:grid-cols-2 lg:gap-14 lg:px-8">
        @if (($variant ?? 'form') === 'software')
        {{-- Software searchers collect waivers on every draw. Sell the paid
             product, with its price, instead of a free form. --}}
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
                Lien waiver software &middot; ${{ $monthlyPrice }} a month
            </div>
            <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-5xl">
                Collect Lien Waivers Without Chasing Anyone
            </h1>
            <p class="mt-5 text-lg text-zinc-300">
                Send each sub or vendor the correct waiver for the job's state. They sign online from their phone, with no account. We remind them until it's signed and keep the signed copy with your project.
            </p>
            {{-- Hidden on phones so the starter sits above the fold. --}}
            <ul class="mt-6 hidden space-y-2.5 text-zinc-300 sm:block">
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> The correct form for all 50 states, with statutory wording where required</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> Automatic reminders until each waiver is signed</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> ${{ $monthlyPrice }} a month per person. Create your first waiver free.</li>
            </ul>
        </div>
        @else
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
                @if ($hasStatutoryForm)
                    Statutory form &middot; {{ $statute }}
                @else
                    Free to generate &middot; No credit card
                @endif
            </div>
            <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-5xl">
                @if ($stateName)
                    Free {{ $stateName }} Lien Waiver Form
                @else
                    Free Lien Waiver Form Generator
                @endif
            </h1>
            <p class="mt-5 text-lg text-zinc-300">
                @if ($hasStatutoryForm)
                    {{ $stateName }} law prescribes the waiver wording ({{ $statute }}). We generate the exact statutory text, filled in with your project and payment details, as a PDF you can download free.
                @elseif ($stateName)
                    The correct conditional or unconditional waiver for a {{ $stateName }} project, filled in with your details and ready to download as a PDF in about two minutes. Free, no watermark.
                @else
                    Conditional, unconditional, progress, and final lien waivers with the correct form for all 50 states, including the exact statutory text where the law prescribes one. Filled in with your details, downloaded free.
                @endif
            </p>
            {{-- Hidden on phones so the starter sits above the fold. --}}
            <ul class="mt-6 hidden space-y-2.5 text-zinc-300 sm:block">
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> Conditional and unconditional waivers for progress and final payments</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> Statutory wording where the state requires it, attorney-reviewed forms everywhere else</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> Download the PDF free, or sign and send it electronically with Pro</li>
            </ul>
        </div>
        @endif

        <x-lien.waiver-starter
            :state="$code"
            :direction="$preselectedDirection"
            :kind="$preselectedKind"
            :from="$from"
            sticky-button
            :heading="($variant ?? 'form') === 'software' ? 'Send your first waiver' : ($stateName ? 'Create your '.$stateName.' waiver' : 'Create your waiver')"
        />
    </div>
</section>

{{-- The form they'll get --}}
<section class="bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">The form you'll get</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                    @if ($hasStatutoryForm)
                        The {{ $stateName }} statutory form, filled in
                    @elseif ($stateName)
                        The right {{ $stateName }} waiver, filled in
                    @else
                        The right form for the project's state, filled in
                    @endif
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    @if ($hasStatutoryForm)
                        Use the wrong wording in {{ $stateName }} and the waiver can be invalid, or waive more than you meant to. We generate the text of {{ $statute }} as written, sized and formatted the way the statute demands, with your names, amounts, and dates in the blanks.
                    @elseif ($stateName)
                        {{ $stateName }} leaves the format to the parties, so we use attorney-reviewed house forms with {{ $stateName }}'s signing rules built in: the right conditional or unconditional language for the payment, with your names, amounts, and dates in the blanks.
                    @else
                        Ten states prescribe the waiver wording by statute ({{ implode(', ', $statutoryStates) }}) and two more impose special rules. We generate the statutory text where it exists and attorney-reviewed house forms everywhere else, with your names, amounts, and dates in the blanks.
                    @endif
                </p>
                @if ($stateName)
                    <ul class="mt-6 space-y-3 text-zinc-600">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 shrink-0 {{ $notaryRequired ? 'text-amber-500' : 'text-emerald-500' }}">{{ $notaryRequired ? '!' : '✓' }}</span>
                            <span><span class="font-medium text-zinc-900">Notarization:</span> {{ $notaryRequired ? 'required — the PDF includes the notary block.' : 'not required in '.$stateName.'.' }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 shrink-0 {{ $witnessRequired ? 'text-amber-500' : 'text-emerald-500' }}">{{ $witnessRequired ? '!' : '✓' }}</span>
                            <span><span class="font-medium text-zinc-900">Witness:</span> {{ $witnessRequired ? 'required — the form carries a witness signature line.' : 'not required in '.$stateName.'.' }}</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 shrink-0 {{ $esignAllowed ? 'text-emerald-500' : 'text-amber-500' }}">{{ $esignAllowed ? '✓' : '!' }}</span>
                            <span><span class="font-medium text-zinc-900">E-signature:</span> {{ $esignAllowed ? 'valid for '.$stateName.' waivers, with a tamper-evident audit certificate.' : ($rules['esign_disabled_reason'] ?? 'not available for '.$stateName.' waivers; we generate a print-ready PDF instead.') }}</span>
                        </li>
                    </ul>
                @endif
            </div>

            {{-- The real form, sample-filled: what the wizard will hand them --}}
            <x-lien.waiver-form-preview
                :previews="$previews"
                :state-name="$stateName"
                :statutory="(bool) $hasStatutoryForm"
                :kind="$preselectedKind"
            />
        </div>
    </div>
</section>

{{-- How it works --}}
<section id="how-it-works" class="scroll-mt-6 border-y border-zinc-200 bg-zinc-50 py-16 lg:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Three steps to a finished waiver</h2>
        </div>
        <div class="mt-12 grid gap-8 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 font-bold text-white">1</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Pick the state and waiver</h3>
                <p class="mt-2 text-zinc-600">Progress or final payment, conditional or unconditional. Not sure? Two plain-English questions choose the right one.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 font-bold text-white">2</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Fill in the payment and parties</h3>
                <p class="mt-2 text-zinc-600">Jobsite address, property owner, the amount and through date, and who the waiver goes to. Addresses autocomplete.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 font-bold text-white">3</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Download free, or sign and send</h3>
                <p class="mt-2 text-zinc-600">Download the PDF free. With Pro, sign it electronically, send it, and keep the signed copy stored on the project.</p>
            </div>
        </div>
    </div>
</section>

{{-- Pricing strip --}}
<section id="pricing" class="scroll-mt-6 bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Free to create. Pro when you need it signed and sent.</h2>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-7">
                <h3 class="text-lg font-semibold text-zinc-900">Free</h3>
                <p class="mt-2 text-4xl font-bold text-zinc-900">$0</p>
                <ul class="mt-5 space-y-2.5 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> The correct form for the project's state, filled in</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> PDF download, no watermark</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Up to {{ $freeSaves }} saved waivers a month</li>
                </ul>
            </div>
            <div class="rounded-2xl border-2 border-amber-500 bg-white p-7 shadow-lg">
                <h3 class="text-lg font-semibold text-zinc-900">Pro</h3>
                <p class="mt-2 text-4xl font-bold text-zinc-900">${{ $monthlyPrice }}<span class="text-base font-normal text-zinc-500">/mo per seat</span></p>
                <ul class="mt-5 space-y-2.5 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Unlimited waivers</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> E-signature: sign your own, or collect from subs and vendors</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Automatic signer reminders and signed-copy storage</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Cancel anytime</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="questions" class="scroll-mt-6 border-t border-zinc-200 bg-zinc-50 py-16 lg:py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold text-zinc-900">Questions</h2>
        <div class="mt-10 space-y-4">
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is the waiver really free?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes. Creating the waiver and downloading the PDF is free, with no watermark and no credit card. Pro adds electronic signing and sending, reminders, and signed-copy storage.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Conditional or unconditional: which one do I need?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">A conditional waiver only takes effect once the payment actually arrives, so it is the safe choice when the check hasn't cleared. An unconditional waiver gives up lien rights the moment it is signed; sign one only after the money is in hand. Progress waivers cover one payment; a final waiver goes with the last payment on the job.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is an electronic signature valid on a lien waiver?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">In most states, yes: the federal E-SIGN Act and each state's UETA make electronic signatures equivalent to ink, and every signed copy comes with a tamper-evident audit certificate. Where a statute requires a notary or witness (Mississippi, Wyoming, Georgia), we generate a print-ready PDF instead and say so before you start.</div>
            </details>
        </div>
    </div>
</section>

{{-- Final CTA back to the starter --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 px-6 py-14 text-center">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">
                Create your free {{ $stateName ? $stateName.' ' : '' }}lien waiver
            </h2>
            <p class="mt-4 text-lg text-zinc-400">The correct form, filled in and ready to download in about two minutes.</p>
            <a href="#start" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:bg-zinc-50">
                Start now
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
            </a>
        </div>
    </div>
</section>
@endsection
