@extends('layouts.lp')

@section('title', $pageTitle)

@section('meta')
<meta name="description" content="{{ $metaDescription }}">
@endsection

@section('nav')
<a href="#how-it-works" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">How it works</a>
<a href="#pricing" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Pricing</a>
<a href="#reviews" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Reviews</a>
<a href="#questions" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Questions</a>
<a href="{{ route('sales-tax-registration') }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Sales tax registration</a>
<a href="{{ route('resale-certificates') }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Resale certificates</a>
<a href="{{ route('contact') }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Contact</a>
@endsection

@section('footer_links')
<a href="{{ route('sales-tax-registration') }}" class="transition hover:text-zinc-900">Sales tax registration</a>
<a href="{{ route('resale-certificates') }}" class="transition hover:text-zinc-900">Resale certificates</a>
<a href="{{ route('lp.resale-certificate', array_filter(['state' => $code ? strtolower($code) : null])) }}" class="transition hover:text-zinc-900">Resale certificate generator</a>
@endsection

@section('content')
@php
    $term = $facts['term'] ?? 'Sales Tax Permit';
    $agency = $facts['agency'] ?? null;
    $since = config('company.in_business_since');
    $where = $stateName ? "in {$stateName}" : 'in any state';
    $faqs = [
        "Is a seller's permit the same as a sales tax permit?" => "Usually, yes. Seller's permit, sales tax permit, sales tax license, certificate of authority and sales tax ID are different states' names for the same registration: the account that lets you collect sales tax and buy inventory tax-free.".($stateName ? " {$stateName} calls it the {$term}." : ''),
        'Do I need a permit before I can use a resale certificate?' => "Yes. A resale certificate is the form you hand a vendor to buy tax-free, and it needs the permit number the state gives you. Register first, then generate certificates.".(($facts['resale_note'] ?? null) ? ' '.$facts['resale_note'] : ''),
        'How long does it take?' => 'Answering our questions takes about 10 minutes. We prepare and file the registration within 5 business days, or 2 business days with rush processing.'.(($facts['state_timing'] ?? null) ? ' '.$facts['state_timing'] : ' Each state then takes its own time to issue the number.'),
        'What does it cost?' => "{$price} per state, one flat fee for preparing and filing the registration.".(($facts['state_fee'] ?? null) ? ' '.$facts['state_fee'] : ' Most states charge nothing for the permit itself.').' Our fee is refunded in full until we file with the state.',
        'What will you ask me for?' => 'Your business name and type, the address, what you sell and where, when you started or plan to start, and the owner or officer details the state requires. An EIN is optional to start; we will ask before we file.',
        'Who is eRegister?' => 'eRegister is a business filing company based in Louisville, Kentucky'.($since ? ", in business since {$since}" : '').'. Besides sales tax registrations we generate resale certificates and file mechanics liens for contractors.',
    ];
@endphp

{{-- Hero: the promise on the left, the three doors on the right --}}
<section class="bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-8 sm:py-12 lg:py-20">
    <div class="mx-auto grid max-w-6xl items-start gap-8 px-4 sm:gap-10 sm:px-6 lg:grid-cols-2 lg:gap-14 lg:px-8">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
                Prepared and filed for you &middot; {{ $price }} per state
            </div>
            <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-5xl">
                @if ($stateName)
                    {{ $stateName }} <span data-hero-keyword>{{ $keyword }}</span>
                @else
                    <span data-hero-keyword>{{ $keyword }}</span>, Filed for You
                @endif
            </h1>
            <p class="mt-5 text-lg text-zinc-300">
                Answer a few plain questions. We prepare the registration, file it with {{ $agency ? 'the '.$agency : 'the state' }}, and send you the number{{ $stateName ? " you need to collect sales tax and buy inventory tax-free in {$stateName}" : '' }}. No state portal, no PINs to chase.
            </p>
            <x-reviews.google-line dark class="mt-5" />
            <ul class="mt-6 hidden space-y-2.5 text-zinc-300 sm:block">
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> About 10 minutes of questions, reviewed by a specialist before filing</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> One state or several in the same application</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> Our fee refunded in full until we file with the state</li>
            </ul>
        </div>

        <x-sales-tax.front-door
            product="sales-tax"
            :state="$code"
            :intent="$intent"
            :term="$facts['term'] ?? null"
            :permit-price="$price"
            :heading="$stateName ? 'Start your '.$stateName.' registration' : 'Start your registration'"
        />
    </div>
</section>

