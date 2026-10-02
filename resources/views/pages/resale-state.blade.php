@extends('layouts.landing')

@section('title', $page->title())
@section('description', $page->metaDescription())
@section('canonical', $page->url())

@section('content')
@php
    /** @var \App\Domains\ResaleCert\Seo\ResaleStatePage $page */
    $name = $page->name;
    $rule = $page->rule;
    $a = $page->article;
    $noTax = $page->noSalesTax();
@endphp

@if ($noTax)
{{-- No statewide sales tax: what a buyer from here gives suppliers in other
     states, and the taxes that do apply. No generator, no Service schema:
     the state issues no certificate for us to sell. --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="text-zinc-400" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Resale Certificates', 'url' => route('resale-certificates')],
            ['name' => $name],
        ]" />
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            {{ $name }} Resale Certificate<br>
            <span class="bg-gradient-to-r from-emerald-300 to-teal-400 bg-clip-text text-transparent">No Sales Tax: What Suppliers Need</span>
        </h1>
        <p class="mt-6 max-w-2xl text-lg font-semibold text-zinc-200">{{ $name }} has no statewide sales tax. Agency: {{ $page->agencyName() }}.</p>
        @if ($page->noSalesTaxNote())
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">{{ $page->noSalesTaxNote() }}</p>
        @endif
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="#suppliers" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                What to give suppliers
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
            <a href="{{ route('resale-certificates') }}#states" class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-8 py-4 text-base font-semibold text-zinc-200 transition hover:bg-zinc-800">
                Resale certificate rules by state
            </a>
        </div>
    </div>
</section>

{{-- What to give suppliers in other states --}}
<section id="suppliers" class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900">What to give suppliers in other states</h2>
                <p class="mt-6 text-zinc-600">A supplier in a state with a sales tax has to charge that tax unless it has a resale certificate it can accept. Give it:</p>
                <ul class="mt-6 space-y-3 text-zinc-600">
                    @foreach ($page->supplierItems() as $item)
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="text-lg font-semibold text-zinc-900">The number they will ask for</h3>
                <p class="mt-4 text-lg font-semibold text-zinc-900">{{ $page->registrationNumberName() ? ucfirst($page->registrationNumberName()) : "No {$name} sales tax number" }}</p>
                @if ($page->registrationNotes())
                <p class="mt-2 text-sm text-zinc-600">{{ $page->registrationNotes() }}</p>
                @endif
                @if ($page->formPdfUrl() || $page->registrationVerifyUrl())
                <ul class="mt-6 space-y-2 text-sm">
                    @if ($page->formPdfUrl())
                    <li><a href="{{ $page->formPdfUrl() }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">Download {{ $page->formNumber() ?? $page->formTitle() ?? 'the form' }}</a></li>
                    @endif
                    @if ($page->registrationVerifyUrl())
                    <li><a href="{{ $page->registrationVerifyUrl() }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">Look up {{ $a }} {{ $name }} business registration</a></li>
                    @endif
                </ul>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Local taxes to know about --}}
@if ($page->localTaxes())
<section class="border-t border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Local taxes to know about</h2>
        <p class="mt-4 max-w-3xl text-zinc-600">{{ $name }} has no general sales tax, but these taxes still apply to some sales.</p>
        <dl class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($page->localTaxes() as $tax)
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <dt class="text-lg font-semibold text-zinc-900">{{ $tax['name'] }}</dt>
                <dd class="mt-2 text-sm text-zinc-600">{{ $tax['text'] }} <a href="{{ $tax['source_url'] }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">Source</a></dd>
            </div>
            @endforeach
        </dl>
    </div>
