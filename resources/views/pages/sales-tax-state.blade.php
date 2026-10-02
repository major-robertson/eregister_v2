@extends('layouts.landing')

@section('title', $page->title())
@section('description', $page->metaDescription())
@section('canonical', $page->url())

@section('content')
@php
    /** @var \App\Domains\SalesTax\Seo\SalesTaxStatePage $page */
    $name = $page->name;
    $filesWithUs = $page->filesWithUs();
    $price = $filesWithUs ? $page->price() : null;
    $portal = $page->portal();
@endphp

<x-seo.service
    name="{{ $name }} Sales Tax Registration"
    :description="$filesWithUs
        ? 'We prepare and file your '.$page->termWithState().' application with the '.$page->agencyName().'.'
        : 'How to register for '.$name.' sales tax with the '.$page->agencyName().': the application, the fee, the timing and what to file afterwards.'"
    :url="$page->url()"
    :price="$filesWithUs ? $page->priceDollars() : null"
    category="Sales tax registration" />

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="text-zinc-400" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Sales Tax Registration', 'url' => route('sales-tax-registration')],
            ['name' => $name],
        ]" />
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $name }} Sales Tax Registration</h1>
        <p class="mt-6 max-w-2xl text-lg font-semibold text-zinc-200">{{ $page->heroLine() }}</p>
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">
            Who has to register in {{ $page->inName }}, how to apply, what it costs, how long it takes, and what you file once you have it.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            @if ($filesWithUs)
            <a href="{{ $page->registerUrl() }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Register me in {{ $page->inName }}
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
            @elseif ($portal && $portal['url'])
            <a href="{{ $portal['url'] }}" rel="noopener" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">
                Apply with {{ $page->agencyShort() }}
            </a>
            @endif
            <a href="{{ route('sales-tax-registration') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-8 py-4 text-base font-semibold text-zinc-200 transition hover:bg-zinc-800">
                How registration works
            </a>
        </div>
    </div>
</section>

{{-- Key facts --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} sales tax registration at a glance</h2>
        <dl class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($page->keyFacts() as $fact)
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
                <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $fact['label'] }}</dt>
                <dd class="mt-2 text-lg font-semibold text-zinc-900">{{ $fact['value'] }}</dd>
                @if ($fact['detail'] !== '')
                <dd class="mt-1 text-sm text-zinc-600">{{ $fact['detail'] }}</dd>
                @endif
            </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- How to register, and what comes after --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900">How to register in {{ $page->inName }}</h2>
                <dl class="mt-6 space-y-5">
                    @foreach ($page->howToRegister() as $step)
                    <div>
                        <dt class="text-sm font-semibold text-zinc-900">{{ $step['label'] }}</dt>
                        <dd class="mt-1 text-zinc-600">
                            {{ $step['text'] }}
                            @if (! empty($step['link']))
                            <a href="{{ $step['link']['url'] }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">{{ $step['link']['label'] }}</a>
                            @endif
                        </dd>
                    </div>
                    @endforeach
                </dl>
            </div>
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900">After you register</h2>
                <dl class="mt-6 space-y-5">
                    @foreach ($page->afterRegistering() as $item)
                    <div>
                        <dt class="text-sm font-semibold text-zinc-900">{{ $item['label'] }}</dt>
                        <dd class="mt-1 text-zinc-600">{{ $item['text'] }}</dd>
                    </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </div>
</section>

{{-- Researched state rules, with sources --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} rules in plain English</h2>
                @foreach ($page->notesParagraphs() as $paragraph)
                <p class="mt-6 text-zinc-600">{{ $paragraph }}</p>
                @endforeach
                @if ($page->sourceLinks())
                <p class="mt-6 text-sm text-zinc-600">
                    Sources:
                    @foreach ($page->sourceLinks() as $link)
                    <a href="{{ $link['url'] }}" rel="noopener" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">{{ $link['label'] }}</a>{{ $loop->last ? '' : ' ·' }}
                    @endforeach
                </p>
                @endif
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
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

{{-- CTA --}}
<section class="border-t border-zinc-200 bg-white py-20">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        @if ($filesWithUs)
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">We register you in {{ $page->inName }}</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            Answer a few questions about your business. We prepare your {{ $page->termWithState() }} application, file it with {{ $page->agencyShort() }}, and send you the number.
            @if ($price)
            {{ $price }} per state. Any state fee is separate.
            @endif
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ $page->registerUrl() }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Start my {{ $name }} registration</a>
        </div>
        @else
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Register for {{ $name }} sales tax yourself with this guide, or ask us</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            Our online application does not cover {{ $page->inName }} yet. Follow the steps above to apply with the {{ $page->agencyName() }}, or contact us about your registration.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            @if ($portal && $portal['url'])
            <a href="{{ $portal['url'] }}" rel="noopener" target="_blank" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Apply with {{ $page->agencyShort() }}</a>
            @endif
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">Ask us</a>
        </div>
        @endif
    </div>
</section>

{{-- FAQ --}}
<section class="border-t border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} sales tax registration FAQ</h2>
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
                    @if ($resaleUrl)
                    <li><a href="{{ $resaleUrl }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $name }} resale certificate rules</a></li>
                    @endif
                    <li><a href="{{ route('sales-tax-registration') }}" class="text-zinc-700 underline hover:text-zinc-900">Sales tax registration in every state</a></li>
                    <li><a href="{{ route('llc') }}" class="text-zinc-700 underline hover:text-zinc-900">Form an LLC</a></li>
                    @if ($lienUrl)
                    <li><a href="{{ $lienUrl }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $name }} mechanics lien rules</a></li>
                    @endif
                </ul>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">Nearby states</h2>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($nearbyStates as $nearbyCode => $nearbyName)
                    <li>
                        <a href="{{ route('sales-tax-registration.state', ['state' => \App\Support\Seo\States::slug($nearbyName)]) }}" class="inline-flex rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-50">
                            {{ $nearbyName }} sales tax registration
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <p class="mt-10 text-xs text-zinc-500">
            This page summarizes {{ $name }} sales tax registration rules for general information and is not tax advice. Confirm current requirements with the
            @if ($page->agencyUrl())
            <a href="{{ $page->agencyUrl() }}" rel="noopener" target="_blank" class="underline hover:text-zinc-900">{{ $page->agencyName() }}</a>
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