{{-- State facts --}}
@if ($stateName)
<section class="bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">{{ $stateName }} at a glance</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">What {{ $stateName }} calls it, and what it takes</h2>
                <p class="mt-4 text-lg text-zinc-600">
                    In {{ $stateName }} the sales tax registration is the <span class="font-medium text-zinc-900">{{ $term }}</span>{{ $agency ? ', issued by the '.$agency : '' }}. Whatever the search called it, this is the registration we file.
                </p>
            </div>
            <dl class="divide-y divide-zinc-200 rounded-2xl border border-zinc-200 bg-zinc-50">
                <div class="grid gap-1 p-5 sm:grid-cols-3">
                    <dt class="font-medium text-zinc-900">State fee</dt>
                    <dd class="text-zinc-600 sm:col-span-2">{{ $facts['state_fee'] ?? 'Most states charge nothing for the permit itself.' }}</dd>
                </div>
                <div class="grid gap-1 p-5 sm:grid-cols-3">
                    <dt class="font-medium text-zinc-900">State timing</dt>
                    <dd class="text-zinc-600 sm:col-span-2">{{ $facts['state_timing'] ?? 'Each state takes its own time to issue the number once the application is in.' }}</dd>
                </div>
                <div class="grid gap-1 p-5 sm:grid-cols-3">
                    <dt class="font-medium text-zinc-900">Our part</dt>
                    <dd class="text-zinc-600 sm:col-span-2">{{ $price }} per state. Prepared, reviewed and filed within 5 business days, or 2 with rush processing.</dd>
                </div>
                @if ($facts['resale_form'] ?? null)
                <div class="grid gap-1 p-5 sm:grid-cols-3">
                    <dt class="font-medium text-zinc-900">Resale certificate</dt>
                    <dd class="text-zinc-600 sm:col-span-2">{{ $facts['resale_form'] }}. <a href="{{ route('lp.resale-certificate', ['state' => strtolower($code)]) }}" class="font-medium text-zinc-900 underline decoration-amber-400 underline-offset-2">Generate it here</a> once you are registered.</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</section>
@endif

{{-- How it works --}}
<section id="how-it-works" class="scroll-mt-6 border-y border-zinc-200 bg-zinc-50 py-16 lg:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Three steps to a valid registration</h2>
            <p class="mt-3 text-lg text-zinc-600">You do the easy part. We do the part that trips everyone up.</p>
        </div>
        <div class="mt-12 grid gap-8 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 font-bold text-white">1</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Pick your state and order</h3>
                <p class="mt-2 text-zinc-600">Choose {{ $stateName ?? 'the state' }} or several states, tell us your business type and name, and pay {{ $price }} per state. Rush processing is optional.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 font-bold text-white">2</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Answer the questions</h3>
                <p class="mt-2 text-zinc-600">About 10 minutes: what you sell, where, when you started, and the owner details the state requires. Save and come back any time.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 font-bold text-white">3</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">We file, you get the number</h3>
                <p class="mt-2 text-zinc-600">A specialist checks everything against {{ $stateName ? "{$stateName}'s" : "the state's" }} rules, files it, and sends you the permit number and account details.</p>
            </div>
        </div>
    </div>
</section>

{{-- What customers say (Google reviews) --}}
<x-reviews.google-cards heading="What customers say about eRegister" class="border-b border-zinc-200" />

{{-- Pricing --}}
<section id="pricing" class="scroll-mt-6 bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">One flat fee per state</h2>
            <p class="mt-3 text-lg text-zinc-600">Know the cost before you start. No metered charges, no surprise add-ons.</p>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border-2 border-amber-500 bg-white p-7 shadow-lg">
                <h3 class="text-lg font-semibold text-zinc-900">Registration</h3>
                <p class="mt-2 text-4xl font-bold text-zinc-900">{{ $price }}<span class="text-base font-normal text-zinc-500"> per state</span></p>
                <ul class="mt-5 space-y-2.5 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Guided application, no state portal</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Reviewed by a specialist, filed within 5 business days</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Permit number and account details sent to you</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Rush processing (2 business days) available at checkout</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Our fee refunded in full until we file</li>
                </ul>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-7">
                <h3 class="text-lg font-semibold text-zinc-900">Then: resale certificates</h3>
                <p class="mt-2 text-4xl font-bold text-zinc-900">{{ $generatorPrice }}<span class="text-base font-normal text-zinc-500"> a year</span></p>
                <ul class="mt-5 space-y-2.5 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Unlimited signed certificates on the official state forms</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Every state you buy in, including New York</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Optional. Offered once your registration is ready</li>
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
            @foreach ($faqs as $question => $answer)
                <details class="group rounded-xl border border-zinc-200 bg-white">
                    <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                        {{ $question }}
                        <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </summary>
                    <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">{{ $answer }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- Final CTA --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 px-6 py-14 text-center">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Get registered {{ $where }}</h2>
            <p class="mt-4 text-lg text-zinc-400">Answer a few questions and we take it from there. {{ $price }} per state.</p>
            <a href="#start" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:bg-zinc-50">
                Start now
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
            </a>
        </div>
    </div>
</section>
@endsection
