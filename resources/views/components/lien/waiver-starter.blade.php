{{--
    The marketing-page starter: state, send vs collect, and (optionally) the
    waiver type, submitted as a plain GET to the start route, which stores
    the choices as a WaiverIntent and routes the visitor to register or the
    wizard. Only the state is required — the wizard still asks whatever was
    skipped. Rendered on the generator landing page, the 50 state pages, and
    the ads landing pages.
--}}
@props([
    'state' => null,
    'direction' => null,
    'kind' => null,
    'from' => null,
    'heading' => 'Create your waiver',
])

@php
    $stateNames = \App\Domains\Lien\Waivers\WaiverStateRegistry::STATE_NAMES;
    $selectedState = strtoupper((string) ($state ?? old('state', '')));
    $selectedDirection = (string) ($direction ?? old('direction', ''));
    $selectedKind = (string) ($kind ?? old('kind', ''));
    $from = $from ?? request()->getPathInfo();

    $directions = [
        'provide' => ['Send a waiver to get paid', 'A customer wants a signed waiver before releasing my payment.'],
        'collect' => ['Collect a waiver from someone I pay', 'I need a signed waiver back from a sub or vendor before I cut the check.'],
    ];

    $kinds = [
        'conditional_progress' => ['Conditional', 'Progress payment'],
        'unconditional_progress' => ['Unconditional', 'Progress payment'],
        'conditional_final' => ['Conditional', 'Final payment'],
        'unconditional_final' => ['Unconditional', 'Final payment'],
    ];
@endphp

<form
    method="GET"
    action="{{ route('liens.lien-waivers.start') }}"
    id="start"
    x-data="{ direction: @js($selectedDirection), kind: @js($selectedKind) }"
    {{ $attributes->class(['scroll-mt-24 rounded-2xl bg-white p-6 text-left shadow-2xl ring-1 ring-black/5 sm:p-8']) }}
>
    <input type="hidden" name="from" value="{{ $from }}">

    <h2 class="text-xl font-bold text-zinc-900">{{ $heading }}</h2>
    <p class="mt-1 text-sm text-zinc-500">About two minutes. The PDF is free to download.</p>

    <div class="mt-6 grid gap-6">
        <label class="block">
            <span class="text-sm font-semibold text-zinc-900">Where is the project?</span>
            <select
                name="state"
                required
                class="mt-2 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2.5 text-base text-zinc-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
            >
                <option value="" @selected($selectedState === '')>Select the project's state</option>
                @foreach ($stateNames as $code => $name)
                    <option value="{{ $code }}" @selected($selectedState === $code)>{{ $name }}</option>
                @endforeach
            </select>
            @error('state')
                <span class="mt-1 block text-sm text-red-600">{{ $message }}</span>
            @enderror
        </label>

        <fieldset>
            <legend class="text-sm font-semibold text-zinc-900">What do you need to do?</legend>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                @foreach ($directions as $value => [$label, $hint])
                    <label
                        class="cursor-pointer rounded-xl border p-3 transition"
                        :class="direction === '{{ $value }}' ? 'border-amber-500 bg-amber-50 ring-1 ring-amber-500' : 'border-zinc-200 hover:border-zinc-300'"
                    >
                        <input type="radio" name="direction" value="{{ $value }}" x-model="direction" class="sr-only">
                        <span class="block text-sm font-semibold text-zinc-900">{{ $label }}</span>
                        <span class="mt-0.5 block text-xs leading-snug text-zinc-500">{{ $hint }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset>
            <legend class="text-sm font-semibold text-zinc-900">
                Which waiver? <span class="font-normal text-zinc-500">(optional)</span>
            </legend>
            <div class="mt-2 grid grid-cols-2 gap-2">
                @foreach ($kinds as $value => [$condition, $payment])
                    <label
                        class="cursor-pointer rounded-xl border px-3 py-2.5 transition"
                        :class="kind === '{{ $value }}' ? 'border-amber-500 bg-amber-50 ring-1 ring-amber-500' : 'border-zinc-200 hover:border-zinc-300'"
                    >
                        <input type="radio" name="kind" value="{{ $value }}" x-model="kind" class="sr-only">
                        <span class="block text-sm font-semibold text-zinc-900">{{ $condition }}</span>
                        <span class="block text-xs text-zinc-500">{{ $payment }}</span>
                    </label>
                @endforeach
            </div>
            <button type="button" x-show="kind !== ''" x-cloak @click="kind = ''" class="mt-2 text-xs text-zinc-500 underline hover:text-zinc-700">
                Not sure — help me choose
            </button>
        </fieldset>

        <div>
            <button
                type="submit"
                class="group inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#DC2626] px-6 py-3.5 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]"
            >
                Create my free waiver
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </button>
            <p class="mt-3 text-center text-xs text-zinc-500">Free PDF download. No credit card. No watermark.</p>
        </div>
    </div>
</form>
