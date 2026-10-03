@extends('layouts.landing')

@section('title', 'Guides to Liens, Lien Waivers and Sales Tax')
@section('description', 'Plain-English guides to mechanics liens, preliminary notices and sales tax. Each one cites the statute or agency page and links to the rules for every state.')

@section('content')
<section class="bg-white pb-12 pt-16 lg:pt-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="mb-6 text-zinc-500" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Guides'],
        ]" />
        <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl">Guides</h1>
        <p class="mt-6 max-w-3xl text-lg leading-relaxed text-zinc-600">
            These guides explain the rules behind liens, lien waivers and sales tax registration, state by state.
            Each one cites the statute or the state agency page it relies on and links to the full rules for each state.
            They are general information, not legal advice.
        </p>
    </div>
</section>

@foreach ($clusters as $key => $cluster)
<section class="border-t border-zinc-200 bg-zinc-50 py-14">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $cluster['label'] }}</h2>
        <ul class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach ($cluster['guides'] as $guide)
            <li>
                <a href="{{ route('guides.show', ['slug' => $guide['slug']]) }}" class="block h-full rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-zinc-300 hover:shadow-sm">
                    <h3 class="text-lg font-semibold text-zinc-900">{{ $guide['title'] }}</h3>
                    <p class="mt-2 text-sm text-zinc-600">{{ $guide['summary'] }}</p>
                    <p class="mt-4 text-xs text-zinc-500">Updated <time datetime="{{ $guide['updated'] }}">{{ \App\Support\Seo\Guides::displayDate($guide['updated']) }}</time></p>
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</section>
@endforeach
@endsection
