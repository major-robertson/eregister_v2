@extends('demo.mdcps.layout')

@section('body')
    <div class="mx-auto flex max-w-screen-xl flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row">
        @include('demo.mdcps.partials.admin-nav', ['active' => 'media'])

        <div class="min-w-0 flex-1"
            x-data="{
                images: [],
                selected: null,
                alt: '',
                error: '',
                saved: false,
                uploading: false,
                load() {
                    this.images = [...$store.mdcps.sampleImages];
                    this.selected = { src: $store.mdcps.media.src, label: $store.mdcps.media.label };
                    this.alt = $store.mdcps.media.alt;
                    this.error = '';
                    this.saved = false;
                },
                select(img) {
                    this.selected = { src: img.src, label: img.label };
                    this.alt = img.alt || '';
                    this.error = '';
                    this.saved = false;
                },
                isSelected(img) { return this.selected && this.selected.src === img.src; },
                handleUpload(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file) { return; }
                    if (!file.type.startsWith('image/')) {
                        this.error = 'Please choose an image file.';
                        event.target.value = '';
                        return;
                    }
                    this.uploading = true;
                    this.error = '';
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            // Downscale through a canvas so the data URL stays
                            // well within the localStorage quota.
                            const max = 1280;
                            let { width, height } = img;
                            if (width > max || height > max) {
                                const scale = Math.min(max / width, max / height);
                                width = Math.round(width * scale);
                                height = Math.round(height * scale);
                            }
                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;
                            canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                            const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                            const label = file.name.replace(/\.[^.]+$/, '').slice(0, 40) || 'Uploaded photo';
                            const entry = { id: 'upload-' + Date.now(), label: label, src: dataUrl, alt: '' };
                            this.images.push(entry);
                            this.select(entry);
                            this.uploading = false;
                        };
                        img.onerror = () => { this.error = 'That image could not be read.'; this.uploading = false; };
                        img.src = e.target.result;
                    };
                    reader.onerror = () => { this.error = 'That file could not be read.'; this.uploading = false; };
                    reader.readAsDataURL(file);
                    event.target.value = '';
                },
                publish() {
                    this.saved = false;
                    if (!this.selected) { this.error = 'Please choose or upload an image first.'; return; }
                    if (!this.alt.trim()) { this.error = 'Alt text is required for accessibility (WCAG 1.1.1). Please describe the image before publishing.'; return; }
                    this.error = '';
                    $store.mdcps.media = { src: this.selected.src, alt: this.alt.trim(), label: this.selected.label };
                    $store.mdcps.persist();
                    this.saved = true;
                },
            }"
            x-init="load()"
            @mdcps-reset.window="load()">

            <h1 class="text-2xl font-bold text-slate-900">Media &amp; alt text</h1>
            <p class="mt-1 text-sm text-slate-500">Choose the homepage hero image. Accessible alt text is required before publishing.</p>

            {{-- Validation error --}}
            <div x-cloak x-show="error" x-transition role="alert" aria-live="assertive"
                class="mt-4 flex items-start gap-2 rounded-lg border border-danger/30 bg-danger/10 px-4 py-3 text-sm font-medium text-danger">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                <span x-text="error"></span>
            </div>

            {{-- Success --}}
            <div x-cloak x-show="saved" x-transition class="mt-4 flex items-center gap-2 rounded-lg border border-action/30 bg-action/10 px-4 py-3 text-sm font-medium text-action">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                Image published with alt text. <a href="{{ route('mdcps-demo.home') }}" target="_blank" class="underline">View homepage &rarr;</a>
            </div>

            <div class="mt-6 grid max-w-3xl gap-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                {{-- Image grid --}}
                <div>
                    <p class="text-sm font-medium text-slate-700">Select an image</p>
                    <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <template x-for="img in images" :key="img.id || img.src">
                            <button type="button" @click="select(img)"
                                class="group relative overflow-hidden rounded-lg border-2 text-left transition"
                                :class="isSelected(img) ? 'border-[#0b5cab] ring-2 ring-[#0b5cab]/30' : 'border-transparent hover:border-slate-300'">
                                <img :src="img.src" :alt="''" class="aspect-[8/5] w-full object-cover" />
                                <span class="absolute inset-x-0 bottom-0 bg-black/45 px-2 py-1 text-xs font-medium text-white" x-text="img.label"></span>
                                <span x-show="isSelected(img)" class="absolute right-1.5 top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-[#0b5cab] text-white">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Real upload --}}
                <div class="rounded-lg border border-dashed border-slate-300 p-4">
                    <p class="text-sm font-medium text-slate-700">Or upload a new image</p>
                    <label class="mt-2 flex cursor-pointer flex-col items-center justify-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center transition hover:border-[#0b5cab]/50 hover:bg-slate-100">
                        <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                        <span class="text-sm font-medium text-slate-700">
                            <span x-show="!uploading">Click to choose an image from your computer</span>
                            <span x-show="uploading" x-cloak>Processing image&hellip;</span>
                        </span>
                        <span class="text-xs text-slate-400">JPG or PNG, stored only in your browser</span>
                        <input type="file" accept="image/*" class="hidden" @change="handleUpload($event)" :disabled="uploading" />
                    </label>
                </div>

                {{-- Alt text --}}
                <div class="grid gap-1.5">
                    <label for="alt" class="text-sm font-medium text-slate-700">
                        Alt text <span class="text-danger">*</span>
                        <span class="font-normal text-slate-400">(describes the image for screen readers)</span>
                    </label>
                    <input id="alt" type="text" x-model="alt" placeholder="e.g. Students collaborating at a science station"
                        class="rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                        :class="error && !alt.trim() ? 'border-danger ring-danger/20' : 'border-slate-300 focus:border-[#0b5cab] focus:ring-[#0b5cab]/20'" />
                </div>

                {{-- Selected preview --}}
                <div x-show="selected" class="flex items-center gap-4 rounded-lg bg-slate-50 p-3">
                    <img :src="selected ? selected.src : ''" :alt="alt" class="h-16 w-24 flex-shrink-0 rounded object-cover" />
                    <div class="min-w-0 text-sm">
                        <p class="font-medium text-slate-900" x-text="selected ? selected.label : ''"></p>
                        <p class="truncate text-slate-500" x-text="alt.trim() ? ('Alt: ' + alt) : 'No alt text yet'"></p>
                    </div>
                </div>

                <div>
                    <button type="button" @click="publish()"
                        class="rounded-lg bg-[#0b5cab] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#094a8a]">Publish image</button>
                </div>
            </div>
        </div>
    </div>
@endsection
