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
<a href="{{ route('resale-certificates') }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Resale certificates</a>
<a href="{{ route('sales-tax-registration') }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Sales tax registration</a>
<a href="{{ route('contact') }}" class="rounded-lg px-3 py-2 transition hover:bg-zinc-100 hover:text-zinc-900 md:p-0 md:hover:bg-transparent">Contact</a>
@endsection

@section('footer_links')
<a href="{{ route('resale-certificates') }}" class="transition hover:text-zinc-900">Resale certificates</a>
<a href="{{ route('sales-tax-registration') }}" class="transition hover:text-zinc-900">Sales tax registration</a>
<a href="{{ route('lp.sales-tax', array_filter(['state' => $code ? strtolower($code) : null])) }}" class="transition hover:text-zinc-900">Sales tax permit registration</a>
@endsection

@section('content')
@php
    $term = $facts['term'] ?? 'sales tax permit';
    $form = $facts['resale_form'] ?? null;
    $since = config('company.in_business_since');
    $faqs = [
        'What is a resale certificate?' => 'The form you give a vendor so you can buy inventory tax-free because you will resell it. Each state has its own form and its own rules.'.($form ? " {$stateName} uses {$form}." : ''),
        'Do I need a sales tax permit first?' => "Yes. The certificate carries the permit number the state gave you.".($stateName ? " In {$stateName} that is the {$term}." : '')." If you do not have one yet, we register you first ({$permitPrice} per state) and the certificates follow.",
        'Can I use one certificate for every vendor and every state?' => 'A blanket certificate covers repeat purchases from the same vendor. For more than one state we generate the right form for each, and the multistate (MTC) and Streamlined Sales Tax certificates where the state accepts them.'.(($facts['resale_note'] ?? null) ? ' '.$facts['resale_note'] : ''),
        'Is an electronic signature accepted?' => 'Yes. Your saved signature is applied to each certificate, and the PDF is ready to email or print for the vendor.',
        'What does it cost?' => "{$price} a year for unlimited certificates in every state you buy in. Cancel anytime.",
        'Who is eRegister?' => 'eRegister is a business filing company based in Louisville, Kentucky'.($since ? ", in business since {$since}" : '').'. We also file sales tax registrations and mechanics liens.',
    ];
@endphp

{{-- Hero --}}
<section class="bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-8 sm:py-12 lg:py-20">
    <div class="mx-auto grid max-w-6xl items-start gap-8 px-4 sm:gap-10 sm:px-6 lg:grid-cols-2 lg:gap-14 lg:px-8">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
                Unlimited certificates &middot; {{ $price }} a year
            </div>
            <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-5xl">
                @if ($stateName)
                    {{ $stateName }} Resale Certificate, Signed in Minutes
                @else
                    Resale Certificates, Signed in Minutes
                @endif
            </h1>
            <p class="mt-5 text-lg text-zinc-300">
                Enter your business details once. We fill in {{ $form ? "the official {$form}" : "the official state form" }}, apply your signature, and hand you a vendor-ready PDF. Every state you buy in, including New York.
            </p>
            <x-reviews.google-line dark class="mt-5" />
            <ul class="mt-6 hidden space-y-2.5 text-zinc-300 sm:block">
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> Official state forms, blanket and multistate certificates</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> Saved vendors and expiration alerts</li>
                <li class="flex items-start gap-2.5"><span class="mt-0.5 text-amber-400">&#10003;</span> No permit yet? We register you first, {{ $permitPrice }} per state</li>
            </ul>
        </div>

        <x-sales-tax.front-door
            product="resale-cert"
            :state="$code"
            :term="$facts['term'] ?? null"
            :permit-price="$permitPrice"
            :generator-price="$price"
            :heading="$stateName ? 'Get your '.$stateName.' certificate' : 'Get your certificate'"
        />
    </div>
</section>

