@props([
    // grid: the /liens hub, anchors read "{State} mechanics lien".
    // strip: small list at the bottom of the lien service pages.
    'variant' => 'grid',
])
{{-- Links to all 50 mechanics lien state pages (/liens/{state}). --}}
@php($states = \App\Support\Seo\States::names())
@if ($variant === 'strip')
<section {{ $attributes->merge(['class' => 'border-t border-zinc-200 bg-white py-12']) }}>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-sm font-semibold text-zinc-900">Mechanics lien rules in every state</h2>
        <ul class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-sm">
            @foreach ($states as $name)
            <li><a href="{{ route('liens.state', ['state' => \App\Support\Seo\States::slug($name)]) }}" class="text-zinc-600 hover:text-zinc-900 hover:underline">{{ $name }}</a></li>
            @endforeach
        </ul>
    </div>
</section>
@else
<section {{ $attributes->merge(['class' => 'bg-white py-24']) }}>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Mechanics lien rules by state</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Deadlines, notices, and filing offices differ in every state. Pick yours to see what applies.</p>
        </div>
        @php($popular = \App\Domains\Lien\Seo\LienStateDepth::popular())
        @if ($popular)
        {{-- The researched states: filing steps, county offices and recent changes. --}}
        <h3 class="mt-12 text-sm font-semibold uppercase tracking-wider text-zinc-500">Popular states</h3>
        <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($popular as $name)
            <li>
                <a href="{{ route('liens.state', ['state' => \App\Support\Seo\States::slug($name)]) }}" class="flex h-full items-center justify-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-3 text-center text-sm font-semibold text-zinc-900 transition hover:border-amber-300 hover:text-amber-700 hover:shadow-sm">{{ $name }} mechanics lien</a>
            </li>
            @endforeach
        </ul>
        <h3 class="mt-12 text-sm font-semibold uppercase tracking-wider text-zinc-500">All 50 states</h3>
        @endif
        <ul class="{{ $popular ? 'mt-4' : 'mt-12' }} grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($states as $name)
            <li>
                <a href="{{ route('liens.state', ['state' => \App\Support\Seo\States::slug($name)]) }}" class="flex h-full items-center justify-center rounded-lg border border-zinc-200 bg-white px-3 py-3 text-center text-sm font-medium text-zinc-900 transition hover:border-amber-300 hover:text-amber-700 hover:shadow-sm">{{ $name }} mechanics lien</a>
            </li>
            @endforeach
        </ul>
    </div>
</section>
@endif
