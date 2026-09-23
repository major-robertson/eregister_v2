@extends('layouts.landing')

@section('title', $page->title())
@section('description', $page->metaDescription())
@section('canonical', $page->url())

@section('content')
@php
    /** @var \App\Domains\ResaleCert\Seo\ResaleStatePage $page */
    $name = $page->name;
    $rule = $page->rule;
@endphp

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
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">
            What a {{ $name }} resale certificate has to say, who can sign one, how long it lasts, and how to produce a signed copy for every vendor in minutes.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Generate a {{ $name }} certificate
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

{{-- What goes on the certificate --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900">What a {{ $name }} resale certificate must include</h2>
                <ul class="mt-6 space-y-3 text-zinc-600">
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>The buyer's legal name, business address, and {{ $rule->accepts_out_of_state ? 'sales tax registration number from '.$name.' or their home state' : $name.' sales tax permit number' }}.</li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>The seller's name and address.</li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>A description of the property being purchased for resale{{ $rule->allows_blanket && $rule->default_blanket_text ? ', or for a blanket certificate a general description such as "'.$rule->default_blanket_text.'"' : '' }}.</li>
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>A statement that the items will be resold in the ordinary course of business, signed and dated by the buyer.</li>
                    @if ($rule->expiration_months)
                    <li class="flex gap-3"><span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>A date, because {{ $name }} certificates are {{ $page->expirationPhrase() }} and vendors need to know when to ask for a new one.</li>
                    @endif
                </ul>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-8">
                <h3 class="text-lg font-semibold text-zinc-900">Using your {{ $name }} certificate</h3>
                <ol class="mt-4 list-decimal space-y-3 pl-5 text-sm text-zinc-600">
                    <li>Confirm the purchase is genuinely for resale. Using a certificate for supplies or equipment you consume is the most common audit finding.</li>
                    <li>Give the vendor the signed certificate before or at the time of the sale; a certificate produced after the fact is often rejected.</li>
                    <li>{{ $rule->allows_blanket ? 'Issue one blanket certificate per vendor and keep a copy for your records.' : 'Issue a fresh certificate for each purchase and keep copies for your records.' }}</li>
                    <li>{{ $rule->expiration_months ? 'Renew before the certificate lapses ('.$page->expirationPhrase().').' : 'Reissue the certificate whenever your permit number, name, or address changes.' }}</li>
                </ol>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Unlimited signed {{ $name }} resale certificates</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            Enter your business once, add each vendor, and download a signed {{ $name }} certificate on {{ $page->hasOfficialForm() ? 'the official state form' : 'a compliant form' }}. One flat yearly price covers every state where you buy inventory.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Start generating certificates</a>
            <a href="{{ route('sales-tax-registration') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">Need a {{ $name }} sales tax permit first?</a>
        </div>
    </div>
</section>

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
                    <li><a href="{{ route('sales-tax-registration') }}" class="text-zinc-700 underline hover:text-zinc-900">Register for {{ $name }} sales tax</a></li>
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
            This page summarizes {{ $name }} resale certificate rules for general information and is not tax advice. Confirm current requirements with the {{ $name }} Department of Revenue or your tax adviser.
        </p>
    </div>
</section>
@endsection
