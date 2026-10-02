@extends('layouts.landing')

@section('title', $page->title())
@section('description', $page->metaDescription())
@section('canonical', $page->url())

@section('content')
@php
    /** @var \App\Domains\Lien\Seo\NoticeOfIntentStatePage $page */
    $name = $page->name;
    $blankUrl = $page->blankUrl();
    $contents = $page->contents();
    $required = $page->isRequired();
@endphp

<x-seo.service
    name="{{ $name }} Notice of Intent to Lien"
    description="We prepare {{ $page->article }} {{ $name }} notice of intent to lien from your project details and send it to the right parties, with proof of delivery."
    :url="$page->url()"
    :price="$page->selfServePrice()"
    category="Notice of intent to lien" />

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="text-zinc-400" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Mechanics Liens', 'url' => route('liens')],
            ['name' => 'Notice of Intent to Lien', 'url' => route('liens.notice-of-intent-to-lien')],
            ['name' => $name],
        ]" />
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $name }} Notice of Intent to Lien</h1>
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">{{ $page->ruleSentence() }}</p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Send {{ $page->article }} {{ $name }} notice from ${{ $page->selfServePrice() }}
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
            @if ($blankUrl)
            <a href="{{ $blankUrl }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-8 py-4 text-base font-semibold text-zinc-200 transition hover:bg-zinc-800" download>
                Download the free blank form
            </a>
            @endif
        </div>
    </div>
</section>

{{-- Requirement and key facts --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Does {{ $name }} require a notice of intent to lien?</h2>
        <div class="mt-4 max-w-3xl space-y-4 text-zinc-600">
            <p><strong class="font-semibold text-zinc-900">{{ $page->requirementLabel() }}.</strong> {{ $page->ruleSentence() }}</p>
            @unless ($required)
            <p>{{ $page->whySend() }}</p>
            @endunless
        </div>
        <dl class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($page->keyFacts() as $fact)
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
                <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $fact['label'] }}</dt>
                <dd class="mt-2 text-lg font-semibold text-zinc-900">{{ $fact['value'] }}</dd>
                @if (! empty($fact['detail']))
                <dd class="mt-1 text-sm text-zinc-600">{{ $fact['detail'] }}</dd>
                @endif
            </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Who, to whom, how --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Who sends it, to whom, and how</h2>
        <dl class="mt-10 space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <dt class="font-semibold text-zinc-900">Who sends it</dt>
                <dd class="mt-2 text-zinc-600">{{ $page->who() }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <dt class="font-semibold text-zinc-900">Who receives it</dt>
                <dd class="mt-2 text-zinc-600">{{ $page->to() }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <dt class="font-semibold text-zinc-900">How to send it</dt>
                <dd class="mt-2 text-zinc-600">{{ $page->how() }}</dd>
            </div>
        </dl>
    </div>
</section>

{{-- What it says --}}
<section id="contents" class="bg-white py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">What {{ $page->article }} {{ $name }} notice of intent says</h2>
        <p class="mt-4 text-zinc-600">
            @if ($contents['statutory'])
            {{ $name }} law requires the notice to state the following{{ $contents['cite'] ? ' ('.$contents['cite'].')' : '' }}.
            @else
            The notice our service prepares states the following. Keep it to facts you can prove.
            @endif
        </p>
        <ul class="mt-8 space-y-3">
            @foreach ($contents['items'] as $item)
            <li class="flex gap-3 text-zinc-700">
                <svg class="mt-1 h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ $item }}</span>
            </li>
            @endforeach
        </ul>

        @if ($blankUrl)
        <div id="free-form" class="mt-10 rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
            <a href="{{ $blankUrl }}" class="font-semibold text-zinc-900 underline" download>Download a blank {{ $name }} notice of intent to lien</a>
            <p class="mt-2 text-sm text-zinc-600">
                The {{ $page->blankTitle }} our service prepares, with every field left blank. PDF, free, no sign-up.
                Fill in your own facts and check them against the rules on this page before you send it.
            </p>
        </div>
        @endif
    </div>
</section>

{{-- The lien deadline it protects --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">The {{ $name }} lien deadline it protects</h2>
        <p class="mt-4 text-zinc-600">
            {{ $page->lienDeadlineSentence() ?? "See the {$name} lien page for the filing deadline." }}
            The lien is filed with the {{ $page->lien->filingLocationPhrase() }}.
            @if ($required)
            Time the notice so you can still file the lien by that deadline.
            @else
            Send the notice early enough that the payment window ends before that deadline.
            @endif
        </p>
        <p class="mt-4">
            <a href="{{ route('liens.state', ['state' => $page->slug]) }}" class="font-medium text-zinc-900 underline">See every {{ $name }} lien deadline and filing rule</a>
        </p>

        @if ($page->notes())
        <div class="mt-10 rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h3 class="font-semibold text-amber-900">More {{ $name }} rules to know</h3>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-relaxed text-amber-900/80">
                @foreach ($page->notes() as $note)
                <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <p class="mt-8 text-sm text-zinc-500">
            Source: {{ $page->entry['cite'] }}.
            @if ($page->lien->rule->statute_url)
            <a href="{{ $page->lien->rule->statute_url }}" rel="noopener" target="_blank" class="font-medium text-zinc-700 underline">Read the {{ $name }} lien statute</a>.
            @endif
            Notice rules turn on facts specific to your project. Confirm with counsel before relying on them.
        </p>
    </div>
</section>

{{-- Pricing / CTA --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Send your {{ $name }} notice of intent</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            We prepare the notice from your project details. Self-serve from ${{ $page->selfServePrice() }}: you review, sign and send it.
            Full service from ${{ $page->fullServicePrice() }}: we send it to every party with proof of delivery.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Start {{ $page->article }} {{ $name }} notice</a>
            <a href="{{ route('liens.notice-of-intent-to-lien') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">How the notice service works</a>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="border-t border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} notice of intent FAQ</h2>
        <x-seo.faq class="mt-10" :items="$page->faq()" />
    </div>
</section>

@include('pages.liens.partials.variant-links', ['page' => $page, 'variantRoute' => \App\Domains\Lien\Seo\NoticeOfIntentStatePage::ROUTE, 'variantLabel' => 'notice of intent'])
@endsection
