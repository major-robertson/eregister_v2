@extends('demo.clay.layout', [
    'active' => 'trails',
    'title' => 'Trails Explorer — 80 Miles of Walking, Biking & Equestrian Trails | Clay County Parks (Concept Demo)',
    'metaDescription' => 'Find your trail at Smithville Lake: filter 6 trail systems by activity, distance, surface, difficulty, and accessibility. Concept demo for Clay County RFP 78-26.',
    'ogImage' => 'trail-camp-branch.webp',
])

@section('content')
    <div x-data="ccTrails">

        {{-- Page header --}}
        <div class="mx-auto flex max-w-[1440px] flex-col items-start justify-between gap-5 px-4 pb-4 pt-6 md:px-8 lg:flex-row lg:items-end xl:px-12">
            <div>
                <nav aria-label="Breadcrumb" class="mb-2 text-[13px] text-[#5A646C]">
                    <a href="{{ route('clay-demo.home') }}">Home</a>
                    <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
                    <span class="font-semibold text-[#232A2E]" aria-current="page">Trails</span>
                </nav>
                <h1 class="m-0 mb-1.5 text-[28px] font-extrabold tracking-[-.02em] text-[#0B3A4E] xl:text-4xl">Trails Explorer</h1>
                <p class="m-0 text-[15px] text-[#5A646C]">80.5 miles of trail: 37 paved, 11.5 singletrack, 32 equestrian. All systems carry 911 emergency response markers.</p>
            </div>
            <div class="flex w-full overflow-hidden rounded-lg border-[1.5px] border-[#B9B0A0] bg-white lg:w-[380px] lg:flex-none">
                <label for="cc-trail-search" class="sr-only">Search trails</label>
                <input id="cc-trail-search" type="search" x-model="q" placeholder="Search trails…"
                    class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-[15px] outline-none placeholder:text-[#8A9199]">
                <span class="flex items-center bg-[#0E5A73] px-4 text-white" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.5"/><path d="m10.5 10.5 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </span>
            </div>
        </div>

        {{-- Desktop filter bar --}}
        <div class="mx-auto hidden max-w-[1440px] flex-wrap items-center gap-2.5 px-4 pb-4 md:flex md:px-8 xl:px-12">
            <div role="group" aria-label="Activity" class="flex overflow-hidden rounded-lg border-[1.5px] border-[#B9B0A0]">
                <template x-for="(label, key) in { all: 'All', 'walk-bike': 'Walk & bike', mtb: 'Mountain bike', equestrian: 'Equestrian' }" :key="key">
                    <button type="button" @click="activity = key" :aria-pressed="activity === key"
                        class="border-0 px-4 py-2.5 text-[13.5px] font-bold first:border-l-0 [&:not(:first-child)]:border-l [&:not(:first-child)]:border-[#E0D9CB]"
                        :class="activity === key ? 'bg-[#0E5A73] text-white' : 'bg-white text-[#232A2E] hover:bg-[#F2ECDF]'"
                        x-text="label"></button>
                </template>
            </div>

            <label class="flex items-center gap-2 rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-3 py-2">
                <span class="text-sm font-bold text-[#232A2E]">Difficulty</span>
                <select x-model="difficulty" class="border-0 bg-transparent text-sm font-semibold text-[#0E5A73] outline-none">
                    <option value="">Any</option>
                    <option value="easy">Easy</option>
                    <option value="moderate">Moderate</option>
                    <option value="challenging">Challenging</option>
                </select>
            </label>

            <label class="flex items-center gap-2 rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-3 py-2">
                <span class="text-sm font-bold text-[#232A2E]">Distance</span>
                <select x-model="distance" class="border-0 bg-transparent text-sm font-semibold text-[#0E5A73] outline-none">
                    <option value="">Any</option>
                    <option value="short">Under 5 mi</option>
                    <option value="medium">5–15 mi</option>
                    <option value="long">Over 15 mi</option>
                </select>
            </label>

            <label class="flex items-center gap-2 rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-3 py-2">
                <span class="text-sm font-bold text-[#232A2E]">Surface</span>
                <select x-model="surface" class="border-0 bg-transparent text-sm font-semibold text-[#0E5A73] outline-none">
                    <option value="">Any</option>
                    <option value="paved">Paved</option>
                    <option value="natural">Natural</option>
                </select>
            </label>

            <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-[1.5px] bg-white px-3.5 py-2 text-sm font-bold"
                :class="accessibleOnly ? 'border-[#0E5A73] bg-[#E3EEF2] text-[#0B3A4E]' : 'border-[#B9B0A0] text-[#232A2E]'">
                <input type="checkbox" x-model="accessibleOnly" class="h-4 w-4 accent-[#0E5A73]">
                Accessible surface only
            </label>

            <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-[1.5px] bg-white px-3.5 py-2 text-sm font-bold"
                :class="openOnly ? 'border-[#0E5A73] bg-[#E3EEF2] text-[#0B3A4E]' : 'border-[#B9B0A0] text-[#232A2E]'">
                <input type="checkbox" x-model="openOnly" class="h-4 w-4 accent-[#0E5A73]">
                Fully open only
            </label>

            <div class="ml-auto flex items-center gap-3.5">
                <p class="m-0 text-sm text-[#5A646C]" role="status" aria-live="polite"><strong class="text-[#232A2E]" x-text="countLabel"></strong></p>
                <div role="group" aria-label="List or map view" class="flex overflow-hidden rounded-lg border-[1.5px] border-[#B9B0A0]">
                    <button type="button" @click="view = 'list'" :aria-pressed="view === 'list'"
                        class="border-0 px-4 py-2 text-[13.5px] font-bold" :class="view === 'list' ? 'bg-[#0E5A73] text-white' : 'bg-white text-[#232A2E] hover:bg-[#F2ECDF]'">List</button>
                    <button type="button" @click="view = 'map'" :aria-pressed="view === 'map'"
                        class="border-0 px-4 py-2 text-[13.5px] font-bold" :class="view === 'map' ? 'bg-[#0E5A73] text-white' : 'bg-white text-[#232A2E] hover:bg-[#F2ECDF]'">Map</button>
                </div>
            </div>
        </div>

        {{-- Mobile filter row --}}
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-3 px-4 pb-4 md:hidden">
            <button type="button" @click="drawerOpen = true"
                class="flex min-h-11 items-center gap-2 rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-4 py-2.5 text-sm font-bold text-[#232A2E]">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6h16M7 12h10m-7 6h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Filters
                <span x-show="activeFilterCount" x-cloak class="flex h-5 w-5 items-center justify-center rounded-full bg-[#0E5A73] text-[11px] font-bold text-white" x-text="activeFilterCount"></span>
            </button>
            <p class="m-0 text-sm text-[#5A646C]" role="status"><strong class="text-[#232A2E]" x-text="countLabel"></strong></p>
        </div>

        {{-- Active filter chips --}}
        <div class="mx-auto flex max-w-[1440px] flex-wrap items-center gap-2 px-4 pb-3 md:px-8 xl:px-12" x-show="activeFilterCount" x-cloak>
            <template x-for="chip in chips" :key="chip.label">
                <span class="flex items-center gap-1.5 rounded-full border-[1.5px] border-[#0E5A73] bg-[#E3EEF2] py-1.5 pl-3 pr-1.5 text-[12.5px] font-bold text-[#0B3A4E]">
                    <span x-text="chip.label"></span>
                    <button type="button" @click="chip.clear()" :aria-label="'Remove filter: ' + chip.label"
                        class="flex h-5 w-5 items-center justify-center rounded-full bg-[#0E5A73] text-[10px] text-white">✕</button>
                </span>
            </template>
            <button type="button" @click="clearAll()" class="text-[13.5px] font-bold text-[#0E5A73] underline underline-offset-2">Clear all filters</button>
        </div>

        {{-- List + Map --}}
        <div class="mx-auto flex max-w-[1440px] items-start border-t border-[#E0D9CB]">
            {{-- Trail list --}}
            <div class="min-w-0 flex-1 px-4 pb-12 pt-6 md:px-8 xl:pl-12 xl:pr-6" :class="view === 'map' ? 'hidden xl:hidden' : ''" x-show="view === 'list'">
                <ul class="m-0 flex list-none flex-col gap-3.5 p-0">
                    <template x-for="trail in filtered" :key="trail.slug">
                        <li :id="'trail-' + trail.slug" class="scroll-mt-24">
                            <div class="cc-hoverable rounded-xl bg-white"
                                :class="selected === trail.slug ? 'border-2 border-[#0E5A73] shadow-[0_8px_24px_rgba(14,90,115,.15)]' : 'border border-[#E0D9CB] hover:border-[#0E5A73] hover:shadow-[0_6px_18px_rgba(20,30,35,.1)]'">
                                <button type="button" @click="selected = selected === trail.slug ? null : trail.slug"
                                    :aria-expanded="selected === trail.slug"
                                    class="flex w-full flex-col gap-2.5 p-5 text-left md:flex-row md:items-start md:justify-between md:gap-4">
                                    <span class="min-w-0">
                                        <span class="mb-1.5 flex flex-wrap items-center gap-2.5">
                                            <span class="text-[17px] font-extrabold text-[#0B3A4E]" x-text="trail.name"></span>
                                            <span x-show="selected === trail.slug" x-cloak class="rounded bg-[#0E5A73] px-2 py-0.5 text-[11px] font-bold tracking-[.05em] text-white uppercase">Selected</span>
                                        </span>
                                        <span class="flex flex-wrap gap-x-4 gap-y-1 text-[13.5px] text-[#3C454C]">
                                            <span><strong x-text="trail.activityLabel"></strong> · <span x-text="trail.surface.toLowerCase()"></span></span>
                                            <span x-text="trail.distance"></span>
                                            <span class="flex items-center gap-1.5">
                                                <span class="inline-block h-3 w-3 flex-none"
                                                    :class="{
                                                        'rounded-full bg-[#256E3C]': trail.difficulty === 'easy',
                                                        'bg-[#0B5A8A]': trail.difficulty === 'moderate',
                                                        'rotate-45 bg-[#232A2E]': trail.difficulty === 'challenging',
                                                    }" aria-hidden="true"></span>
                                                <span x-text="trail.difficultyLabel + ' (sample)'"></span>
                                            </span>
                                            <span x-show="trail.accessible" class="font-bold text-[#0B5A8A]">Accessible surface</span>
                                        </span>
                                    </span>
                                    <span class="flex flex-none items-center gap-1.5 text-[13px] font-bold"
                                        :class="trail.status === 'caution' ? 'text-[#5A4200]' : 'text-[#1C4A28]'">
                                        <template x-if="trail.status === 'caution'">
                                            <svg width="12" height="12" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5 15 14H1L8 1.5Z" fill="#7A5200"/></svg>
                                        </template>
                                        <template x-if="trail.status !== 'caution'">
                                            <span class="h-[9px] w-[9px] rounded-full bg-[#256E3C]" aria-hidden="true"></span>
                                        </template>
                                        <span x-text="trail.status === 'caution' ? trail.statusNote : 'Open · ' + trail.statusNote.toLowerCase()"></span>
                                        <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 text-[10px] font-bold tracking-[.05em] text-[#7A5200] uppercase">Sample</span>
                                    </span>
                                </button>

                                {{-- Expanded state --}}
                                <div x-show="selected === trail.slug" x-cloak class="border-t border-[#EFE9DD] px-5 pb-5 pt-3.5">
                                    <p class="m-0 max-w-[80ch] text-[14.5px] leading-relaxed text-[#3C454C]" x-text="trail.description"></p>
                                    <ul x-show="trail.segments.length" class="m-0 mt-3 flex list-none flex-wrap gap-2 p-0">
                                        <template x-for="seg in trail.segments" :key="seg.name">
                                            <li class="rounded bg-[#F2ECDF] px-2.5 py-1 text-[12.5px] font-bold text-[#3C454C]"><span x-text="seg.name"></span> · <span x-text="seg.distance"></span></li>
                                        </template>
                                    </ul>
                                    <div class="mt-3.5 flex flex-wrap items-center gap-x-5 gap-y-2">
                                        <a href="https://www.claycountymo.gov/DocumentCenter/View/776" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-bold">
                                            Trail map (PDF)
                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                            <span class="sr-only">(opens external site)</span>
                                        </a>
                                        <a :href="'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(trail.trailhead + ' Smithville Lake MO')" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-bold">
                                            Directions to trailhead
                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                            <span class="sr-only">(opens external map)</span>
                                        </a>
                                        <a href="https://www.alltrails.com/parks/us/missouri/smithville-lake" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 text-sm font-bold">
                                            View on AllTrails
                                            <svg width="11" height="11" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                            <span class="sr-only">(opens external site)</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>

                {{-- Empty state --}}
                <div x-show="! filtered.length" x-cloak class="flex flex-col items-center gap-3 rounded-2xl border border-[#E0D9CB] bg-white px-8 py-10 text-center">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 20c3-7 6-9 8-9s3 3 5 3 3-2 3-2" stroke="#B9B0A0" stroke-width="1.8" stroke-linecap="round"/><path d="M4 20h16" stroke="#B9B0A0" stroke-width="1.8" stroke-linecap="round" stroke-dasharray="2 3"/></svg>
                    <p class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">No trails match those filters</p>
                    <p class="m-0 max-w-[42ch] text-[13.5px] leading-relaxed text-[#5A646C]">Try removing a filter — all 6 systems are a short drive apart.</p>
                    <button type="button" @click="clearAll()" class="text-[13.5px] font-bold text-[#0E5A73] underline underline-offset-2">Clear all filters</button>
                </div>

                <p class="m-0 mt-4 flex items-start gap-2.5 rounded-[10px] border border-[#E0D9CB] bg-[#F2ECDF] px-4.5 py-3.5 text-[13.5px] leading-normal text-[#3C454C]">
                    <svg class="mt-0.5 flex-none" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="#5A646C" stroke-width="1.8"/><path d="M12 11v5" stroke="#5A646C" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="8" r="1" fill="#5A646C"/></svg>
                    <span>Every trail carries MARC 911 emergency response markers — GPS-coded address signs that help first responders find you. Note the nearest marker if you need help.</span>
                </p>
            </div>

            {{-- Map panel --}}
            <div class="sticky top-0 hidden self-start border-l border-[#E0D9CB] xl:block"
                :class="view === 'map' ? 'w-full border-l-0' : 'w-[520px] flex-none'">
                @include('demo.clay.partials.trails-map')
            </div>

            {{-- Mobile/tablet map view --}}
            <div class="w-full xl:hidden" x-show="view === 'map'" x-cloak>
                @include('demo.clay.partials.trails-map')
            </div>
        </div>

        {{-- Floating map/list pill (mobile + tablet) --}}
        <button type="button" @click="view = view === 'map' ? 'list' : 'map'; window.scrollTo({ top: 0 })"
            class="fixed bottom-5 left-1/2 z-40 flex min-h-11 -translate-x-1/2 items-center gap-2 rounded-full bg-[#0B3A4E] px-5 py-3 text-sm font-bold text-white shadow-[0_8px_24px_rgba(11,58,78,.4)] xl:hidden">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 4-5 2v14l5-2 6 2 5-2V4l-5 2-6-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 4v14m6-12v14" stroke="currentColor" stroke-width="1.8"/></svg>
            <span x-text="view === 'map' ? 'List view' : 'Map view'"></span>
        </button>

        {{-- Mobile filter drawer --}}
        <div x-cloak x-show="drawerOpen" class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Trail filters">
            <div x-show="drawerOpen" @click="drawerOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
            <div x-show="drawerOpen" x-cc-trap="drawerOpen"
                class="cc-anim-up absolute inset-x-0 bottom-0 flex max-h-[85vh] flex-col rounded-t-2xl bg-white">
                <div class="flex items-center justify-between border-b border-[#EFE9DD] px-5 py-4">
                    <h2 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Filter trails</h2>
                    <button type="button" @click="drawerOpen = false" aria-label="Close filters"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#F2ECDF] text-[17px] text-[#5A646C]">✕</button>
                </div>
                <div class="flex flex-col gap-5 overflow-y-auto px-5 py-4">
                    <fieldset class="m-0 border-0 p-0">
                        <legend class="mb-2 p-0 text-[12.5px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase">Activity</legend>
                        <div class="flex flex-col">
                            <template x-for="(label, key) in { all: 'All trails', 'walk-bike': 'Walk & bike', mtb: 'Mountain bike', equestrian: 'Equestrian' }" :key="key">
                                <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                                    <input type="radio" name="cc-activity" :value="key" x-model="activity" class="h-4.5 w-4.5 accent-[#0E5A73]">
                                    <span x-text="label"></span>
                                </label>
                            </template>
                        </div>
                    </fieldset>
                    <fieldset class="m-0 border-0 p-0">
                        <legend class="mb-2 p-0 text-[12.5px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase">Difficulty <span class="normal-case">(sample data)</span></legend>
                        <div class="flex flex-col">
                            <template x-for="(label, key) in { '': 'Any', easy: 'Easy', moderate: 'Moderate', challenging: 'Challenging' }" :key="'d' + key">
                                <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                                    <input type="radio" name="cc-difficulty" :value="key" x-model="difficulty" class="h-4.5 w-4.5 accent-[#0E5A73]">
                                    <span x-text="label"></span>
                                </label>
                            </template>
                        </div>
                    </fieldset>
                    <fieldset class="m-0 border-0 p-0">
                        <legend class="mb-2 p-0 text-[12.5px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase">Distance</legend>
                        <div class="flex flex-col">
                            <template x-for="(label, key) in { '': 'Any', short: 'Under 5 mi', medium: '5–15 mi', long: 'Over 15 mi' }" :key="'dist' + key">
                                <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                                    <input type="radio" name="cc-distance" :value="key" x-model="distance" class="h-4.5 w-4.5 accent-[#0E5A73]">
                                    <span x-text="label"></span>
                                </label>
                            </template>
                        </div>
                    </fieldset>
                    <fieldset class="m-0 border-0 p-0">
                        <legend class="mb-2 p-0 text-[12.5px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase">Surface &amp; access</legend>
                        <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                            <input type="checkbox" x-model="accessibleOnly" class="h-4.5 w-4.5 accent-[#0E5A73]">
                            Accessible surface only
                        </label>
                        <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                            <input type="checkbox" x-model="openOnly" class="h-4.5 w-4.5 accent-[#0E5A73]">
                            Fully open only
                        </label>
                        <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                            <input type="checkbox" :checked="surface === 'paved'" @change="surface = $event.target.checked ? 'paved' : ''" class="h-4.5 w-4.5 accent-[#0E5A73]">
                            Paved surface only
                        </label>
                    </fieldset>
                </div>
                <div class="flex gap-2.5 border-t border-[#EFE9DD] px-5 py-3.5">
                    <button type="button" @click="clearAll()" class="min-h-12 flex-1 rounded-lg border-2 border-[#0E5A73] px-4 text-[15px] font-bold text-[#0E5A73]">Clear all</button>
                    <button type="button" @click="drawerOpen = false" class="min-h-12 flex-1 rounded-lg bg-[#0E5A73] px-4 text-[15px] font-bold text-white" x-text="'Show ' + countLabel"></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ccTrails', () => ({
                trails: @js($trails),
                q: '',
                activity: 'all',
                difficulty: '',
                distance: '',
                surface: '',
                accessibleOnly: false,
                openOnly: false,
                view: 'list',
                selected: null,
                drawerOpen: false,
                zoom: 1,

                init() {
                    const hash = window.location.hash.replace('#trail-', '');
                    if (hash && this.trails.some((t) => t.slug === hash)) {
                        this.selected = hash;
                    }
                },

                get filtered() {
                    const q = this.q.trim().toLowerCase();
                    return this.trails.filter((t) =>
                        (this.activity === 'all' || t.activity === this.activity)
                        && (! this.difficulty || t.difficulty === this.difficulty)
                        && (! this.distance
                            || (this.distance === 'short' && t.distanceMiles < 5)
                            || (this.distance === 'medium' && t.distanceMiles >= 5 && t.distanceMiles <= 15)
                            || (this.distance === 'long' && t.distanceMiles > 15))
                        && (! this.surface
                            || (this.surface === 'paved' && t.surface.toLowerCase().includes('paved'))
                            || (this.surface === 'natural' && ! t.surface.toLowerCase().includes('paved')))
                        && (! this.accessibleOnly || t.accessible)
                        && (! this.openOnly || t.status === 'open')
                        && (! q || (t.name + ' ' + t.description + ' ' + t.activityLabel).toLowerCase().includes(q))
                    );
                },

                get countLabel() {
                    const n = this.filtered.length;
                    return n === 1 ? '1 trail system' : n + ' trail systems';
                },

                get activeFilterCount() {
                    return [this.activity !== 'all', this.difficulty, this.distance, this.surface, this.accessibleOnly, this.openOnly, this.q.trim()]
                        .filter(Boolean).length;
                },

                get chips() {
                    const chips = [];
                    const labels = { 'walk-bike': 'Walk & bike', mtb: 'Mountain bike', equestrian: 'Equestrian' };
                    const distances = { short: 'Under 5 mi', medium: '5–15 mi', long: 'Over 15 mi' };
                    if (this.activity !== 'all') chips.push({ label: labels[this.activity], clear: () => this.activity = 'all' });
                    if (this.difficulty) chips.push({ label: this.difficulty[0].toUpperCase() + this.difficulty.slice(1), clear: () => this.difficulty = '' });
                    if (this.distance) chips.push({ label: distances[this.distance], clear: () => this.distance = '' });
                    if (this.surface) chips.push({ label: this.surface === 'paved' ? 'Paved surface' : 'Natural surface', clear: () => this.surface = '' });
                    if (this.accessibleOnly) chips.push({ label: 'Accessible surface', clear: () => this.accessibleOnly = false });
                    if (this.openOnly) chips.push({ label: 'Fully open', clear: () => this.openOnly = false });
                    if (this.q.trim()) chips.push({ label: 'Search: “' + this.q.trim() + '”', clear: () => this.q = '' });
                    return chips;
                },

                clearAll() {
                    this.q = '';
                    this.activity = 'all';
                    this.difficulty = '';
                    this.distance = '';
                    this.surface = '';
                    this.accessibleOnly = false;
                    this.openOnly = false;
                },
            }));
        });
    </script>
@endpush
