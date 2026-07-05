@props([
    'productKey',
    'documentName',
    'title',
    'selfServeText' => null,
    'fullServiceText' => null,
])

@php
    $pricing = config("lien.pricing.{$productKey}");
    $formatPrice = fn (int $cents) => '$'.number_format($cents / 100);

    // Per-state price overrides for this document type (e.g. the NJ mechanics
    // lien) so the advertised price always matches what checkout charges.
    $stateNotes = collect(config('lien.state_pricing', []))
        ->flatMap(fn (array $documentTypes, string $state) => collect($documentTypes[$productKey] ?? [])
            ->map(fn (int $cents, string $level) => [
                'state' => config("states.{$state}", $state),
                'level' => $level,
                'label' => str_replace('_', '-', $level),
                'price' => $formatPrice($cents),
            ])->values());

    $selfServeText ??= "We generate your state-compliant {$documentName} — you review, sign, and send it yourself.";
    $fullServiceText ??= "We prepare your {$documentName} and deliver it to every required party, with proof of service.";
@endphp

<section id="pricing" {{ $attributes->merge(['class' => 'py-24']) }}>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Simple, Flat-Rate Pricing</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">{{ $title }}</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">One flat price, state fees included.* No hourly billing, no surprises.</p>
        </div>

        <div class="mx-auto mt-12 grid max-w-3xl gap-6 sm:grid-cols-2">
            {{-- Self-Serve --}}
            <div class="flex flex-col rounded-2xl border border-zinc-200 bg-white p-8">
                <h3 class="font-semibold text-zinc-900">Self-Serve</h3>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-5xl font-extrabold tracking-tight text-zinc-900">{{ $formatPrice($pricing['self_serve']) }}@if ($stateNotes->contains('level', 'self_serve'))<span class="text-2xl text-amber-600">**</span>@endif</span>
                    <span class="text-sm text-zinc-500">one-time</span>
                </div>
                <p class="mt-4 flex-1 text-sm text-zinc-600">{{ $selfServeText }}</p>
                <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center rounded-lg border border-zinc-300 px-6 py-3 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50">
                    Get Started
                </a>
            </div>

            {{-- Full-Service --}}
            <div class="relative flex flex-col rounded-2xl border-2 border-amber-400 bg-white p-8 shadow-lg">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-white">Most Popular</span>
                <h3 class="font-semibold text-zinc-900">Full-Service</h3>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-5xl font-extrabold tracking-tight text-zinc-900">{{ $formatPrice($pricing['full_service']) }}@if ($stateNotes->contains('level', 'full_service'))<span class="text-2xl text-amber-600">**</span>@endif</span>
                    <span class="text-sm text-zinc-500">one-time</span>
                </div>
                <p class="mt-4 flex-1 text-sm text-zinc-600">{{ $fullServiceText }}</p>
                <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-zinc-900 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                    Get Started
                </a>
            </div>
        </div>

        <div class="mx-auto mt-8 max-w-2xl space-y-1 text-center text-xs text-zinc-500">
            <p>* All prices include standard state fees. Unusually high state or county fees, counties that require documents to be physically served, and similar requirements may incur additional charges.</p>
            @foreach ($stateNotes as $note)
                <p>** {{ $note['state'] }} {{ $note['label'] }} {{ $documentName }}: {{ $note['price'] }}, reflecting {{ $note['state'] }}'s filing and service requirements.</p>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('liens.pricing') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 transition hover:text-amber-700">
                Compare pricing for all lien services
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>
