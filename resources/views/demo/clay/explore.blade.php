@extends('demo.clay.layout', [
    'active' => 'explore',
    'title' => 'Explore Clay County — Parks, Lake, Beaches & Historic Sites Directory (Concept Demo)',
    'metaDescription' => 'One directory for every Clay County destination: Smithville Lake, county parks, beaches, marinas, the Nature Center, and five historic sites — searchable and filterable.',
    'ogImage' => 'tryst-falls.webp',
])

@section('content')
    <div x-data="ccExplore">

        {{-- Page header --}}
        <div class="mx-auto flex max-w-[1440px] flex-col items-start justify-between gap-5 px-4 pb-4 pt-7 md:px-8 lg:flex-row lg:items-end xl:px-12">
            <div>
                <nav aria-label="Breadcrumb" class="mb-2 text-[13px] text-[#5A646C]">
                    <a href="{{ route('clay-demo.home') }}">Home</a>
                    <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
                    <span class="font-semibold text-[#232A2E]" aria-current="page">Explore</span>
                </nav>
                <h1 class="m-0 text-[28px] font-extrabold tracking-[-.02em] text-[#0B3A4E] xl:text-4xl">Explore Clay County</h1>
                <p class="m-0 mt-1.5 max-w-[64ch] text-[15px] text-[#5A646C]">Every park, beach, marina, and historic site in one directory — content that lives on three websites today, brought together.</p>
            </div>
            <div class="flex w-full overflow-hidden rounded-lg border-[1.5px] border-[#B9B0A0] bg-white lg:w-[420px] lg:flex-none">
                <label for="cc-explore-search" class="sr-only">Search destinations</label>
                <input id="cc-explore-search" type="search" x-model="q" placeholder="Search parks, beaches, historic sites…"
                    class="min-w-0 flex-1 border-0 bg-transparent px-4 py-3 text-[15px] outline-none placeholder:text-[#8A9199]">
                <span class="flex items-center bg-[#0E5A73] px-4 text-white" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.5"/><path d="m10.5 10.5 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                </span>
            </div>
        </div>

        {{-- Desktop filter bar --}}
        <div class="mx-auto hidden max-w-[1440px] flex-wrap items-center gap-2.5 px-4 pb-4 md:flex md:px-8 xl:px-12">
            <label class="flex items-center gap-2 rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-3 py-2">
                <span class="text-sm font-bold text-[#232A2E]">Type</span>
                <select x-model="type" class="border-0 bg-transparent text-sm font-semibold text-[#0E5A73] outline-none">
                    <option value="">All</option>
                    <option value="lake">Lake &amp; recreation</option>
                    <option value="park">Parks</option>
                    <option value="beach">Beaches</option>
                    <option value="nature">Nature center</option>
                    <option value="historic">Historic sites</option>
                </select>
            </label>
            <label class="flex items-center gap-2 rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-3 py-2">
                <span class="text-sm font-bold text-[#232A2E]">Activity</span>
                <select x-model="activity" class="border-0 bg-transparent text-sm font-semibold text-[#0E5A73] outline-none">
                    <option value="">Any</option>
                    <template x-for="a in activityOptions" :key="a">
                        <option :value="a" x-text="a"></option>
                    </template>
                </select>
            </label>
            <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-[1.5px] bg-white px-3.5 py-2 text-sm font-bold"
                :class="accessibleOnly ? 'border-[#0E5A73] bg-[#E3EEF2] text-[#0B3A4E]' : 'border-[#B9B0A0] text-[#232A2E]'">
                <input type="checkbox" x-model="accessibleOnly" class="h-4 w-4 accent-[#0E5A73]">
                Accessible features listed
            </label>

            <div class="ml-auto flex items-center gap-3.5">
                <p class="m-0 text-sm text-[#5A646C]" role="status" aria-live="polite"><strong class="text-[#232A2E]" x-text="filtered.length + ' places'"></strong> match your filters</p>
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
            <p class="m-0 text-sm text-[#5A646C]" role="status"><strong class="text-[#232A2E]" x-text="filtered.length + ' places'"></strong></p>
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
            <button type="button" @click="clearAll()" class="text-[13.5px] font-bold text-[#0E5A73] underline underline-offset-2">Clear all</button>
        </div>

        <div class="mx-auto flex max-w-[1440px] items-start border-t border-[#E0D9CB]">
            {{-- Results list --}}
            <div class="min-w-0 flex-1 px-4 pb-12 pt-6 md:px-8 xl:pl-12 xl:pr-6" x-show="view === 'list'">
                <ul class="m-0 flex list-none flex-col gap-4 p-0">
                    <template x-for="dest in filtered" :key="dest.slug">
                        <li :id="'dest-' + dest.slug" class="scroll-mt-24"
                            :class="highlight === dest.slug ? 'rounded-xl outline outline-2 outline-offset-2 outline-[#0E5A73]' : ''">
                            <template x-if="dest.url">
                                <a :href="dest.url"
                                    class="cc-hoverable flex flex-col overflow-hidden rounded-xl border border-[#E0D9CB] bg-white no-underline hover:shadow-[0_8px_24px_rgba(20,30,35,.12)] md:min-h-[172px] md:flex-row"
                                    :class="dest.historic ? 'hover:border-[#93402A]' : 'hover:border-[#B98A54]'">
                                    <span class="relative block h-40 flex-none md:h-auto md:w-[250px]">
                                        <img :src="'{{ asset('img/demos/clay-county') }}/' + dest.image" :alt="dest.imageAlt" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                                    </span>
                                    <span class="flex min-w-0 flex-1 flex-col gap-1.5 p-5">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="rounded px-2 py-0.5 text-[11px] font-bold tracking-[.06em] uppercase" :class="typeTagClass(dest.type)" x-text="dest.typeLabel"></span>
                                            <span class="flex items-center gap-1.5 text-xs font-bold" :class="status(dest).cls">
                                                <span class="h-2 w-2 rounded-full" :class="status(dest).dot" aria-hidden="true"></span>
                                                <span x-text="status(dest).label"></span>
                                            </span>
                                        </span>
                                        <span class="text-[19px] font-extrabold text-[#0B3A4E]" :class="dest.historic ? 'cc-serif' : ''" x-text="dest.name"></span>
                                        <span class="text-sm leading-normal text-[#5A646C]" x-text="dest.summary"></span>
                                        <span class="mt-auto flex flex-wrap gap-x-3.5 gap-y-1 pt-1.5 text-[12.5px] text-[#3C454C]">
                                            <template x-for="a in dest.amenities.slice(0, 4)" :key="a"><span x-text="a"></span></template>
                                            <template x-for="acc in dest.accessibility" :key="acc"><span class="font-bold text-[#0B5A8A]" x-text="acc"></span></template>
                                        </span>
                                    </span>
                                </a>
                            </template>
                            <template x-if="! dest.url">
                                <div class="flex flex-col overflow-hidden rounded-xl border border-[#E0D9CB] bg-white md:min-h-[172px] md:flex-row">
                                    <span class="relative block h-40 flex-none md:h-auto md:w-[250px]">
                                        <img x-show="dest.image" :src="'{{ asset('img/demos/clay-county') }}/' + (dest.image || '')" :alt="dest.imageAlt" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                                        <span x-show="! dest.image" class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 bg-[#F2ECDF] text-[#8A9199]">
                                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="m3 16 5-5 4 4 3-3 6 6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="9" cy="9.5" r="1.4" stroke="currentColor" stroke-width="1.4"/></svg>
                                            <span class="text-[11px] font-bold tracking-[.05em] uppercase">Photo pending</span>
                                        </span>
                                    </span>
                                    <div class="flex min-w-0 flex-1 flex-col gap-1.5 p-5">
                                        <p class="m-0 flex flex-wrap items-center gap-2">
                                            <span class="rounded px-2 py-0.5 text-[11px] font-bold tracking-[.06em] uppercase" :class="typeTagClass(dest.type)" x-text="dest.typeLabel"></span>
                                            <span class="flex items-center gap-1.5 text-xs font-bold" :class="status(dest).cls">
                                                <span class="h-2 w-2 rounded-full" :class="status(dest).dot" aria-hidden="true"></span>
                                                <span x-text="status(dest).label"></span>
                                            </span>
                                        </p>
                                        <p class="m-0 text-[19px] font-extrabold text-[#0B3A4E]" :class="dest.historic ? 'cc-serif' : ''" x-text="dest.name"></p>
                                        <p class="m-0 text-sm leading-normal text-[#5A646C]" x-text="dest.summary"></p>
                                        <p class="m-0 mt-auto flex flex-wrap gap-x-3.5 gap-y-1 pt-1.5 text-[12.5px] text-[#3C454C]">
                                            <template x-for="a in dest.amenities.slice(0, 4)" :key="a"><span x-text="a"></span></template>
                                            <template x-for="acc in dest.accessibility" :key="acc"><span class="font-bold text-[#0B5A8A]" x-text="acc"></span></template>
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </li>
                    </template>
                </ul>

                {{-- Empty state --}}
                <div x-show="! filtered.length" x-cloak class="flex flex-col items-center gap-3 rounded-2xl border border-[#E0D9CB] bg-white px-8 py-10 text-center">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="6.5" stroke="#B9B0A0" stroke-width="1.8"/><path d="m15 15 5.5 5.5" stroke="#B9B0A0" stroke-width="1.8" stroke-linecap="round"/><path d="M7.5 10h5" stroke="#B9B0A0" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <p class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">No places match</p>
                    <p class="m-0 max-w-[40ch] text-[13.5px] leading-relaxed text-[#5A646C]">Check the spelling, or try a broader word like "swimming" or "beach". Clearing a filter usually helps.</p>
                    <button type="button" @click="clearAll()" class="rounded-lg bg-[#0E5A73] px-5 py-2.5 text-[13.5px] font-bold text-white hover:bg-[#0C4A5F]">Clear all filters</button>
                </div>
            </div>

            {{-- Map panel (desktop side / full when map view) --}}
            <div class="sticky top-0 hidden self-start border-l border-[#E0D9CB] xl:block"
                :class="view === 'map' ? 'w-full border-l-0' : 'w-[520px] flex-none'">
                @include('demo.clay.partials.explore-map')
            </div>
            <div class="w-full xl:hidden" x-show="view === 'map'" x-cloak>
                @include('demo.clay.partials.explore-map')
            </div>
        </div>

        {{-- Floating map/list pill (mobile + tablet) --}}
        <button type="button" @click="view = view === 'map' ? 'list' : 'map'; window.scrollTo({ top: 0 })"
            class="fixed bottom-5 left-1/2 z-40 flex min-h-11 -translate-x-1/2 items-center gap-2 rounded-full bg-[#0B3A4E] px-5 py-3 text-sm font-bold text-white shadow-[0_8px_24px_rgba(11,58,78,.4)] xl:hidden">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 4-5 2v14l5-2 6 2 5-2V4l-5 2-6-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 4v14m6-12v14" stroke="currentColor" stroke-width="1.8"/></svg>
            <span x-text="view === 'map' ? 'List view' : 'Map view'"></span>
        </button>

        {{-- Mobile filter drawer --}}
        <div x-cloak x-show="drawerOpen" class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Destination filters">
            <div x-show="drawerOpen" @click="drawerOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
            <div x-show="drawerOpen" x-cc-trap="drawerOpen"
                class="cc-anim-up absolute inset-x-0 bottom-0 flex max-h-[85vh] flex-col rounded-t-2xl bg-white">
                <div class="flex items-center justify-between border-b border-[#EFE9DD] px-5 py-4">
                    <h2 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Filter destinations</h2>
                    <button type="button" @click="drawerOpen = false" aria-label="Close filters"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#F2ECDF] text-[17px] text-[#5A646C]">✕</button>
                </div>
                <div class="flex flex-col gap-5 overflow-y-auto px-5 py-4">
                    <fieldset class="m-0 border-0 p-0">
                        <legend class="mb-2 p-0 text-[12.5px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase">Type</legend>
                        <div class="flex flex-col">
                            <template x-for="(label, key) in { '': 'All destinations', lake: 'Lake & recreation', park: 'Parks', beach: 'Beaches', nature: 'Nature center', historic: 'Historic sites' }" :key="'t' + key">
                                <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                                    <input type="radio" name="cc-type" :value="key" x-model="type" class="h-4.5 w-4.5 accent-[#0E5A73]">
                                    <span x-text="label"></span>
                                </label>
                            </template>
                        </div>
                    </fieldset>
                    <fieldset class="m-0 border-0 p-0">
                        <legend class="mb-2 p-0 text-[12.5px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase">Activity</legend>
                        <div class="flex flex-col">
                            <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                                <input type="radio" name="cc-activity-e" value="" x-model="activity" class="h-4.5 w-4.5 accent-[#0E5A73]">
                                Any
                            </label>
                            <template x-for="a in activityOptions" :key="'a' + a">
                                <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                                    <input type="radio" name="cc-activity-e" :value="a" x-model="activity" class="h-4.5 w-4.5 accent-[#0E5A73]">
                                    <span x-text="a"></span>
                                </label>
                            </template>
                        </div>
                    </fieldset>
                    <fieldset class="m-0 border-0 p-0">
                        <legend class="mb-2 p-0 text-[12.5px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase">Accessibility</legend>
                        <label class="flex min-h-11 cursor-pointer items-center gap-3 text-[15px] font-semibold text-[#232A2E]">
                            <input type="checkbox" x-model="accessibleOnly" class="h-4.5 w-4.5 accent-[#0E5A73]">
                            Accessible features listed
                        </label>
                    </fieldset>
                </div>
                <div class="flex gap-2.5 border-t border-[#EFE9DD] px-5 py-3.5">
                    <button type="button" @click="clearAll()" class="min-h-12 flex-1 rounded-lg border-2 border-[#0E5A73] px-4 text-[15px] font-bold text-[#0E5A73]">Clear all</button>
                    <button type="button" @click="drawerOpen = false" class="min-h-12 flex-1 rounded-lg bg-[#0E5A73] px-4 text-[15px] font-bold text-white" x-text="'Show ' + filtered.length + ' places'"></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ccExplore', () => ({
                destinations: @js($destinations),
                q: '',
                type: '',
                activity: '',
                accessibleOnly: false,
                view: 'list',
                drawerOpen: false,
                highlight: null,
                zoom: 1,

                activityOptions: ['Camping', 'Boating', 'Fishing', 'Swimming', 'Trails', 'Picnicking', 'Disc golf', 'Golf', 'Museum', 'Special events'],

                init() {
                    const hash = window.location.hash.replace('#dest-', '');
                    if (hash && this.destinations.some((d) => d.slug === hash)) {
                        this.highlight = hash;
                        this.$nextTick(() => document.getElementById('dest-' + hash)?.scrollIntoView({ block: 'center' }));
                    }
                },

                get filtered() {
                    const q = this.q.trim().toLowerCase();
                    return this.destinations.filter((d) =>
                        (! this.type || d.type === this.type)
                        && (! this.activity || d.activities.some((a) => a.toLowerCase().includes(this.activity.toLowerCase())))
                        && (! this.accessibleOnly || d.accessibility.length)
                        && (! q || (d.name + ' ' + d.summary + ' ' + d.activities.join(' ') + ' ' + d.amenities.join(' ')).toLowerCase().includes(q))
                    );
                },

                get activeFilterCount() {
                    return [this.type, this.activity, this.accessibleOnly, this.q.trim()].filter(Boolean).length;
                },

                get chips() {
                    const chips = [];
                    const typeLabels = { lake: 'Lake & recreation', park: 'Parks', beach: 'Beaches', nature: 'Nature center', historic: 'Historic sites' };
                    if (this.type) chips.push({ label: typeLabels[this.type], clear: () => this.type = '' });
                    if (this.activity) chips.push({ label: this.activity, clear: () => this.activity = '' });
                    if (this.accessibleOnly) chips.push({ label: 'Accessible features', clear: () => this.accessibleOnly = false });
                    if (this.q.trim()) chips.push({ label: 'Search: “' + this.q.trim() + '”', clear: () => this.q = '' });
                    return chips;
                },

                clearAll() {
                    this.q = '';
                    this.type = '';
                    this.activity = '';
                    this.accessibleOnly = false;
                },

                typeTagClass(type) {
                    return {
                        lake: 'text-[#0E5A73] bg-[#E3EEF2]',
                        park: 'text-[#35663C] bg-[#E5F0E4]',
                        beach: 'text-[#8A5A20] bg-[#FAF5EC]',
                        nature: 'text-[#35663C] bg-[#E5F0E4]',
                        historic: 'text-[#93402A] bg-[#F5E6DF]',
                    }[type] || 'text-[#35663C] bg-[#E5F0E4]';
                },

                status(dest) {
                    if (['mt-gilead-church', 'mt-gilead-school', 'pharis-farm'].includes(dest.slug)) {
                        return { label: 'By appointment & events', cls: 'text-[#5A646C]', dot: 'bg-[#8A9199]' };
                    }
                    if (dest.type === 'beach') {
                        return { label: 'Open · 8:30 AM–sunset (sample)', cls: 'text-[#1C4A28]', dot: 'bg-[#256E3C]' };
                    }
                    return { label: 'Open today (sample)', cls: 'text-[#1C4A28]', dot: 'bg-[#256E3C]' };
                },

                markerClass(dest) {
                    if (dest.historic) return 'bg-[#93402A]';
                    if (dest.type === 'beach' || dest.type === 'park') return 'bg-[#B98A54]';
                    return 'bg-[#0E5A73]';
                },

                goTo(slug) {
                    this.view = 'list';
                    this.highlight = slug;
                    this.$nextTick(() => document.getElementById('dest-' + slug)?.scrollIntoView({ block: 'center', behavior: 'smooth' }));
                },
            }));
        });
    </script>
@endpush
