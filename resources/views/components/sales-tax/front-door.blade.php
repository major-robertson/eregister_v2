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
            <a href="{{ $door['href'] }}" data-door="{{ $key }}"
               class="block rounded-xl border p-4 transition {{ $primary ? 'border-amber-500 bg-amber-50 hover:bg-amber-100' : 'border-zinc-200 hover:border-zinc-400' }}">
                <span class="flex items-start justify-between gap-4">
                    <span>
                        <span class="block font-semibold text-zinc-900">{{ $door['title'] }}</span>
                        <span class="mt-1 block text-sm text-zinc-600">{{ $door['text'] }}</span>
                    </span>
                    <span class="mt-0.5 shrink-0 rounded-full {{ $primary ? 'bg-zinc-900 text-white' : 'bg-zinc-100 text-zinc-700' }} px-3 py-1.5 text-xs font-semibold">{{ $door['button'] }}</span>
                </span>
            </a>
        @endforeach
    </div>
    <p class="mt-4 text-xs text-zinc-500">Free to create your account. Nothing to pay until you order.</p>
</div>
