@extends('demo.pcc.layout', [
    'title' => 'Find Your Program — Pitt Community College (Concept Demo)',
    'metaDescription' => 'Search 100+ PCC degrees, diplomas, certificates and short-term workforce training, and filter by interest area. Concept demo for RFP 115-6181.',
])

@php
    use Illuminate\Support\Js;
    use Illuminate\Support\Str;

    $searchText = fn (array $p): string => Str::lower($p['name'].' '.$p['blurb'].' '.$p['division']);
    $initialCount = collect($programs)
        ->filter(fn (array $p): bool => ($initialArea === 'All' || $p['division'] === $initialArea)
            && ($initialQuery === '' || Str::contains($searchText($p), Str::lower(trim($initialQuery)))))
        ->count();
@endphp

@section('content')
    <div x-data="pccFinder({
        q: @js($initialQuery),
        area: @js($initialArea),
        entries: @js(collect($programs)->map(fn (array $p): array => ['division' => $p['division'], 'text' => $searchText($p)])->all()),
    })">
        {{-- Finder hero with prominent search (Lenoir pattern) --}}
        <div class="pcc-on-dark bg-[#0C2E52] px-4 py-[52px] md:px-8">
            <div class="mx-auto flex max-w-[1100px] flex-col gap-[18px]">
                <h1 class="pcc-display m-0 text-[clamp(30px,3.6vw,44px)] font-black text-white">Find your program.</h1>
                <p class="m-0 text-[17px] text-[#C9DAEC]">Search 100+ degrees, diplomas, certificates and short-term workforce training.</p>
                <div class="flex max-w-[640px] overflow-hidden rounded-md bg-white shadow-[0_10px_28px_rgba(8,31,58,.3)]">
                    <label for="pcc-finder-q" class="sr-only">Search programs</label>
                    <input id="pcc-finder-q" type="search" x-model="q" placeholder="Try 'nursing', 'welding', 'transfer'&hellip;"
                        class="min-w-0 flex-1 border-none px-[18px] py-4 text-base text-[#1A2733] outline-none placeholder:text-[#5E7288]">
                    <span class="flex items-center px-[18px] text-[#5E7288]" aria-hidden="true">
                        <svg width="17" height="17" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.8"/><path d="m10.5 10.5 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    </span>
                </div>
            </div>
        </div>

        <div class="mx-auto flex max-w-[1100px] flex-col gap-[22px] px-4 pb-[72px] pt-7 md:px-8">
            {{-- Interest-area filter chips --}}
            <div class="flex flex-wrap items-center gap-2.5" role="group" aria-label="Filter by area">
                <span class="mr-1 text-[13.5px] font-bold uppercase tracking-[.06em] text-[#4A5A6A]">Filter by area</span>
                @foreach (array_merge(['All'], $divisions) as $division)
                    {{-- State classes live only in the Alpine binding: static copies
                         would linger after a click and fight the bound ones. --}}
                    <button type="button" @click="area = {{ Js::from($division) }}"
                        :class="area === {{ Js::from($division) }} ? 'border-[#0C2E52] bg-[#0C2E52] text-white' : 'border-[#C9DAEC] bg-white text-[#0C2E52]'"
                        :aria-pressed="area === {{ Js::from($division) }}"
                        class="pcc-hoverable cursor-pointer rounded-full border-[1.5px] px-4 py-[7px] text-sm font-semibold hover:border-[#0C2E52]">
                        {{ $division === 'All' ? 'All areas' : $division }}
                    </button>
                @endforeach
            </div>

            <p class="m-0 text-[14.5px] text-[#4A5A6A]" aria-live="polite"><span x-text="count">{{ $initialCount }}</span> programs</p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($programs as $program)
                    @include('demo.pcc.partials.program-card', [
                        'program' => $program,
                        'eyebrow' => $program['division'],
                        'show' => sprintf('visible(%s, %s)', Js::from($program['division']), Js::from($searchText($program))),
                    ])
                @endforeach
            </div>

            {{-- Empty state --}}
            <div x-cloak x-show="count === 0" class="rounded-[10px] border-[1.5px] border-dashed border-[#C9DAEC] bg-[#F6F9FC] px-6 py-12 text-center">
                <p class="pcc-display m-0 text-xl font-extrabold text-[#0C2E52]">No programs match your search.</p>
                <p class="m-0 mt-2 text-[15px] text-[#4A5A6A]">Try a different keyword, or clear your filters to browse everything PCC offers.</p>
                <button type="button" @click="reset()" class="pcc-hoverable mt-4 cursor-pointer rounded bg-[#0C2E52] px-5 py-2.5 font-bold text-white hover:bg-[#123E6B]">Show all programs</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pccFinder', (initial) => ({
                q: initial.q,
                area: initial.area,

                // Mirror of the rendered cards, for the live result count.
                entries: initial.entries,

                visible(division, text) {
                    const q = this.q.trim().toLowerCase();

                    return (this.area === 'All' || division === this.area)
                        && (q === '' || text.includes(q));
                },

                get count() {
                    return this.entries.filter((e) => this.visible(e.division, e.text)).length;
                },

                reset() {
                    this.q = '';
                    this.area = 'All';
                },
            }));
        });
    </script>
@endpush
