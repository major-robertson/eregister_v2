@extends('demo.mdcps.layout')

@section('body')
    <div :style="'--school-accent:' + $store.mdcps.branding.accent">
        {{-- Site temporarily unavailable state --}}
        <div x-cloak x-show="!$store.mdcps.siteEnabled" class="flex min-h-[70vh] items-center justify-center px-4">
            <div class="max-w-md text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.34 3.94a2 2 0 013.32 0l7.2 12a2 2 0 01-1.66 3.06H4.8a2 2 0 01-1.66-3.06l7.2-12zM12 9v4m0 4h.01" />
                    </svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold text-slate-900">Site temporarily unavailable</h1>
                <p class="mt-3 text-slate-600">The Everglades Elementary School website is undergoing scheduled maintenance.</p>
            </div>
        </div>

        <div x-cloak x-show="$store.mdcps.siteEnabled">
            @include('demo.mdcps.partials.public-header', ['active' => 'calendar'])

            {{-- Emergency alert banner --}}
            <div x-cloak x-show="$store.mdcps.alert.active" x-transition role="alert" class="border-b border-red-700 bg-danger text-white">
                <div class="mx-auto flex max-w-screen-xl items-start gap-3 px-4 py-3 sm:px-6">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide">Emergency Alert</p>
                        <p class="text-sm" x-text="$store.mdcps.alert.text"></p>
                    </div>
                </div>
            </div>

            {{-- Page header --}}
            <section class="text-white" :style="`background: linear-gradient(to bottom right, #0b3d6b, ${$store.mdcps.branding.accent})`">
                <div class="mx-auto max-w-screen-xl px-4 py-12 sm:px-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-100">Everglades Elementary School</p>
                    <h1 class="mt-2 text-3xl font-extrabold sm:text-4xl">School Calendar</h1>
                    <p class="mt-3 max-w-xl text-blue-50">Upcoming events, performances, and important dates for our school community.</p>
                </div>
            </section>

            {{-- Events list --}}
            <main class="mx-auto max-w-screen-xl px-4 py-10 sm:px-6"
                x-data="{
                    prettyMonth(d) { return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short' }); },
                    prettyDay(d) { return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { day: 'numeric' }); },
                    prettyFull(d) { return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }); },
                }">
                <div class="space-y-4">
                    <template x-for="event in $store.mdcps.sortedEvents()" :key="event.id">
                        <article class="flex gap-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex h-16 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg text-white"
                                :style="`background: ${$store.mdcps.branding.accent}`">
                                <span class="text-[11px] font-semibold uppercase tracking-wide" x-text="prettyMonth(event.date)"></span>
                                <span class="text-2xl font-extrabold leading-none" x-text="prettyDay(event.date)"></span>
                            </div>
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold text-slate-900" x-text="event.title"></h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    <span x-text="prettyFull(event.date)"></span>
                                    <template x-if="event.time"><span> &middot; <span x-text="event.time"></span></span></template>
                                </p>
                                <p class="mt-0.5 text-sm font-medium" x-show="event.location" :style="`color: ${$store.mdcps.branding.accent}`" x-text="event.location"></p>
                                <p class="mt-2 text-sm text-slate-600" x-show="event.description" x-text="event.description"></p>
                            </div>
                        </article>
                    </template>
                    <p x-show="!$store.mdcps.events.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-10 text-center text-slate-400">
                        There are no events scheduled right now. Please check back soon.
                    </p>
                </div>

                <div class="mt-8">
                    <a href="{{ route('mdcps-demo.home') }}" class="text-sm font-semibold hover:underline" :style="`color: ${$store.mdcps.branding.accent}`">&larr; Back to homepage</a>
                </div>
            </main>

            {{-- Footer --}}
            <footer class="mt-4 bg-[#0b3d6b] text-blue-100">
                <div class="mx-auto max-w-screen-xl px-4 py-8 text-xs sm:px-6">
                    <p>&copy; {{ date('Y') }} Miami-Dade County Public Schools. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </div>
@endsection
