@extends('demo.mdcps.layout')

@section('body')
    <div class="mx-auto flex max-w-screen-xl flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row">
        @include('demo.mdcps.partials.admin-nav', ['active' => 'calendar'])

        <div class="min-w-0 flex-1"
            x-data="{
                form: { id: null, title: '', date: '', time: '', location: '', description: '' },
                saved: '',
                blank() { return { id: null, title: '', date: '', time: '', location: '', description: '' }; },
                startAdd() { this.form = this.blank(); this.saved = ''; this.$nextTick(() => this.$refs.title.focus()); },
                startEdit(event) { this.form = JSON.parse(JSON.stringify(event)); this.saved = ''; this.$refs.title.focus(); },
                save() {
                    if (!this.form.title.trim() || !this.form.date) { return; }
                    if (this.form.id) {
                        $store.mdcps.updateEvent(JSON.parse(JSON.stringify(this.form)));
                        this.saved = 'Event updated.';
                    } else {
                        $store.mdcps.addEvent({
                            title: this.form.title, date: this.form.date, time: this.form.time,
                            location: this.form.location, description: this.form.description,
                        });
                        this.saved = 'Event added.';
                    }
                    this.form = this.blank();
                    setTimeout(() => this.saved = '', 3000);
                },
                remove(id) {
                    if (confirm('Delete this event?')) {
                        $store.mdcps.deleteEvent(id);
                        if (this.form.id === id) { this.form = this.blank(); }
                    }
                },
                prettyDate(d) {
                    if (!d) { return 'No date'; }
                    return new Date(d + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
                },
            }"
            @mdcps-reset.window="form = blank(); saved = ''">

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Calendar events</h1>
                    <p class="mt-1 text-sm text-slate-500">Add, edit, or remove events. They appear on the homepage and the public calendar instantly.</p>
                </div>
                <a href="{{ route('mdcps-demo.calendar') }}" target="_blank" class="text-sm font-semibold text-[#0b5cab] hover:underline">View public calendar &rarr;</a>
            </div>

            <div x-cloak x-show="saved" x-transition class="mt-4 flex items-center gap-2 rounded-lg border border-action/30 bg-action/10 px-4 py-3 text-sm font-medium text-action">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span x-text="saved"></span>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-5">
                {{-- Event list --}}
                <div class="lg:col-span-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-400">All events (<span x-text="$store.mdcps.events.length"></span>)</h2>
                        <button type="button" @click="startAdd()" class="rounded-lg bg-[#0b5cab] px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-[#094a8a]">+ New event</button>
                    </div>
                    <div class="mt-3 space-y-3">
                        <template x-for="event in $store.mdcps.sortedEvents()" :key="event.id">
                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                                :class="form.id === event.id ? 'ring-2 ring-[#0b5cab]/40' : ''">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-900" x-text="event.title"></p>
                                        <p class="mt-0.5 text-sm text-slate-500">
                                            <span x-text="prettyDate(event.date)"></span>
                                            <template x-if="event.time"><span> &middot; <span x-text="event.time"></span></span></template>
                                        </p>
                                        <p class="text-sm text-slate-500" x-show="event.location" x-text="event.location"></p>
                                    </div>
                                    <div class="flex flex-shrink-0 gap-1">
                                        <button type="button" @click="startEdit(event)" class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-[#0b5cab]" aria-label="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        </button>
                                        <button type="button" @click="remove(event.id)" class="rounded-md p-2 text-slate-500 transition hover:bg-slate-100 hover:text-danger" aria-label="Delete">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <p x-show="!$store.mdcps.events.length" class="rounded-xl border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-400">
                            No events yet. Add one to see it on the homepage.
                        </p>
                    </div>
                </div>

                {{-- Add / edit form --}}
                <div class="lg:col-span-2">
                    <form @submit.prevent="save()" class="grid gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p class="text-sm font-semibold text-slate-900" x-text="form.id ? 'Edit event' : 'Add a new event'"></p>

                        <div class="grid gap-1.5">
                            <label for="title" class="text-sm font-medium text-slate-700">Event title</label>
                            <input id="title" x-ref="title" type="text" x-model="form.title" required
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="date" class="text-sm font-medium text-slate-700">Date</label>
                            <input id="date" type="date" x-model="form.date" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="time" class="text-sm font-medium text-slate-700">Time</label>
                            <input id="time" type="text" x-model="form.time" placeholder="6:00 PM - 8:00 PM"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="location" class="text-sm font-medium text-slate-700">Location</label>
                            <input id="location" type="text" x-model="form.location"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20" />
                        </div>
                        <div class="grid gap-1.5">
                            <label for="description" class="text-sm font-medium text-slate-700">Description</label>
                            <textarea id="description" rows="3" x-model="form.description"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20"></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="submit" class="rounded-lg bg-[#0b5cab] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#094a8a]"
                                x-text="form.id ? 'Update event' : 'Add event'"></button>
                            <button type="button" x-show="form.id" @click="form = blank()" class="text-sm font-medium text-slate-500 hover:text-slate-700">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
