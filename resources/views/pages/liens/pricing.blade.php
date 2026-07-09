@extends('layouts.landing')

@section('title', 'Lien Services Pricing | Flat-Rate Mechanics Lien & Notice Filing')

@section('meta')
<meta name="description" content="Simple, flat-rate pricing for every lien service — preliminary notices, notices of intent, mechanics liens, lien releases, and payment demand letters. State fees included, free lien tracking.">
@endsection

@php
    $formatPrice = fn (int $cents) => '$'.number_format($cents / 100);

    $services = [
        [
            'key' => 'prelim_notice',
            'name' => 'Preliminary Notice',
            'icon' => '📨',
            'featured' => false,
            'route' => route('liens.preliminary-notice'),
            'description' => 'Preserve your lien rights at the start of a project.',
        ],
        [
            'key' => 'noi',
            'name' => 'Notice of Intent to Lien',
            'icon' => '⚠️',
            'featured' => false,
            'route' => route('liens.notice-of-intent-to-lien'),
            'description' => 'A formal final warning before you file a mechanics lien.',
        ],
        [
            'key' => 'mechanics_lien',
            'name' => 'Mechanics Lien',
            'icon' => '📋',
            'featured' => true,
            'route' => route('liens'),
            'description' => 'File a lien on the property to secure your payment.',
        ],
        [
            'key' => 'lien_release',
            'name' => 'Lien Release',
            'icon' => '✅',
            'featured' => false,
            'route' => route('liens.lien-release'),
            'description' => 'Remove a filed lien once you have been paid.',
        ],
        [
            'key' => 'demand_letter',
            'name' => 'Payment Demand Letter',
            'icon' => '💰',
            'featured' => false,
            'route' => route('liens.payment-demand-letter'),
            'description' => 'Demand overdue payment before escalating to a lien.',
        ],
    ];

    // Per-state price overrides (e.g. the NJ mechanics lien), keyed for cell
    // markers and rendered as footnotes so display matches the checkout charge.
    $stateNotes = collect(config('lien.state_pricing', []))
        ->flatMap(fn (array $documentTypes, string $state) => collect($documentTypes)
            ->flatMap(fn (array $variants, string $productKey) => collect($variants)
                ->map(fn (int $cents, string $level) => [
                    'key' => $productKey,
                    'state' => config("states.{$state}", $state),
                    'level' => $level,
                    'label' => str_replace('_', '-', $level),
                    'price' => $formatPrice($cents),
                ])->values()));

    $hasStateNote = fn (string $key, string $level) => $stateNotes
        ->contains(fn (array $note) => $note['key'] === $key && $note['level'] === $level);

    $serviceNames = collect($services)->pluck('name', 'key');