</section>
@endif
@else
<x-seo.service
    name="{{ $name }} Resale Certificate Generator"
    description="Generate signed {{ $name }} resale certificates on the accepted form, with the state's rules for uniform certificates, out-of-state buyers, blanket certificates, and expiration applied automatically."
    :url="$page->url()"
    category="Resale certificate generation" />

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="text-zinc-400" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Resale Certificates', 'url' => route('resale-certificates')],
            ['name' => $name],
        ]" />
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            {{ $name }} Resale Certificate<br>
            <span class="bg-gradient-to-r from-emerald-300 to-teal-400 bg-clip-text text-transparent">Rules, Form &amp; Expiration</span>
        </h1>
        @if ($page->heroLine())
        <p class="mt-6 max-w-2xl text-lg font-semibold text-zinc-200">{{ $page->heroLine() }}</p>
        @endif
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">
            What {{ $a }} {{ $name }} resale certificate has to say, who can sign one, how long it lasts, and how to produce a signed copy for every vendor in minutes.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Generate {{ $a }} {{ $name }} certificate
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
            <a href="{{ route('resale-certificates') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-8 py-4 text-base font-semibold text-zinc-200 transition hover:bg-zinc-800">
                How the generator works
            </a>
        </div>
    </div>
</section>

{{-- Key facts --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} resale certificate rules</h2>
        <dl class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($page->keyFacts() as $fact)
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
                <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $fact['label'] }}</dt>
                <dd class="mt-2 text-lg font-semibold text-zinc-900">{{ $fact['value'] }}</dd>
                <dd class="mt-1 text-sm text-zinc-600">{{ $fact['detail'] }}</dd>
            </div>
            @endforeach
        </dl>
    </div>
</section>
@endif

@if ($page->content)
{{-- Researched state rules, with sources --}}
<section class="{{ $noTax ? 'border-t border-zinc-200 bg-white' : 'border-y border-zinc-200 bg-zinc-50' }} py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} rules in plain English</h2>
                @foreach ($page->notesParagraphs() as $paragraph)
                <p class="mt-6 text-zinc-600">{{ $paragraph }}</p>
                @endforeach
                @if ($noTax && $page->sources())
                <p class="mt-6 text-sm text-zinc-600">
                    Sources:
                    @foreach ($page->sources() as $source)
                    <a href="{{ $source['url'] }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">{{ $source['title'] }}</a>{{ $loop->last ? '' : ' ·' }}
                    @endforeach
                </p>
                @elseif ($page->sourceLinks())
                <p class="mt-6 text-sm text-zinc-600">
                    Sources:
                    @foreach ($page->sourceLinks() as $link)
                    <a href="{{ $link['url'] }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">{{ $link['label'] }}</a>{{ $loop->last ? '' : ' ·' }}
                    @endforeach
                </p>
                @endif
            </div>
            <div class="rounded-2xl border border-zinc-200 {{ $noTax ? 'bg-zinc-50' : 'bg-white' }} p-8">
                <h3 class="text-lg font-semibold text-zinc-900">More {{ $name }} facts</h3>
                <ul class="mt-4 space-y-3 text-sm text-zinc-600">
                    @foreach ($page->moreFacts() as $fact)
                    <li class="flex gap-3">
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                        <span>{{ $fact['text'] }} <a href="{{ $fact['source_url'] }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">Source</a></span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>
@endif

@if ($noTax)
{{-- No generator CTA: the state issues no certificate to sell. --}}
<section class="border-t border-zinc-200 bg-white py-20">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Selling into states with a sales tax?</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            {{ $name }} does not issue a sales tax permit. If your business has to collect tax in another state, register there first. Each state's resale certificate page explains what its suppliers will accept.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('sales-tax-registration') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Sales tax registration</a>
            <a href="{{ route('resale-certificates') }}#states" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">Resale certificates by state</a>
        </div>
    </div>
