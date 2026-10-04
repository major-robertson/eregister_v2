@php
    use App\Support\HappyWebsites;

    $businessName = $business->name ?? $business->legal_name ?? __('your business');
@endphp

<x-layouts.portal :title="__('Websites')">
    <div class="mx-auto max-w-5xl px-6 py-10">
        <x-ui.page-header
            :title="__('A website for :business', ['business' => $businessName])"
            :subtitle="__('Happy Websites builds your website and keeps it running. They are our sister company.')"
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

        <div class="rounded-xl border border-border bg-white p-6 shadow-sm sm:p-8">
            <p class="text-base font-medium text-text-primary">
                {{ __('They will make a free mockup first. You see it before you pay anything.') }}
            </p>

            <ul class="mt-5 space-y-3 text-sm text-text-primary sm:text-base">
                @foreach ([
                    __('They design it and write it for you.'),
                    __('Hosting, your domain and security are included.'),
                    __('Want a change? Email them. Changes are free.'),
                    __('Live in about two weeks.'),
                ] as $point)
                    <li class="flex items-start gap-3">
                        <flux:icon name="check" class="mt-0.5 size-5 shrink-0 text-success" />
                        <span>{{ $point }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-6 text-sm text-text-secondary sm:text-base">
                {{ __('$:one a month for a one-page site. $:multi a month for 5 to 7 pages. No setup fee. No contract.', [
                    'one' => config('happy_websites.price_one_page'),
                    'multi' => config('happy_websites.price_multi_page'),
                ]) }}
            </p>

            <div class="mt-6 border-t border-border pt-6">
                @if ($requested)
                    <div class="flex items-start gap-3 rounded-lg border border-success/20 bg-success/5 p-4 text-success">
                        <flux:icon name="check-circle" class="mt-0.5 size-5 shrink-0" />
                        <div>
                            <p class="font-medium">{{ __('Done. Happy Websites has your request.') }}</p>
                            <p class="mt-1 text-sm">
                                {{ __('They will email you at :email within one business day.', ['email' => auth()->user()->email]) }}
                            </p>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('portal.websites.request') }}">
                        @csrf
                        <flux:button type="submit" variant="primary">{{ __('Make my free mockup') }}</flux:button>
                    </form>
                    <p class="mt-3 text-sm text-text-secondary">
                        {{ __("We'll send your name, email, business name, city and state to Happy Websites. They will email you within one business day.") }}
                    </p>
                @endif
            </div>
        </div>

        <section class="mt-12">
            <h2 class="mb-6 text-lg font-semibold text-text-primary">{{ __('Websites they have built') }}</h2>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                @foreach (config('happy_websites.examples') as $example)
                    <a
                        href="{{ HappyWebsites::url($example['path'], 'portal') }}"
                        target="_blank"
                        rel="noopener"
                        class="group block overflow-hidden rounded-xl border border-border bg-white shadow-sm transition-all duration-200 hover:shadow-md"
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
            </div>

            <p class="mt-6 text-sm">
                <flux:link :href="HappyWebsites::url('/work', 'portal')" target="_blank" rel="noopener">
                    {{ __('See more of their work at happywebsites.com') }}
                </flux:link>
            </p>
        </section>
    </div>
</x-layouts.portal>
