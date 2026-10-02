@props([
    'heading' => 'Related services',
    'links' => [],  // [['name' => 'Registered Agent', 'url' => route('registered-agent'), 'text' => 'One short line.']]
])
{{-- Small cross-link module near the bottom of a product page. --}}
<section {{ $attributes->merge(['class' => 'border-t border-zinc-200 bg-white py-16']) }}>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-lg font-semibold text-zinc-900">{{ $heading }}</h2>
        <ul class="mt-6 grid gap-4 sm:grid-cols-3">
            @foreach ($links as $link)
            <li>
                <a href="{{ $link['url'] }}" class="block h-full rounded-xl border border-zinc-200 p-5 transition hover:border-zinc-300 hover:bg-zinc-50">
                    <span class="font-semibold text-zinc-900">{{ $link['name'] }}</span>
                    @if (! empty($link['text']))
                    <span class="mt-1 block text-sm text-zinc-600">{{ $link['text'] }}</span>
                    @endif
                </a>
            </li>
            @endforeach
        </ul>
    </div>
</section>