</section>
@else
{{-- What goes on the certificate --}}
<section class="{{ $page->content ? 'bg-white' : 'border-y border-zinc-200 bg-zinc-50' }} py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900">What {{ $a }} {{ $name }} resale certificate must include</h2>
                <ul class="mt-6 space-y-3 text-zinc-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>The buyer's legal name, business address, and {{ $page->buyerNumberPhrase() }}.</li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>The seller's name and address.</li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>A description of the property being purchased for resale{{ $page->allowsBlanket() && $rule->default_blanket_text ? ', or for a blanket certificate a general description such as "'.$rule->default_blanket_text.'"' : '' }}.</li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>A statement that the items will be resold in the ordinary course of business, signed and dated by the buyer.</li>
                    @if (! $page->expirationLabel() && $rule->expiration_months)
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>A date, because {{ $name }} certificates are {{ $page->expirationPhrase() }} and vendors need to know when to ask for a new one.</li>
                    @endif
                </ul>
            </div>
            <div class="rounded-2xl border border-zinc-200 {{ $page->content ? 'bg-zinc-50' : 'bg-white' }} p-8">
                <h3 class="text-lg font-semibold text-zinc-900">Using your {{ $name }} certificate</h3>
                <ol class="mt-4 list-decimal space-y-3 pl-5 text-sm text-zinc-600">
                    <li>Confirm the purchase is genuinely for resale. Using a certificate for supplies or equipment you consume is the most common audit finding.</li>
                    <li>Give the vendor the signed certificate before or at the time of the sale; a certificate produced after the fact is often rejected.</li>
                    <li>{{ $page->allowsBlanket() ? 'Issue one blanket certificate per vendor and keep a copy for your records.' : 'Issue a fresh certificate for each purchase and keep copies for your records.' }}</li>
                    @if ($page->expirationLabel())
                    <li>Expiration: {{ $page->expirationLabel() }}. Reissue the certificate whenever your permit number, name, or address changes.</li>
                    @else
                    <li>{{ $rule->expiration_months ? 'Renew before the certificate lapses ('.$page->expirationPhrase().').' : 'Reissue the certificate whenever your permit number, name, or address changes.' }}</li>
                    @endif
                </ol>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="{{ $page->content ? 'border-t border-zinc-200 ' : '' }}bg-white py-20">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Unlimited signed {{ $name }} resale certificates</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            Enter your business once, add each vendor, and download a signed {{ $name }} certificate on {{ $page->generatorFormPhrase() }}. One flat yearly price covers every state where you buy inventory.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Start generating certificates</a>
            <a href="{{ route('sales-tax-registration') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">Need {{ $page->salesTaxPermitPhrase() }} first?</a>
        </div>
    </div>
</section>
@endif

{{-- FAQ --}}
<section class="border-t border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} resale certificate FAQ</h2>
        <x-seo.faq class="mt-10" :items="$page->faq()" />
    </div>
</section>

{{-- Related --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">Related</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('resale-certificates') }}" class="text-zinc-700 underline hover:text-zinc-900">Resale certificate generator overview</a></li>
                    @if ($noTax)
                    <li><a href="{{ route('sales-tax-registration') }}" class="text-zinc-700 underline hover:text-zinc-900">Register for sales tax in other states</a></li>
                    @else
                    <li><a href="{{ \App\Domains\SalesTax\Seo\SalesTaxStateContent::exists($page->code) ? route('sales-tax-registration.state', ['state' => $page->slug]) : route('sales-tax-registration') }}" class="text-zinc-700 underline hover:text-zinc-900">Register for {{ $page->salesTaxLabel() }}</a></li>
                    @endif
                    <li><a href="{{ route('llc') }}" class="text-zinc-700 underline hover:text-zinc-900">Form an LLC</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">Nearby states</h2>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($nearbyStates as $nearbyCode => $nearbyName)
                    <li>
                        <a href="{{ route('resale-certificates.state', ['state' => \App\Support\Seo\States::slug($nearbyName)]) }}" class="inline-flex rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-50">
                            {{ $nearbyName }} resale certificate
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <p class="mt-10 text-xs text-zinc-500">
            This page summarizes {{ $name }} resale certificate rules for general information and is not tax advice. Confirm current requirements with the
            @if ($page->agencyUrl())
            <a href="{{ $page->agencyUrl() }}" rel="noopener" target="_blank" class="underline hover:text-zinc-900">{{ $page->agencyName() }}</a>{{ $page->isAlaska() ? ', your city or borough,' : '' }}
            @else
            {{ $page->agencyName() }}
            @endif
            or your tax adviser.
            @if ($page->researchedOn())
            State rules last checked {{ $page->researchedOn() }}.
            @endif
        </p>
    </div>
</section>
@endsection
