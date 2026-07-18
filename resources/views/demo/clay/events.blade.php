@extends('demo.clay.layout', [
    'active' => 'events',
    'title' => 'Events Calendar — Programs at the Lake, Trails & Historic Sites | Clay County Parks (Concept Demo)',
    'metaDescription' => 'Nature programs, living history days, paddles, and family events across Clay County parks and historic sites — filter by category or browse the calendar. Demo events only.',
    'ogImage' => 'james-farm-house.webp',
])

@php
    $featured = collect($events)->firstWhere('featured', true);
@endphp

@section('content')
    <div x-data="ccEvents">

        {{-- Page header --}}
        <div class="mx-auto flex max-w-[1440px] flex-col items-start justify-between gap-4 px-4 pb-4 pt-7 md:flex-row md:items-end md:px-8 xl:px-12">
            <div>
                <nav aria-label="Breadcrumb" class="mb-2 text-[13px] text-[#5A646C]">
                    <a href="{{ route('clay-demo.home') }}">Home</a>
                    <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
                    <span class="font-semibold text-[#232A2E]" aria-current="page">Events</span>
                </nav>
                <div class="flex flex-wrap items-baseline gap-3">
                    <h1 class="m-0 text-[28px] font-extrabold tracking-[-.02em] text-[#0B3A4E] xl:text-4xl">Events</h1>
                    <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-1.5 py-0.5 text-[11px] font-bold tracking-[.06em] text-[#7A5200] uppercase">All events shown are demo data</span>
                </div>
            </div>
            <button type="button" @click="openSubmit()"
                class="cc-hoverable flex-none rounded-lg border-2 border-[#0E5A73] px-5 py-3 text-[14.5px] font-bold text-[#0E5A73] hover:bg-[#F2F7F9]">
                Submit a community event
            </button>
        </div>

        {{-- Filter bar --}}
        <div class="mx-auto flex max-w-[1440px] flex-wrap items-center gap-2.5 px-4 pb-5 md:px-8 xl:px-12">
            <div role="group" aria-label="Calendar or list view" class="flex overflow-hidden rounded-lg border-[1.5px] border-[#B9B0A0]">
                <button type="button" @click="view = 'calendar'" :aria-pressed="view === 'calendar'"
                    class="border-0 px-4 py-2.5 text-[13.5px] font-bold" :class="view === 'calendar' ? 'bg-[#0E5A73] text-white' : 'bg-white text-[#232A2E] hover:bg-[#F2ECDF]'">Calendar</button>
                <button type="button" @click="view = 'list'" :aria-pressed="view === 'list'"
                    class="border-0 px-4 py-2.5 text-[13.5px] font-bold" :class="view === 'list' ? 'bg-[#0E5A73] text-white' : 'bg-white text-[#232A2E] hover:bg-[#F2ECDF]'">List</button>
            </div>

            <div class="flex items-center gap-1.5 md:ml-2">
                <button type="button" @click="prevMonth()" :disabled="monthIndex === 0" aria-label="Previous month"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border-[1.5px] border-[#B9B0A0] bg-white disabled:opacity-40">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="#232A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <span class="min-w-[150px] text-center text-[17px] font-extrabold text-[#0B3A4E]" x-text="monthLabel" aria-live="polite"></span>
                <button type="button" @click="nextMonth()" :disabled="monthIndex === months.length - 1" aria-label="Next month"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border-[1.5px] border-[#B9B0A0] bg-white disabled:opacity-40">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 5 7 7-7 7" stroke="#232A2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>

            <span class="mx-1.5 hidden h-6 w-px bg-[#DCD4C4] md:block" aria-hidden="true"></span>

            <div class="cc-scrollbar-none -mx-4 flex w-[calc(100%+2rem)] gap-2 overflow-x-auto px-4 md:mx-0 md:w-auto md:flex-wrap md:overflow-visible md:px-0" role="group" aria-label="Event categories">
                <template x-for="(label, key) in categories" :key="key">
                    <button type="button" @click="category = key" :aria-pressed="category === key"
                        class="min-h-10 flex-none rounded-full border-[1.5px] px-4 py-2 text-[13.5px] font-bold"
                        :class="category === key ? 'border-[#0E5A73] bg-[#0E5A73] text-white' : 'border-[#B9B0A0] bg-white text-[#232A2E] hover:border-[#0E5A73]'"
                        x-text="label"></button>
                </template>
            </div>
        </div>

        <div class="mx-auto flex max-w-[1440px] flex-col items-start gap-7 px-4 pb-14 md:px-8 xl:flex-row xl:px-12">
            <div class="w-full min-w-0 flex-1">

                {{-- ============ List view ============ --}}
                <div x-show="view === 'list'" class="flex flex-col gap-3.5">
                    <template x-for="group in grouped" :key="group.label">
                        <div class="flex flex-col gap-3.5">
                            <h2 class="m-0 pt-1 text-[12.5px] font-extrabold tracking-[.08em] text-[#5A646C] uppercase" x-text="group.label"></h2>
                            <template x-for="event in group.events" :key="event.slug">
                                <button type="button" :id="'event-' + event.slug" @click="openEvent(event)"
                                    class="cc-hoverable flex scroll-mt-24 items-center gap-4 rounded-xl border border-[#E0D9CB] bg-white p-5 text-left hover:shadow-[0_8px_24px_rgba(20,30,35,.1)] md:gap-5"
                                    :class="'hover:border-[' + catColor(event.category) + ']'">
                                    <span class="w-[60px] flex-none rounded-lg py-2 text-center" :class="catTint(event.category)">
                                        <span class="block text-[10.5px] font-extrabold tracking-[.08em]" :style="'color:' + catColor(event.category)" x-text="event.monthShort"></span>
                                        <span class="block text-[23px] font-extrabold leading-tight text-[#0B3A4E]" x-text="event.day"></span>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="mb-1 flex flex-wrap gap-2">
                                            <span class="rounded px-2 py-0.5 text-[10.5px] font-bold tracking-[.06em] uppercase" :class="catTint(event.category)" :style="'color:' + catColor(event.category)" x-text="event.categoryLabel"></span>
                                            <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-2 py-0.5 text-[10.5px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Demo event</span>
                                        </span>
                                        <span class="block text-[17.5px] font-extrabold text-[#0B3A4E]" x-text="event.title"></span>
                                        <span class="mt-0.5 block text-[13.5px] text-[#5A646C]" x-text="event.time + ' · ' + event.location + (event.registration ? ' · registration required' : '')"></span>
                                    </span>
                                    <svg class="flex-none" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 5 7 7-7 7" stroke="#5A646C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                            </template>
                        </div>
                    </template>

                    <div x-show="! grouped.length" x-cloak class="flex flex-col items-center gap-3 rounded-2xl border border-[#E0D9CB] bg-white px-8 py-10 text-center">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2" stroke="#B9B0A0" stroke-width="1.8"/><path d="M3 10h18M8 3v4m8-4v4" stroke="#B9B0A0" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <p class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Nothing in this category this month</p>
                        <p class="m-0 max-w-[40ch] text-[13.5px] leading-relaxed text-[#5A646C]">Try another month or clear the category filter.</p>
                        <button type="button" @click="category = 'all'" class="text-[13.5px] font-bold text-[#0E5A73] underline underline-offset-2">Show all categories</button>
                    </div>
                </div>

                {{-- ============ Calendar view ============ --}}
                <div x-show="view === 'calendar'" x-cloak class="overflow-hidden rounded-2xl border border-[#E0D9CB] bg-white">
                    <div class="grid grid-cols-7 border-b border-[#EFE9DD] bg-[#FAF7F0]" aria-hidden="true">
                        <template x-for="d in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="d">
                            <span class="px-2 py-2.5 text-center text-[11px] font-extrabold tracking-[.06em] text-[#5A646C] uppercase" x-text="d"></span>
                        </template>
                    </div>
                    <div class="grid grid-cols-7">
                        <template x-for="(cell, i) in calendarCells" :key="i">
                            <div class="min-h-[68px] border-b border-r border-[#F2ECDF] p-1.5 md:min-h-[104px] md:p-2 [&:nth-child(7n)]:border-r-0"
                                :class="cell ? '' : 'bg-[#FAF7F0]/60'">
                                <template x-if="cell">
                                    <div>
                                        <span class="block text-[12.5px] font-bold text-[#5A646C]" x-text="cell.day"></span>
                                        <div class="mt-1 hidden flex-col gap-1 md:flex">
                                            <template x-for="event in cell.events.slice(0, 2)" :key="event.slug">
                                                <button type="button" @click="openEvent(event)"
                                                    class="truncate rounded px-1.5 py-1 text-left text-[11px] font-bold leading-tight" :class="catTint(event.category)"
                                                    :style="'color:' + catColor(event.category)" x-text="event.title"></button>
                                            </template>
                                            <span x-show="cell.events.length > 2" class="px-1.5 text-[10.5px] font-bold text-[#8A9199]" x-text="'+' + (cell.events.length - 2) + ' more'"></span>
                                        </div>
                                        <div class="mt-1 flex flex-wrap gap-1 md:hidden">
                                            <template x-for="event in cell.events.slice(0, 3)" :key="'dot' + event.slug">
                                                <button type="button" @click="openEvent(event)" class="h-2.5 w-2.5 rounded-full" :style="'background:' + catColor(event.category)"
                                                    :aria-label="event.title"></button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <p class="m-0 border-t border-[#EFE9DD] bg-[#FAF7F0] px-4 py-2.5 text-[12.5px] text-[#8A9199]">Tap an event to see details. Demo events only — the production calendar would be powered by The Events Calendar plugin.</p>
                </div>
            </div>

            {{-- ============ Sidebar ============ --}}
            <aside class="flex w-full flex-none flex-col gap-4 xl:sticky xl:top-5 xl:w-[400px]" aria-label="Featured event and community submissions">
                @if ($featured)
                    <div class="overflow-hidden rounded-2xl border border-[#E0D9CB] bg-white shadow-[0_6px_20px_rgba(20,30,35,.08)]">
                        <div class="relative h-[190px]">
                            <img src="{{ asset('img/demos/clay-county/james-farm-house.webp') }}"
                                alt="The James farm at dusk — venue for the lantern tours" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                            <span class="absolute left-3 top-3 rounded-md bg-[#E7C55C] px-2.5 py-1 text-[11px] font-extrabold tracking-[.06em] text-[#2A2000] uppercase">Featured</span>
                        </div>
                        <div class="p-5">
                            <p class="m-0 mb-2 flex gap-2">
                                <span class="rounded bg-[#F5E6DF] px-2 py-0.5 text-[10.5px] font-bold tracking-[.06em] text-[#93402A] uppercase">{{ $featured['categoryLabel'] }}</span>
                                <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-2 py-0.5 text-[10.5px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Demo event</span>
                            </p>
                            <h2 class="m-0 text-xl font-extrabold leading-snug text-[#0B3A4E]">{{ $featured['title'] }}</h2>
                            <p class="m-0 mt-1.5 text-sm leading-normal text-[#5A646C]">{{ $featured['dateLabel'] }}, {{ $featured['time'] }} · {{ $featured['location'] }}. Limited capacity.</p>
                            <button type="button" @click="openEvent(@js($featured))"
                                class="cc-hoverable mt-3.5 rounded-lg bg-[#0E5A73] px-5 py-3 text-[14.5px] font-bold text-white hover:bg-[#0C4A5F]">Event details</button>
                        </div>
                    </div>
                @endif

                <div class="rounded-2xl border border-[#E0D9CB] bg-[#F2ECDF] p-6">
                    <h2 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Hosting something at the parks?</h2>
                    <p class="m-0 mb-3.5 mt-2 text-[13.5px] leading-normal text-[#5A646C]">Community groups can submit events for the calendar. Submissions are reviewed by parks staff before publishing.</p>
                    <button type="button" @click="openSubmit()"
                        class="cc-hoverable rounded-lg border-2 border-[#0E5A73] px-5 py-[11px] text-sm font-bold text-[#0E5A73] hover:bg-[#FAF7F0]">Submit a community event</button>
                </div>
            </aside>
        </div>

        {{-- ============ Event detail modal ============ --}}
        <div x-cloak x-show="activeEvent" class="fixed inset-0 z-50 flex items-end justify-center md:items-center md:p-4" role="dialog" aria-modal="true" aria-labelledby="cc-event-title">
            <div x-show="activeEvent" @click="activeEvent = null" class="absolute inset-0 bg-[#232A2E]/60"></div>
            <div x-show="activeEvent" x-cc-trap="activeEvent"
                class="cc-anim-up relative flex max-h-[88vh] w-full flex-col overflow-hidden rounded-t-2xl bg-white md:max-w-md md:rounded-2xl md:border md:border-[#E0D9CB] md:shadow-[0_16px_40px_rgba(20,30,35,.22)]">
                <template x-if="activeEvent">
                    <div class="flex flex-col overflow-y-auto">
                        <div class="flex items-start justify-between gap-3 px-5 pt-5">
                            <div class="flex flex-wrap gap-2">
                                <span class="rounded px-2 py-0.5 text-[10.5px] font-bold tracking-[.06em] uppercase" :class="catTint(activeEvent.category)" :style="'color:' + catColor(activeEvent.category)" x-text="activeEvent.categoryLabel"></span>
                                <span class="rounded border border-[#E7C55C] bg-[#FCF1CF] px-2 py-0.5 text-[10.5px] font-bold tracking-[.06em] text-[#7A5200] uppercase">Demo event</span>
                            </div>
                            <button type="button" @click="activeEvent = null" aria-label="Close event details"
                                class="flex h-10 w-10 flex-none items-center justify-center rounded-lg bg-[#F2ECDF] text-[17px] text-[#5A646C]">✕</button>
                        </div>
                        <div class="flex flex-col gap-2.5 px-5 pb-6 pt-2.5">
                            <h2 id="cc-event-title" class="m-0 text-[21px] font-extrabold leading-snug text-[#0B3A4E]" x-text="activeEvent.title"></h2>
                            <p class="m-0 text-sm font-semibold text-[#3C3129]" x-text="activeEvent.dateLabel + ' · ' + activeEvent.time"></p>
                            <p class="m-0 text-sm text-[#5A646C]" x-text="activeEvent.location"></p>
                            <p class="m-0 text-[14.5px] leading-relaxed text-[#3C454C]" x-text="activeEvent.description"></p>
                            <div class="mt-2 flex gap-2.5">
                                <button type="button" x-show="activeEvent.registration" @click="registered = true"
                                    class="flex-1 rounded-lg bg-[#0E5A73] px-4 py-3 text-[13.5px] font-bold text-white hover:bg-[#0C4A5F]"
                                    x-text="registered ? 'Demo only — no registration sent' : 'Register'"></button>
                                <button type="button" @click="added = ! added"
                                    class="flex-1 rounded-lg border-[1.5px] border-[#0E5A73] px-4 py-3 text-[13.5px] font-bold text-[#0E5A73] hover:bg-[#F2F7F9]"
                                    x-text="added ? '✓ Added (demo)' : 'Add to calendar'"></button>
                            </div>
                            <p class="m-0 text-center text-[11.5px] text-[#8A9199]">Front-end demo — nothing is saved or sent.</p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ============ Community submission modal ============ --}}
        <div x-cloak x-show="submitOpen" class="fixed inset-0 z-50 flex items-end justify-center md:items-center md:p-4" role="dialog" aria-modal="true" aria-labelledby="cc-submit-title">
            <div x-show="submitOpen" @click="submitOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
            <div x-show="submitOpen" x-cc-trap="submitOpen"
                class="cc-anim-up relative flex max-h-[88vh] w-full flex-col overflow-hidden rounded-t-2xl bg-white md:max-w-md md:rounded-2xl md:border md:border-[#E0D9CB] md:shadow-[0_16px_40px_rgba(20,30,35,.22)]">
                <div class="flex items-center justify-between px-5 pt-5">
                    <h2 id="cc-submit-title" class="m-0 text-[19px] font-extrabold text-[#0B3A4E]">Submit a community event</h2>
                    <button type="button" @click="submitOpen = false" aria-label="Close submission form"
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#F2ECDF] text-[17px] text-[#5A646C]">✕</button>
                </div>

                <form x-show="! submitted" @submit.prevent="trySubmit()" novalidate class="flex flex-col gap-3.5 overflow-y-auto px-5 pb-6 pt-3">
                    <div class="flex flex-col gap-1.5">
                        <label for="cc-ev-name" class="text-[12.5px] font-bold text-[#3C454C]">Event name <span class="text-[#A63024]" aria-hidden="true">*</span><span class="sr-only">(required)</span></label>
                        <input id="cc-ev-name" type="text" x-model="form.name" required placeholder="e.g. Lakeside 5K Fun Run"
                            class="rounded-lg border-[1.5px] border-[#B9B0A0] px-3.5 py-3 text-sm placeholder:text-[#8A9199]">
                    </div>
                    <div class="flex gap-2.5">
                        <div class="flex flex-1 flex-col gap-1.5">
                            <label for="cc-ev-date" class="text-[12.5px] font-bold text-[#3C454C]">Date <span class="text-[#A63024]" aria-hidden="true">*</span><span class="sr-only">(required)</span></label>
                            <input id="cc-ev-date" type="date" x-model="form.date" required
                                class="rounded-lg border-[1.5px] border-[#B9B0A0] px-3.5 py-[11px] text-sm text-[#232A2E]">
                        </div>
                        <div class="flex flex-1 flex-col gap-1.5">
                            <label for="cc-ev-loc" class="text-[12.5px] font-bold text-[#3C454C]">Location <span class="text-[#A63024]" aria-hidden="true">*</span><span class="sr-only">(required)</span></label>
                            <select id="cc-ev-loc" x-model="form.location" required class="rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-3 py-3 text-sm text-[#232A2E]">
                                <option value="">Choose a park…</option>
                                <option>Smithville Lake</option>
                                <option>Tryst Falls Park</option>
                                <option>Rocky Hollow Park</option>
                                <option>A historic site</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="cc-ev-email" class="text-[12.5px] font-bold text-[#3C454C]">Contact email <span class="text-[#A63024]" aria-hidden="true">*</span><span class="sr-only">(required)</span></label>
                        <input id="cc-ev-email" type="email" x-model="form.email" required placeholder="you@example.com"
                            :aria-invalid="emailError ? 'true' : null" aria-describedby="cc-ev-email-error"
                            class="rounded-lg border-[1.5px] px-3.5 py-3 text-sm placeholder:text-[#8A9199]"
                            :class="emailError ? 'border-[#A63024] bg-[#FEF6F5]' : 'border-[#B9B0A0]'">
                        <p id="cc-ev-email-error" x-show="emailError" x-cloak class="m-0 flex items-center gap-1.5 text-[12.5px] font-bold text-[#7C2018]">
                            <svg width="12" height="12" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="7" fill="#A63024"/><path d="M8 4.5V9" stroke="#FFF" stroke-width="1.6" stroke-linecap="round"/><circle cx="8" cy="11.4" r=".9" fill="#FFF"/></svg>
                            Enter a valid email address
                        </p>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="cc-ev-desc" class="text-[12.5px] font-bold text-[#3C454C]">Tell us about it</label>
                        <textarea id="cc-ev-desc" x-model="form.description" rows="3" placeholder="What's happening, who's it for, is there a cost?"
                            class="rounded-lg border-[1.5px] border-[#B9B0A0] px-3.5 py-3 text-sm placeholder:text-[#8A9199]"></textarea>
                    </div>
                    <button type="submit" class="rounded-lg bg-[#0E5A73] px-4 py-3.5 text-[15px] font-extrabold text-white hover:bg-[#0C4A5F]">Submit for review</button>
                    <p class="m-0 text-[11.5px] leading-normal text-[#8A9199]">Demo form — nothing is transmitted or stored. Production submissions are reviewed by parks staff before publishing (approval workflow per RFP 2.6.2).</p>
                </form>

                <div x-show="submitted" x-cloak class="flex flex-col items-center gap-3 px-5 pb-7 pt-4 text-center">
                    <svg width="42" height="42" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#E5F0E4"/><path d="m7.5 12.5 3 3 6-7" stroke="#256E3C" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <p class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Thanks — this is where review would begin.</p>
                    <p class="m-0 max-w-[38ch] text-[13.5px] leading-relaxed text-[#5A646C]">This is a demo confirmation only. Nothing was sent or saved — on the production site, parks staff would review your event before it publishes.</p>
                    <button type="button" @click="submitOpen = false" class="rounded-lg bg-[#0E5A73] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#0C4A5F]">Done</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ccEvents', () => ({
                events: @js($events),
                months: ['2026-07', '2026-08', '2026-09'],
                monthIndex: 0,
                view: 'list',
                category: 'all',
                activeEvent: null,
                registered: false,
                added: false,
                submitOpen: false,
                submitted: false,
                emailError: false,
                form: { name: '', date: '', location: '', email: '', description: '' },

                categories: { all: 'All', outdoors: 'Outdoors', historic: 'Historic sites', family: 'Family', education: 'Education', camping: 'Camping', nature: 'Nature' },

                init() {
                    const hash = window.location.hash.replace('#event-', '');
                    const hit = this.events.find((e) => e.slug === hash);
                    if (hit) {
                        this.monthIndex = Math.max(0, this.months.indexOf(hit.monthKey));
                        this.openEvent(hit);
                    }
                },

                get monthLabel() {
                    const [y, m] = this.months[this.monthIndex].split('-');
                    return new Date(y, m - 1, 1).toLocaleString('en-US', { month: 'long', year: 'numeric' });
                },

                get filtered() {
                    return this.events.filter((e) =>
                        e.monthKey === this.months[this.monthIndex]
                        && (this.category === 'all' || e.category === this.category)
                    ).sort((a, b) => a.date.localeCompare(b.date));
                },

                get grouped() {
                    const groups = [];
                    for (const event of this.filtered) {
                        const label = new Date(event.date + 'T12:00:00').toLocaleString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
                        const last = groups[groups.length - 1];
                        if (last && last.label === label) {
                            last.events.push(event);
                        } else {
                            groups.push({ label, events: [event] });
                        }
                    }
                    return groups;
                },

                get calendarCells() {
                    const [y, m] = this.months[this.monthIndex].split('-').map(Number);
                    const firstDay = new Date(y, m - 1, 1).getDay();
                    const daysInMonth = new Date(y, m, 0).getDate();
                    const cells = Array(firstDay).fill(null);
                    for (let day = 1; day <= daysInMonth; day++) {
                        const dateStr = this.months[this.monthIndex] + '-' + String(day).padStart(2, '0');
                        cells.push({ day, events: this.filtered.filter((e) => e.date === dateStr) });
                    }
                    while (cells.length % 7 !== 0) cells.push(null);
                    return cells;
                },

                prevMonth() { if (this.monthIndex > 0) this.monthIndex--; },
                nextMonth() { if (this.monthIndex < this.months.length - 1) this.monthIndex++; },

                openEvent(event) {
                    this.registered = false;
                    this.added = false;
                    this.activeEvent = event;
                },

                openSubmit() {
                    this.submitted = false;
                    this.emailError = false;
                    this.submitOpen = true;
                },

                trySubmit() {
                    this.emailError = ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email);
                    if (this.emailError || ! this.form.name || ! this.form.date || ! this.form.location) {
                        this.emailError = this.emailError || ! this.form.email;
                        return;
                    }
                    this.submitted = true;
                },

                catColor(cat) {
                    return { outdoors: '#0E5A73', historic: '#93402A', family: '#35663C', education: '#8A6636', camping: '#0E5A73', nature: '#35663C' }[cat] || '#0E5A73';
                },

                catTint(cat) {
                    return { outdoors: 'bg-[#E3EEF2]', historic: 'bg-[#F5E6DF]', family: 'bg-[#E5F0E4]', education: 'bg-[#F5EDDF]', camping: 'bg-[#E3EEF2]', nature: 'bg-[#E5F0E4]' }[cat] || 'bg-[#E3EEF2]';
                },
            }));
        });
    </script>
@endpush
