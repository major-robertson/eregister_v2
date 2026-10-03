@extends('layouts.landing')

@section('title', 'Mechanics Lien Deadline Calculator')
@section('description', 'Free mechanics lien deadline calculator for all 50 states. Enter your role and project dates to see your preliminary notice, intent to lien, and lien deadlines.')

@section('content')
@php
    $faq = [
        [
            'q' => 'How is a mechanics lien deadline calculated?',
            'a' => 'Each state counts from a trigger date, usually the last day you furnished labor or materials or the day the project was completed. Some states count days, some count months, and Texas counts to the 15th day of a later month. The calculator applies your state\'s rule for your role and project type to the dates you enter.',
        ],
        [
            'q' => 'What counts as my last furnishing date?',
            'a' => 'The last day you performed work or delivered materials under your contract. Small corrections and warranty work often do not count, so use the last day of real work on the job.',
        ],
        [
            'q' => 'Does a notice of completion change my deadline?',
            'a' => 'In some states, yes. When the owner files a notice of completion, the lien deadline can move earlier. In those states the calculator asks for the notice date and shows whichever deadline comes first.',
        ],
        [
            'q' => 'Why does my role change the deadline?',
            'a' => 'Lien rights and deadlines depend on who hired you. A general contractor hired by the owner often has a different deadline, and often no preliminary notice, compared with a subcontractor or supplier further down the chain.',
        ],
        [
            'q' => 'What if a deadline falls on a weekend or holiday?',
            'a' => 'The calculator shows the date the statute\'s count lands on. Some states extend a deadline that falls on a weekend or holiday and some do not, so plan to file before the date shown.',
        ],
    ];
@endphp

<x-seo.service
    name="Mechanics Lien Deadline Calculator"
    description="Free calculator that turns a project's dates into the preliminary notice, notice of intent, and mechanics lien deadlines for any state."
    category="Mechanics lien deadline calculator" />

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="text-zinc-400" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Mechanics Liens', 'url' => route('liens')],
            ['name' => 'Deadline Calculator'],
        ]" />
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Mechanics Lien<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Deadline Calculator</span>
        </h1>
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">
            Pick your state and role, enter your project dates, and see when your preliminary notice, notice of intent, and lien are due.
            It runs on the same rules our lien filing service uses.
        </p>
    </div>
</section>

{{-- Calculator --}}
<section class="bg-zinc-50 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Find your deadlines</h2>
        <p class="mt-2 text-zinc-600">The calculator asks only for the dates your state's rules use. To compare the rules side by side, see <a href="{{ route('guides.show', ['slug' => 'mechanics-lien-deadlines-by-state']) }}" class="font-medium text-zinc-900 underline">mechanics lien deadlines by state</a>.</p>
        <x-seo.lien-deadline-calculator class="mt-6" :exports="$calculatorRules" />
    </div>
</section>

{{-- FAQ --}}
<section class="border-t border-zinc-200 bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight text-zinc-900">How lien deadlines work</h2>
        <x-seo.faq class="mt-10" card="zinc" :items="$faq" />
    </div>
</section>

{{-- Related lien tools --}}
<x-seo.lien-more class="border-t border-zinc-200" />

{{-- Every state page --}}
<x-seo.lien-states variant="strip" />
@endsection
