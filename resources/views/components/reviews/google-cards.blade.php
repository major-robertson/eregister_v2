{{--
    Customer reviews from the company's Google listing (config/company.php),
    quoted word for word. They are about the lien filing service, and the
    section says so.
--}}
@props([
    // Lien pages talk to contractors; the sales tax pages pass a broader heading.
    'heading' => 'What contractors say about eRegister',
])

@php
    $google = config('company.google_reviews');
    $reviews = $google['featured'] ?? [];
@endphp

@if ($reviews !== [])
<section id="reviews" {{ $attributes->class(['scroll-mt-6 bg-white py-16 lg:py-20']) }}>
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">{{ $heading }}</h2>
            <x-reviews.google-line class="mt-3 justify-center" />
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-2">
            @foreach ($reviews as $review)
                <figure class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
                    <div class="flex items-center gap-0.5 text-amber-400" aria-hidden="true">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.29 3.97a1 1 0 00.95.69h4.17c.97 0 1.37 1.24.59 1.81l-3.38 2.45a1 1 0 00-.36 1.12l1.29 3.97c.3.92-.76 1.69-1.54 1.12l-3.37-2.45a1 1 0 00-1.18 0l-3.37 2.45c-.78.57-1.84-.2-1.54-1.12l1.29-3.97a1 1 0 00-.36-1.12L2.05 9.4c-.78-.57-.38-1.81.59-1.81h4.17a1 1 0 00.95-.69l1.29-3.97z"/></svg>
                        @endfor
                    </div>
                    <blockquote class="mt-3 text-zinc-700">&ldquo;{{ $review['text'] }}&rdquo;</blockquote>
                    <figcaption class="mt-4 text-sm font-medium text-zinc-900">{{ $review['name'] }} <span class="font-normal text-zinc-500">&middot; Google review</span></figcaption>
                </figure>
            @endforeach
        </div>

        <p class="mt-8 text-center text-sm text-zinc-500">
            Reviews of eRegister's lien filing service.
            <a href="{{ $google['url'] }}" target="_blank" rel="noopener" class="font-medium text-zinc-700 underline hover:text-zinc-900">Read them on Google</a>
        </p>
    </div>
</section>
@endif
