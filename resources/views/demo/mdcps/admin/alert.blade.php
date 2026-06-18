@extends('demo.mdcps.layout')

@section('body')
    <div class="mx-auto flex max-w-screen-xl flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row">
        @include('demo.mdcps.partials.admin-nav', ['active' => 'alert'])

        <div class="min-w-0 flex-1"
            x-data="{
                form: { text: '', active: false },
                saved: false,
                load() { this.form = JSON.parse(JSON.stringify($store.mdcps.alert)); },
                save() {
                    $store.mdcps.alert = JSON.parse(JSON.stringify(this.form));
                    $store.mdcps.persist();
                    this.saved = true;
                    setTimeout(() => this.saved = false, 2600);
                },
            }"
            x-init="load()"
            @mdcps-reset.window="load()">

            <h1 class="text-2xl font-bold text-slate-900">Emergency alert</h1>
            <p class="mt-1 text-sm text-slate-500">Post a banner to the top of the public homepage. Toggle it off to remove it.</p>

            <div x-cloak x-show="saved" x-transition class="mt-4 flex items-center gap-2 rounded-lg border border-action/30 bg-action/10 px-4 py-3 text-sm font-medium text-action">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                <span x-text="form.active ? 'Alert published to the homepage.' : 'Alert saved and hidden from the homepage.'"></span>
                <a href="{{ route('mdcps-demo.home') }}" target="_blank" class="underline">View homepage &rarr;</a>
            </div>

            <form @submit.prevent="save()" class="mt-6 grid max-w-2xl gap-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="grid gap-1.5">
                    <label for="alert-text" class="text-sm font-medium text-slate-700">Alert message</label>
                    <textarea id="alert-text" rows="3" x-model="form.text"
                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b5cab] focus:outline-none focus:ring-2 focus:ring-[#0b5cab]/20"></textarea>
                    <p class="text-xs text-slate-400">Example: Weather update: After-school activities are canceled today.</p>
                </div>

                <div class="flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-medium text-slate-900">Alert active</p>
                        <p class="text-xs text-slate-500" x-text="form.active ? 'Currently shown on the homepage' : 'Currently hidden'"></p>
                    </div>
                    <button type="button" role="switch" :aria-checked="form.active" @click="form.active = !form.active"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition"
                        :class="form.active ? 'bg-danger' : 'bg-slate-300'">
                        <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition" :class="form.active ? 'translate-x-5' : 'translate-x-1'"></span>
                    </button>
                </div>

                <div>
                    <button type="submit" class="rounded-lg bg-[#0b5cab] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#094a8a]">Save alert</button>
                </div>
            </form>

            {{-- Live preview --}}
            <div class="mt-8 max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Banner preview</p>
                <div class="mt-2" x-show="form.active && form.text.trim().length">
                    <div class="flex items-start gap-3 rounded-lg bg-danger px-4 py-3 text-white">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                        <div>
                            <p class="text-sm font-bold uppercase tracking-wide">Emergency Alert</p>
                            <p class="text-sm" x-text="form.text"></p>
                        </div>
                    </div>
                </div>
                <p x-show="!form.active || !form.text.trim().length" class="mt-2 rounded-lg border border-dashed border-slate-300 px-4 py-3 text-sm text-slate-400">
                    Banner is hidden. Enable the toggle and add a message to preview it.
                </p>
            </div>
        </div>
    </div>
@endsection