@endphp

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            State fees included
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Simple, Flat-Rate Pricing<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">for Every Lien Service</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">One flat price per filing — no hourly billing, no hidden costs. Standard state fees are included, and lien tracking is free.</p>
        <div class="mt-10">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Get Started
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Pricing cards --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Every Service, One Flat Price</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Choose self-serve to prepare documents yourself, or full-service and we handle filing and delivery for you.</p>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <div class="relative flex flex-col rounded-2xl bg-white p-6 transition hover:shadow-md {{ $service['featured'] ? 'border-2 border-amber-400 shadow-lg' : 'border border-zinc-200 shadow-sm' }}">
                    @if ($service['featured'])
                        <span class="absolute -top-3 left-6 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-white">Most Popular</span>
                    @endif
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-xl">{{ $service['icon'] }}</div>
                        <h3 class="font-semibold text-zinc-900">{{ $service['name'] }}</h3>
                    </div>
                    <p class="mt-3 flex-1 text-sm text-zinc-500">{{ $service['description'] }}</p>
                    <div class="mt-5 grid grid-cols-2 divide-x divide-zinc-200 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50">
                        <div class="px-4 py-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-400">Self-Serve</div>
                            <div class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900">{{ $formatPrice(config("lien.pricing.{$service['key']}.self_serve")) }}@if ($hasStateNote($service['key'], 'self_serve'))<span class="text-base text-amber-600">**</span>@endif</div>
                        </div>
                        <div class="bg-amber-50/60 px-4 py-3">
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-amber-600">Full-Service</div>
                            <div class="mt-1 text-2xl font-extrabold tracking-tight text-zinc-900">{{ $formatPrice(config("lien.pricing.{$service['key']}.full_service")) }}@if ($hasStateNote($service['key'], 'full_service'))<span class="text-base text-amber-600">**</span>@endif</div>
                        </div>
                    </div>
                    <a href="{{ $service['route'] }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-amber-600 transition hover:text-amber-700">
                        Learn more
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            @endforeach

            {{-- Free tracking portal --}}
            <div class="flex flex-col rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm transition hover:shadow-md">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-xl">📊</div>
                    <h3 class="font-semibold text-zinc-900">Lien Tracking Portal</h3>
                </div>
                <p class="mt-3 flex-1 text-sm text-zinc-500">Track projects, deadlines, and lien rights in one dashboard — for every job, in every state.</p>
                <div class="mt-5 overflow-hidden rounded-xl border border-emerald-200 bg-white px-4 py-3">
                    <div class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">Always</div>
                    <div class="mt-1 text-2xl font-extrabold tracking-tight text-emerald-600">Free</div>
                </div>
                <a href="{{ route('register') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">
                    Start tracking free
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="mx-auto mt-10 max-w-3xl space-y-1 text-center text-xs text-zinc-500">
            <p>* All prices include standard state fees. Unusually high state or county fees, counties that require documents to be physically served, and similar requirements may incur additional charges.</p>
            @foreach ($stateNotes as $note)
                <p>** {{ $note['state'] }} {{ $note['label'] }} {{ strtolower($serviceNames[$note['key']] ?? $note['key']) }}: {{ $note['price'] }}, reflecting {{ $note['state'] }}'s filing and service requirements.</p>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-zinc-900 px-8 py-4 font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-zinc-800 hover:shadow-xl">
                Get Started
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Cross-reference: lien waivers --}}
<section class="bg-white pb-24">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-zinc-200 bg-zinc-50 p-8 sm:flex-row sm:items-center">
            <div>
                <h3 class="text-xl font-bold text-zinc-900">Sending or collecting lien waivers?</h3>
                <p class="mt-2 text-zinc-600">Waivers are what get your check released before it ever comes to a lien. Generating them is free for every state, and e-signature with automatic reminders is a flat monthly subscription, priced separately from lien filings.</p>
            </div>
            <a href="{{ route('liens.lien-waivers.pricing') }}" class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-zinc-900 px-6 py-3 font-semibold text-white transition hover:bg-zinc-800">
                See lien waiver pricing
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- Self-serve vs full-service --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Self-Serve or Full-Service?</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Every option starts with state-compliant documents prepared for your project. The difference is who does the sending and filing.</p>
        </div>
        <div class="mx-auto mt-12 grid max-w-3xl gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-8">
                <h3 class="text-xl font-semibold text-zinc-900">Self-Serve</h3>
                <p class="mt-2 text-sm text-zinc-600">Best if you're comfortable mailing or recording documents yourself.</p>
                <ul class="mt-6 space-y-3 text-sm text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> State-compliant document prepared for your project</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> Instant download, ready to sign</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> Step-by-step sending and filing instructions</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> Deadline tracking in your free portal</li>
                </ul>
            </div>
            <div class="rounded-2xl border-2 border-amber-400 bg-white p-8 shadow-lg">
                <h3 class="text-xl font-semibold text-zinc-900">Full-Service</h3>
                <p class="mt-2 text-sm text-zinc-600">Best if you want it handled end-to-end with proof it was done right.</p>
                <ul class="mt-6 space-y-3 text-sm text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> Everything in Self-Serve</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> We file or record with the county where required</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> Delivery to every required party</li>
                    <li class="flex items-start gap-2"><span class="mt-0.5 text-amber-500">✓</span> Proof of service and filing confirmation</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Pricing FAQ</h2>
        </div>
        <div class="mt-12 space-y-4">
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do your prices include state filing fees?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes — standard state fees are built into every price, so what you see is what you pay. In rare cases, unusually high state or county fees, or counties that require documents to be physically served, may incur additional charges. We'll always tell you before you pay.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Are these one-time or recurring charges?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Every lien service is a one-time flat fee per document — no subscriptions and no hourly billing. The lien tracking portal is free, with no credit card required.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Why is the New Jersey mechanics lien priced differently?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">New Jersey has some of the strictest mechanics lien requirements in the country, including county recording and formal service rules that take significantly more work to complete correctly. The full-service price reflects handling all of it for you.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between self-serve and full-service?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Self-serve means we prepare the state-compliant document and you handle signing, sending, or recording it. Full-service means we do it all — preparation, filing or recording where required, delivery to every required party, and proof of service.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Protect Your Payment Today</h2>
            <p class="mt-4 text-lg text-zinc-400">Start with free lien tracking, then file the right document at the right time — at one flat price.</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50">
                    Get Started
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
