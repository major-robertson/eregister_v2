@props([
    // 'sales-tax' or 'resale-cert': the service the ad promised, shown first.
    'product' => 'sales-tax',
    'state' => null,
    'intent' => null,
    // The state's own name for the registration, when the page knows it.
    'term' => null,
    'permitPrice' => '$199',
    'generatorPrice' => '$297',
    'heading' => null,
])

@php
    // Three doors, the advertised one first. A company with permits in some
    // states may still need registration in others, so nothing here turns a
    // visitor away because of a permit they already hold.
    $stateName = $state ? config("states.{$state}") : null;
    $q = array_filter(['state' => $state, 'intent' => $intent]);
    $registerUrl = route('register', ['product' => 'sales-tax'] + $q);
    $certificateUrl = route('register', ['product' => 'resale-cert'] + array_filter(['state' => $state]));
    $doors = [
        'sales-tax' => [
            'title' => $stateName ? "Register for a {$stateName} ".($term ?? 'sales tax permit') : 'Register for a sales tax permit',
            'text' => "One state or several. We prepare and file the registration and send you the number. {$permitPrice} per state.",
            'button' => 'Start my registration',
            'href' => $registerUrl,
        ],
        'resale-cert' => [
            'title' => $stateName ? "Create a {$stateName} resale certificate" : 'Create a resale certificate',
            'text' => "Already registered? Generate signed, vendor-ready certificates for every state you buy in. {$generatorPrice} a year, unlimited.",
            'button' => 'Create my certificate',
            'href' => $certificateUrl,
        ],
        'not-sure' => [
            'title' => 'Not sure what I need',
            'text' => 'Tell us your state and what you sell. If you have no permit yet, registration comes first and the certificates follow.',
            'button' => 'Help me choose',
            // Registration is the safe default: a resale certificate needs a permit number.
            'href' => route('register', ['product' => 'sales-tax', 'intent' => 'not-sure'] + array_filter(['state' => $state])),
        ],
    ];
    $order = $product === 'resale-cert' ? ['resale-cert', 'sales-tax', 'not-sure'] : ['sales-tax', 'resale-cert', 'not-sure'];
@endphp

<div id="start" {{ $attributes->merge(['class' => 'scroll-mt-6 rounded-2xl border border-zinc-200 bg-white p-5 shadow-xl sm:p-7']) }}>
    <h2 class="text-xl font-bold text-zinc-900">{{ $heading ?? 'What do you need today?' }}</h2>
    <p class="mt-1 text-sm text-zinc-500">Pick one. You can change your mind inside your account.</p>
    <div class="mt-5 space-y-3">
        @foreach ($order as $i => $key)
            @php $door = $doors[$key]; $primary = $i === 0; @endphp
            {{-- The whole card is the link. On phones a chevron marks it as a tap
                 target and the button runs full width under the text; from sm up
                 the button sits beside the text as before. --}}
            <a href="{{ $door['href'] }}" data-door="{{ $key }}"
               class="block rounded-xl border p-4 transition {{ $primary ? 'border-amber-500 bg-amber-50 hover:bg-amber-100' : 'border-zinc-200 hover:border-zinc-400 hover:bg-zinc-50' }}">
                <span class="flex items-start justify-between gap-4">
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-zinc-900">{{ $door['title'] }}</span>
                        <span class="mt-1 block text-sm text-zinc-600">{{ $door['text'] }}</span>
                    </span>
                    <span class="mt-0.5 hidden shrink-0 rounded-full px-3 py-1.5 text-xs font-semibold sm:inline-block {{ $primary ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-700' }}">{{ $door['button'] }}</span>
                    <span aria-hidden="true" class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full sm:hidden {{ $primary ? 'bg-amber-200 text-amber-900' : 'bg-zinc-100 text-zinc-500' }}">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6" /></svg>
                    </span>
                </span>
                <span class="mt-3 block rounded-full px-3 py-2.5 text-center text-sm font-semibold sm:hidden {{ $primary ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-700' }}">{{ $door['button'] }}</span>
            </a>
        @endforeach
    </div>
    <p class="mt-4 text-xs text-zinc-500">Free to create your account. Nothing to pay until you order.</p>
</div>
