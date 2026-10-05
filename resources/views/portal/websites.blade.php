@php
    use App\Support\HappyWebsites;

    $businessName = $business->name ?? $business->legal_name ?? __('your business');
    $price = config('happy_websites.price_one_page');

    // The first example sits beside the offer. The rest go under "Some of their work".
    $examples = collect(config('happy_websites.examples'));
    $featured = $examples->first();

    // Red, as on the sales tax front door, not the blue Flux primary. The page
    // has this button twice (the offer card and the bottom band).
    $requestButton = 'flex w-full cursor-pointer items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-base font-semibold text-white transition hover:bg-red-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600';
@endphp

<x-layouts.portal :title="__('Websites')">
    <div class="mx-auto max-w-5xl px-6 py-10">
        <x-ui.page-header
            :title="__('A website for :business', ['business' => $businessName])"
            :subtitle="__('Built and run by Happy Websites, our sister company.')"
            :chip="false"
        />

        @if (session('error'))
            <x-ui.card class="mb-8 border-danger/20 bg-danger/5">
                <div class="flex items-center gap-3 text-danger">
                    <flux:icon name="x-circle" class="size-5" />
                    {{ session('error') }}
                </div>
            </x-ui.card>
        @endif

        {{-- The offer, with one site they built beside it --}}
        <div class="mt-6 grid items-start gap-8 lg:grid-cols-5">
            <div class="rounded-xl border border-border bg-white p-6 shadow-sm sm:p-8 lg:col-span-3">
                <h2 class="text-lg font-semibold text-text-primary">
                    {{ __('A free mockup first. $:price a month only if you like it.', ['price' => $price]) }}
                </h2>

                <div class="mt-3 space-y-3 text-base text-text-primary">
                    <p>{{ __('Happy Websites will design a website for :business and show it to you. You pay nothing to see it.', ['business' => $businessName]) }}</p>
                    <p>{{ __("If you like it, it's $:price a month and they put it online. No setup fee. No contract. If you don't, you owe nothing.", ['price' => $price]) }}</p>
                </div>

                @if ($requested)
                    <div class="mt-6 flex items-start gap-3 rounded-lg border border-success/20 bg-success/5 p-4 text-success">
                        <flux:icon name="check-circle" class="mt-0.5 size-5 shrink-0" />
                        <div>
                            <p class="font-medium">{{ __('Done. Happy Websites has your request.') }}</p>
                            <p class="mt-1 text-sm">
                                {{ __('They will email you at :email within one business day.', ['email' => auth()->user()->email]) }}
                            </p>
                            <p class="mt-1 text-sm">{{ __('Have a logo or photos? Keep them handy. It speeds things up.') }}</p>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('portal.websites.request') }}">
                        @csrf
                        <button type="submit" class="mt-6 {{ $requestButton }}">{{ __('Make my free mockup') }}</button>
                    </form>
                @endif
            </div>

            <a
                href="{{ HappyWebsites::url($featured['path'], 'portal') }}"
                target="_blank"
                rel="noopener"
                class="block overflow-hidden rounded-xl border border-border bg-white shadow-sm transition-all duration-200 hover:shadow-md lg:col-span-2"
            >
                <img
                    src="{{ asset($featured['image']) }}"
                    alt="{{ __(':title website built by Happy Websites', ['title' => $featured['title']]) }}"
                    width="900"
                    height="628"
                    class="w-full border-b border-border"
                />
                <div class="px-4 py-3 text-sm">
                    <span class="font-medium text-text-primary">{{ __($featured['title']) }}</span>
                    <span class="ml-1 text-text-secondary">{{ __('Built by Happy Websites') }}</span>
                </div>
            </a>
        </div>

        <section class="mt-12">
            <h2 class="mb-6 text-lg font-semibold text-text-primary">{{ __('How it works') }}</h2>

            {{-- Step 1 names everything the request email carries, so the page
                 says what it sends. Keep it in step with HappyWebsitesRequest. --}}
            <ol class="grid gap-6 sm:grid-cols-3">
                @foreach ([
                    [__('Click the button.'), __('We send Happy Websites your name, email, business name, city and state. You type nothing.')],
                    [__('They email you within one business day.'), __("They'll ask a bit about your business, then make your mockup.")],
                    [__('Like it? It goes live in about two weeks.'), __("Don't like it? You owe nothing.")],
                ] as [$step, $detail])
                    <li>
                        <div class="mb-3 flex size-8 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary" aria-hidden="true">{{ $loop->iteration }}</div>
                        <p class="font-medium text-text-primary">{{ $step }}</p>
                        <p class="mt-1 text-sm text-text-secondary">{{ $detail }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="mt-12">
            <h2 class="mb-6 text-lg font-semibold text-text-primary">{{ __('The price') }}</h2>

            <div class="rounded-xl border border-border bg-white p-6 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        [$price, __('One-page site. Everything on one page.')],
                        [config('happy_websites.price_multi_page'), __('5 to 7 pages. Room for every service.')],
                    ] as [$amount, $plan])
                        <div class="rounded-lg border border-border p-5">
                            <div class="text-3xl font-bold text-text-primary">
                                ${{ $amount }}<span class="ml-1 text-sm font-normal text-text-secondary">{{ __('a month') }}</span>
                            </div>
                            <p class="mt-2 text-sm text-text-secondary">{{ $plan }}</p>
                        </div>
                    @endforeach
                </div>

                <p class="mt-4 text-sm text-text-secondary">{{ __('Same features in both. Only the number of pages changes.') }}</p>
                <p class="mt-1 font-medium text-text-primary">{{ __('No setup fee. No contract. Cancel anytime and the site stays yours.') }}</p>

                <p class="mt-5 text-sm font-medium text-text-primary">{{ __('Both plans include') }}</p>
                <ul class="mt-2 space-y-2">
                    @foreach ([
                        __('They design it and write it for you.'),
                        __('Hosting, your domain and security are included.'),
                        __('Want a change? Email them. Changes are free.'),
                        __('Already have a website? They rebuild it. Included.'),
                        __('Live in about two weeks.'),
                        __('Real people answer, within one business day.'),
                    ] as $included)
                        <x-ui.tip>{{ $included }}</x-ui.tip>
                    @endforeach
                </ul>
            </div>
        </section>

        <section class="mt-12">
            <h2 class="mb-6 text-lg font-semibold text-text-primary">{{ __('Some of their work') }}</h2>

            <div class="grid gap-6 sm:grid-cols-3">
                @foreach ($examples->skip(1) as $example)
                    <a
                        href="{{ HappyWebsites::url($example['path'], 'portal') }}"
                        target="_blank"
                        rel="noopener"
                        class="block overflow-hidden rounded-xl border border-border bg-white shadow-sm transition-all duration-200 hover:shadow-md"
                    >
                        <img
                            src="{{ asset($example['image']) }}"
                            alt="{{ __(':title website built by Happy Websites', ['title' => $example['title']]) }}"
                            width="900"
                            height="628"
                            loading="lazy"
                            class="w-full border-b border-border"
                        />
                        <div class="px-4 py-3 text-sm font-medium text-text-primary">{{ __($example['title']) }}</div>
                    </a>
                @endforeach

                <a
                    href="{{ HappyWebsites::url('/work', 'portal') }}"
                    target="_blank"
                    rel="noopener"
                    class="flex min-h-48 flex-col items-center justify-center gap-3 rounded-xl border border-border bg-white p-6 text-center shadow-sm transition-all duration-200 hover:shadow-md"
                >
                    <span class="flex size-12 items-center justify-center rounded-lg bg-zinc-500/10 text-zinc-600">
                        <flux:icon name="globe-alt" class="size-6" />
                    </span>
                    <span class="text-sm font-medium text-primary">{{ __('See all their work at happywebsites.com') }}</span>
                </a>
            </div>
        </section>

        @unless ($requested)
            <section class="mt-12">
                <div class="rounded-xl bg-zinc-900 px-6 py-10 text-center text-white">
                    <h2 class="text-xl font-bold text-white">{{ __('Want to see your mockup?') }}</h2>
                    <p class="mt-1 mb-6 text-zinc-400">{{ __("It's free. You pay nothing unless you like it.") }}</p>
                    <form method="POST" action="{{ route('portal.websites.request') }}" class="mx-auto max-w-md">
                        @csrf
                        <button type="submit" class="{{ $requestButton }}">{{ __('Make my free mockup') }}</button>
                    </form>
                </div>
            </section>
        @endunless
    </div>
</x-layouts.portal>