{{-- What you get --}}
<section class="bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">What you'll get</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">{{ $stateName ? "The {$stateName} form, filled in and signed" : 'The right form for each state, filled in and signed' }}</h2>
                <p class="mt-4 text-lg text-zinc-600">
                    {{ $form ? "{$stateName} expects {$form}." : 'Every state has its own certificate and its own rules.' }} You type your business details and permit numbers once. Each certificate comes out on the correct form with your signature applied, ready to send to the vendor.
                </p>
                <ul class="mt-6 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-3"><span class="mt-0.5 shrink-0 text-emerald-500">&#10003;</span><span><span class="font-medium text-zinc-900">Vendors saved:</span> add a vendor once, reuse it for every purchase.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-0.5 shrink-0 text-emerald-500">&#10003;</span><span><span class="font-medium text-zinc-900">Expirations tracked:</span> we tell you before a certificate lapses.</span></li>
                    <li class="flex items-start gap-3"><span class="mt-0.5 shrink-0 text-emerald-500">&#10003;</span><span><span class="font-medium text-zinc-900">Every state:</span> including the ones that reject the multistate form.</span></li>
                </ul>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6 shadow-sm">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 font-mono text-sm text-zinc-700">
                    <p class="text-xs uppercase tracking-widest text-zinc-400">{{ $form ?? 'Resale certificate' }}</p>
                    <p class="mt-4"><span class="text-zinc-400">Purchaser:</span> Your Business LLC</p>
                    <p class="mt-1"><span class="text-zinc-400">Permit no.:</span> {{ $stateName ? 'Your '.$term.' number' : 'Your permit number' }}</p>
                    <p class="mt-1"><span class="text-zinc-400">Vendor:</span> Summit Wholesale Co.</p>
                    <p class="mt-1"><span class="text-zinc-400">Items:</span> Merchandise for resale</p>
                    <p class="mt-4 border-t border-zinc-200 pt-4"><span class="text-zinc-400">Signed:</span> <span class="italic">your saved e-signature</span></p>
                    <p class="mt-4 text-xs text-zinc-400">Sample. Your certificate is generated on the official state form.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section id="how-it-works" class="scroll-mt-6 border-y border-zinc-200 bg-zinc-50 py-16 lg:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Set up once. Generate forever.</h2>
        </div>
        <div class="mt-12 grid gap-8 md:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 font-bold text-white">1</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Enter your business once</h3>
                <p class="mt-2 text-zinc-600">Business details, the states you are registered in with their permit numbers, and your signature. About five minutes.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-zinc-900 font-bold text-white">2</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Add a vendor, pick the state</h3>
                <p class="mt-2 text-zinc-600">We choose the correct form for that state and vendor: blanket, single purchase, multistate or Streamlined where accepted.</p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-500 font-bold text-white">3</div>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900">Download and send</h3>
                <p class="mt-2 text-zinc-600">A signed PDF in seconds. Email it to the vendor or print it. Generate as many as you need.</p>
            </div>
        </div>
    </div>
</section>

<x-reviews.google-cards heading="What customers say about eRegister" class="border-b border-zinc-200" />

{{-- Pricing --}}
<section id="pricing" class="scroll-mt-6 bg-white py-16 lg:py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">One flat price. No per-certificate fees.</h2>
        </div>
        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <div class="rounded-2xl border-2 border-amber-500 bg-white p-7 shadow-lg">
                <h3 class="text-lg font-semibold text-zinc-900">Resale certificates</h3>
                <p class="mt-2 text-4xl font-bold text-zinc-900">{{ $price }}<span class="text-base font-normal text-zinc-500"> a year</span></p>
                <ul class="mt-5 space-y-2.5 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Unlimited certificates, every state you buy in</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Official state forms, MTC and SST where accepted</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> E-signature, saved vendors, expiration alerts</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Cancel anytime</li>
                </ul>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-7">
                <h3 class="text-lg font-semibold text-zinc-900">Need the permit first?</h3>
                <p class="mt-2 text-4xl font-bold text-zinc-900">{{ $permitPrice }}<span class="text-base font-normal text-zinc-500"> per state</span></p>
                <ul class="mt-5 space-y-2.5 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> We prepare and file your {{ $stateName ? "{$stateName} {$term}" : 'sales tax registration' }}</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Permit number sent to you, then generate certificates</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Our fee refunded in full until we file</li>
                </ul>
                <a href="{{ route('lp.sales-tax', array_filter(['state' => $code ? strtolower($code) : null])) }}" class="mt-6 inline-block font-medium text-zinc-900 underline decoration-amber-400 underline-offset-2">About the registration</a>
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
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Stop paying sales tax on inventory</h2>
            <p class="mt-4 text-lg text-zinc-400">Signed, vendor-ready {{ $stateName ? "{$stateName} " : '' }}resale certificates in minutes. {{ $price }} a year.</p>
            <a href="#start" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:bg-zinc-50">
                Start now
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
            </a>
        </div>
    </div>
</section>
@endsection
