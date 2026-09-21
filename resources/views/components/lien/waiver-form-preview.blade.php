{{--
    The real form, shown before anyone signs up: a sample-filled copy of each
    waiver the state uses (WaiverFormPreview), one visible at a time. Every
    blank the visitor's own details would fill is highlighted inside the
    sheet. SAMPLE across it and the cut-off bottom keep it from doubling as a
    free blank form.

    Each sheet is the generator's document in a sandboxed iframe (letter width,
    816px), scaled to the card. Picking a type here also picks it in the
    starter on the same page, and the other way round ("waiver-kind" event).
--}}
@props([
    'previews' => [],
    'stateName' => null,
    'statutory' => false,
    'kind' => null,
])

@php
    $labels = [
        'conditional_progress' => ['Conditional', 'Progress payment'],
        'unconditional_progress' => ['Unconditional', 'Progress payment'],
        'conditional_final' => ['Conditional', 'Final payment'],
        'unconditional_final' => ['Unconditional', 'Final payment'],
    ];
    $first = array_key_exists((string) $kind, $previews) ? (string) $kind : array_key_first($previews);
@endphp

@if ($previews !== [])
<div
    data-waiver-preview
    x-data="{ kind: @js($first), scale: null }"
    x-init="const fit = () => scale = $refs.paper.clientWidth / 816; fit(); new ResizeObserver(fit).observe($refs.paper)"
    @waiver-kind.window="if ($event.detail.from !== 'preview' && @js(array_keys($previews)).includes($event.detail.kind)) kind = $event.detail.kind"
    {{ $attributes }}
>
    <div class="flex items-center justify-between">
        <span class="font-serif text-xs uppercase tracking-[0.2em] text-zinc-500">{{ $stateName ?? 'Any state' }}</span>
        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700">
            {{ $statutory ? 'Exact statutory text' : 'Attorney-reviewed form' }}
        </span>
    </div>

    @if (count($previews) > 1)
        <div class="mt-3 grid grid-cols-2 gap-1.5 sm:grid-cols-4" role="tablist" aria-label="Waiver type">
            @foreach ($previews as $value => $preview)
                <button
                    type="button"
                    role="tab"
                    :aria-selected="(kind === '{{ $value }}').toString()"
                    @click="kind = '{{ $value }}'; $dispatch('waiver-kind', { kind: '{{ $value }}', from: 'preview' })"
                    class="rounded-lg border px-2.5 py-2 text-left transition"
                    :class="kind === '{{ $value }}' ? 'border-amber-500 bg-amber-50 ring-1 ring-amber-500' : 'border-zinc-200 bg-white hover:border-zinc-300'"
                >
                    <span class="block text-[13px] font-semibold leading-tight text-zinc-900">{{ $labels[$value][0] ?? $preview['title'] }}</span>
                    <span class="block text-[11px] text-zinc-500">{{ $labels[$value][1] ?? '' }}</span>
                </button>
            @endforeach
        </div>
    @endif

    <div class="relative mt-3">
        <div class="absolute inset-0 translate-x-2.5 translate-y-2.5 rounded-lg bg-zinc-200"></div>
        <div x-ref="paper" class="relative h-[24rem] overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-xl sm:h-[35rem]">
            @foreach ($previews as $value => $preview)
                {{-- Letter-size sheet scaled to the card. The CSS scale is a
                     close guess per breakpoint until Alpine measures it. --}}
                <iframe
                    x-show="kind === '{{ $value }}'"
                    @if ($value !== $first) style="display: none;" loading="lazy" @endif
                    :style="scale ? { scale: scale } : {}"
                    class="pointer-events-none absolute left-0 top-0 h-[1056px] w-[816px] origin-top-left scale-[0.41] border-0 sm:scale-[0.62]"
                    sandbox
                    tabindex="-1"
                    aria-hidden="true"
                    title="Sample {{ $preview['title'] }}"
                    srcdoc="{{ $preview['html'] }}"
                ></iframe>
            @endforeach

            <div class="pointer-events-none absolute inset-0 flex select-none items-center justify-center" aria-hidden="true">
                <span class="-rotate-[28deg] text-6xl font-extrabold tracking-[0.12em] text-red-600/15 sm:text-8xl">SAMPLE</span>
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-28 bg-gradient-to-b from-white/0 to-white"></div>
        </div>
    </div>

    <p class="mt-5 flex items-start gap-2 text-sm text-zinc-500">
        <span class="mt-0.5 h-3.5 w-3.5 shrink-0 rounded-sm border border-amber-500 bg-amber-200"></span>
        Sample details shown. Your project, names, amounts and dates go in the highlighted blanks.
    </p>

    {{-- Phones already carry the starter's fixed button, so this one starts at sm. --}}
    <a href="#start" class="mt-4 hidden items-center justify-center gap-2 rounded-lg bg-[#DC2626] px-6 py-3.5 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C] sm:flex">
        Create my free {{ $stateName ? $stateName.' ' : '' }}waiver
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
        </svg>
    </a>
</div>
@endif
