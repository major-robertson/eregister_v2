@extends('layouts.landing')

@section('title', $page->title())
@section('description', $page->metaDescription())
@section('canonical', $page->url())

@section('content')
@php
    /** @var \App\Domains\Lien\Seo\LienReleaseStatePage $page */
    $name = $page->name;
    $blankUrl = $page->blankUrl();
    $office = $page->officePhrase();
    $documentName = $page->documentName();
@endphp

<x-seo.service
    name="{{ $name }} Mechanics Lien Release"
    description="We prepare the {{ mb_strtolower($documentName) }} that clears a paid {{ $name }} mechanics lien from the public record, ready to sign and record."
    :url="$page->url()"
    :price="$page->selfServePrice()"
    category="Mechanics lien release" />

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="text-zinc-400" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Mechanics Liens', 'url' => route('liens')],
            ['name' => 'Lien Release', 'url' => route('liens.lien-release')],
            ['name' => $name],
        ]" />
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">{{ $name }} Mechanics Lien Release</h1>
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">
            Once a {{ $name }} lien is paid, a {{ mb_strtolower($documentName) }} clears it from the public record.
            @if ($page->deadline() !== null)
            Here is when it is due, what happens if you wait, and where it is recorded.
            @else
            Here is what we know about releasing one, and what to confirm with counsel.
            @endif
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Release {{ $page->article }} {{ $name }} lien from ${{ $page->selfServePrice() }}
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

{{-- What it is, and key facts --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">What a lien release is in {{ $name }}</h2>
        <div class="mt-4 max-w-3xl space-y-4 text-zinc-600">
            @if ($page->hasStatutoryName())
            <p>{{ $name }} law calls it a {{ mb_strtolower($documentName) }}@if ($page->cite()) ({{ $page->cite() }})@endif. It is the claimant's signed statement that the lien is paid or released, and it clears the lien of record.</p>
            @else
            <p>We have not confirmed a statutory name for it in {{ $name }}. A release of lien is the usual title: the claimant's signed statement that the lien is paid or released. Confirm the form with counsel.</p>
            @endif
            <p>A lien that stays on the record after payment can hold up a sale or a refinance. Release it promptly, even when no one has asked yet.</p>
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

{{-- Deadline, penalty, office --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">When to release a paid {{ $name }} lien</h2>
        <dl class="mt-10 space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <dt class="font-semibold text-zinc-900">Deadline after payment</dt>
                <dd class="mt-2 text-zinc-600">{{ $page->deadlineSentence() }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <dt class="font-semibold text-zinc-900">If you do not release it</dt>
                <dd class="mt-2 text-zinc-600">{{ $page->penaltySentence() }}</dd>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <dt class="font-semibold text-zinc-900">Where it is recorded</dt>
                <dd class="mt-2 text-zinc-600">
                    @if ($office)
                    Record it with the {{ $office }}. Bring the lien's recording number or book and page so the release can be matched to it.
                    @else
                    We could not confirm where {{ $page->article }} {{ $name }} release is filed. Confirm with counsel.
                    @endif
                </dd>
            </div>
        </dl>

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

        @if ($blankUrl)
        <div id="free-form" class="mt-10 rounded-2xl border border-zinc-200 bg-white p-6">
            <a href="{{ $blankUrl }}" class="font-semibold text-zinc-900 underline" download>Download a blank {{ $name }} lien release form</a>
            <p class="mt-2 text-sm text-zinc-600">
                The {{ $page->blankTitle }} our service prepares, with every field left blank. PDF, free, no sign-up.
                Fill in the lien's recording details and your own facts before you sign it.
            </p>
        </div>
        @endif

        <p class="mt-8 text-sm text-zinc-500">
            @if ($page->cite()) Source: {{ $page->cite() }}. @endif
            @if ($page->lien->rule->statute_url)
            <a href="{{ $page->lien->rule->statute_url }}" rel="noopener" target="_blank" class="font-medium text-zinc-700 underline">Read the {{ $name }} lien statute</a>.
            @endif
            Release rules turn on facts specific to your lien. Confirm with counsel before relying on them.
        </p>
    </div>
</section>

{{-- Pricing / CTA --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Release your {{ $name }} lien</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            We prepare the release from your lien's recording details. Self-serve from ${{ $page->selfServePrice() }}: you review, sign and record it.
            Full service from ${{ $page->fullServicePrice() }}: we handle the recording for you.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Start {{ $page->article }} {{ $name }} lien release</a>
            <a href="{{ route('liens.lien-release') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">How the release service works</a>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="border-t border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} lien release FAQ</h2>
        <x-seo.faq class="mt-10" :items="$page->faq()" />
    </div>
</section>

@include('pages.liens.partials.variant-links', ['page' => $page, 'variantRoute' => \App\Domains\Lien\Seo\LienReleaseStatePage::ROUTE, 'variantLabel' => 'lien release'])
@endsection
