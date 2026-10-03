@extends('layouts.landing')

@section('title', 'About eRegister | eRegister')
@section('description', 'eRegister is a private document preparation and filing service for liens, lien waivers, sales tax registration, resale certificates and formations.')

@php
    // Facts come from config/company.php. No team, photos, address, phone or
    // email on this page (the owner's call, 2026-10-03).
    $since = config('company.in_business_since');
    $helped = config('company.businesses_helped');
    $helpedAsOf = config('company.businesses_helped_as_of');
@endphp

@section('content')
<section class="bg-white pb-12 pt-16 lg:pt-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="mb-6 text-zinc-500" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'About'],
        ]" />
        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl">About eRegister</h1>
        <x-reviews.google-line class="mt-4" />

        <p class="mt-8 text-lg leading-relaxed text-zinc-600">
            eRegister is a private document preparation and filing service. We prepare and file mechanics liens and
            notices, lien waivers, sales tax registrations, resale certificates and business formations in all 50
            states. eRegister is not a government agency and is not affiliated with or endorsed by any government
            agency.
        </p>

        <dl class="mt-10 grid gap-4 sm:grid-cols-2">
            @if ($since)
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
                    <dt class="text-sm text-zinc-500">In business since</dt>
                    <dd class="mt-1 text-3xl font-bold text-zinc-900">{{ $since }}</dd>
                </div>
            @endif
            @if ($helped)
                <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
                    <dt class="text-sm text-zinc-500">Businesses helped</dt>
                    <dd class="mt-1 text-3xl font-bold text-zinc-900">{{ number_format($helped) }}+</dd>
                    @if ($helpedAsOf)
                        <dd class="mt-1 text-sm text-zinc-500">As of {{ \Illuminate\Support\Carbon::parse($helpedAsOf)->format('F Y') }}</dd>
                    @endif
                </div>
            @endif
        </dl>
    </div>
</section>

<x-reviews.google-cards heading="What customers say about eRegister" class="border-t border-zinc-100" />

<section class="bg-zinc-50 py-16">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-zinc-900">How to reach us</h2>
        <p class="mt-4 text-lg text-zinc-600">
            Send us a message through our <a href="{{ route('contact') }}" class="font-medium text-blue-600 underline hover:text-blue-700">contact page</a>. We reply by email.
        </p>
    </div>
</section>
@endsection
