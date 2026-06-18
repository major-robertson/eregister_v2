@extends('demo.mdcps.layout')

@section('body')
    <div class="mx-auto flex max-w-screen-xl flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row">
        @include('demo.mdcps.partials.admin-nav', ['active' => 'dashboard'])

        <div class="min-w-0 flex-1"
            x-data="{
                toast: false,
                customColor: $store.mdcps.branding.accent,
                flash() { this.toast = true; setTimeout(() => this.toast = false, 1800); },
                applyColor(value, name) {
                    $store.mdcps.branding.accent = value;
                    $store.mdcps.branding.accentName = name;
                    $store.mdcps.persist();
                    this.flash();
                },
                handleLogoUpload(event) {
                    const file = event.target.files && event.target.files[0];
                    if (!file || !file.type.startsWith('image/')) { event.target.value = ''; return; }
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = new Image();
                        img.onload = () => {
                            const max = 256;
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
                            $store.mdcps.branding.logo = canvas.toDataURL('image/png');
                            $store.mdcps.persist();
                            this.flash();
                        };
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    event.target.value = '';
                },
                removeLogo() {
                    $store.mdcps.branding.logo = null;
                    $store.mdcps.persist();
                    this.flash();
                },
            }"
            @mdcps-reset.window="customColor = $store.mdcps.branding.accent; flash()">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">CMS Dashboard</h1>
                    <p class="mt-1 text-sm text-slate-500">Manage the Everglades Elementary website. Changes save instantly to this browser and appear on the public site.</p>
                </div>
                <span x-cloak x-show="toast" x-transition class="rounded-full bg-action/10 px-3 py-1 text-sm font-medium text-action">Saved</span>
            </div>

            {{-- Editor cards --}}
            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                @php
                    $cards = [
                        ['Calendar event', 'Create or edit the featured school event.', 'mdcps-demo.admin.calendar', '#0b5cab', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['Emergency alert', 'Post or clear a homepage alert banner.', 'mdcps-demo.admin.alert', '#de4437', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                        ['Media &amp; alt text', 'Choose the hero image and set accessible alt text.', 'mdcps-demo.admin.media', '#16a34a', 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ];
                @endphp
                @foreach ($cards as $card)
                    <a href="{{ route($card[2]) }}" class="group flex items-start gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-lg text-white" style="background: {{ $card[3] }}">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $card[4] }}" /></svg>
                        </span>
                        <span>
                            <span class="block font-semibold text-slate-900">{!! $card[0] !!}</span>
                            <span class="mt-1 block text-sm text-slate-500">{!! $card[1] !!}</span>
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- District controls --}}
            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">District controls</h2>
                <p class="mt-1 text-sm text-slate-500">Quick toggles that affect the public homepage immediately.</p>

                <div class="mt-5 space-y-5">
                    {{-- District announcement toggle --}}
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 pb-5">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">District announcement</p>
                            <p class="mt-1 text-sm text-slate-500" x-text="$store.mdcps.districtAnnouncement.text"></p>
                        </div>
                        <button type="button" role="switch" :aria-checked="$store.mdcps.districtAnnouncement.active"
                            @click="$store.mdcps.districtAnnouncement.active = !$store.mdcps.districtAnnouncement.active; $store.mdcps.persist(); flash()"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition"
                            :class="$store.mdcps.districtAnnouncement.active ? 'bg-action' : 'bg-slate-300'">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition" :class="$store.mdcps.districtAnnouncement.active ? 'translate-x-5' : 'translate-x-1'"></span>
                        </button>
                    </div>

                    {{-- Site enabled toggle --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900">School site enabled</p>
                            <p class="mt-1 text-sm text-slate-500">When off, visitors see a "Site temporarily unavailable" page.</p>
                        </div>
                        <button type="button" role="switch" :aria-checked="$store.mdcps.siteEnabled"
                            @click="$store.mdcps.siteEnabled = !$store.mdcps.siteEnabled; $store.mdcps.persist(); flash()"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 items-center rounded-full transition"
                            :class="$store.mdcps.siteEnabled ? 'bg-action' : 'bg-slate-300'">
                            <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition" :class="$store.mdcps.siteEnabled ? 'translate-x-5' : 'translate-x-1'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- School identity / brand flexibility --}}
            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900">School identity</h2>
                <p class="mt-1 text-sm text-slate-500">
                    The district <span class="font-medium text-slate-700">Master Brand</span> (navy header, M-DCPS wordmark) stays fixed across every school.
                    Below, each school can <span class="font-medium text-slate-700">upload its own logo, customize its colors, and choose a mascot</span> &mdash; add, change, or remove them anytime and the homepage updates instantly.
                </p>

                <div class="mt-5 grid gap-6 lg:grid-cols-2">
                    {{-- School logo --}}
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-sm font-medium text-slate-700">School logo</p>
                        <p class="mt-0.5 text-xs text-slate-400">Upload a custom logo (PNG with transparency works best). Replaces the mascot in the header.</p>
                        <div class="mt-3 flex items-center gap-4">
                            <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200"
                                :style="$store.mdcps.branding.logo ? '' : `background: linear-gradient(to bottom right, ${$store.mdcps.branding.accent}, #0b3d6b)`">
                                <template x-if="$store.mdcps.branding.logo">
                                    <img :src="$store.mdcps.branding.logo" alt="Current school logo" class="h-full w-full object-cover" />
                                </template>
                                <template x-if="!$store.mdcps.branding.logo">
                                    <span class="text-2xl" x-text="$store.mdcps.branding.mascot"></span>
                                </template>
                            </div>
                            <div class="flex flex-col gap-2">
                                <label class="cursor-pointer rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <span x-text="$store.mdcps.branding.logo ? 'Replace logo' : 'Upload logo'"></span>
                                    <input type="file" accept="image/*" class="hidden" @change="handleLogoUpload($event)" />
                                </label>
                                <button type="button" x-show="$store.mdcps.branding.logo" @click="removeLogo()"
                                    class="text-left text-sm font-medium text-slate-500 hover:text-danger">Delete logo</button>
                            </div>
                        </div>
                    </div>

                    {{-- Mascot (fallback identity) --}}
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-sm font-medium text-slate-700">School mascot</p>
                        <p class="mt-0.5 text-xs text-slate-400">Used when no logo is uploaded, and for the "Home of the ___" tagline.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="preset in $store.mdcps.mascotPresets" :key="preset.emoji">
                                <button type="button"
                                    @click="$store.mdcps.branding.mascot = preset.emoji; $store.mdcps.branding.mascotName = preset.name; $store.mdcps.persist(); flash()"
                                    class="flex h-11 w-11 items-center justify-center rounded-lg border text-xl transition"
                                    :class="$store.mdcps.branding.mascot === preset.emoji ? 'border-slate-900 bg-slate-50' : 'border-slate-200 hover:border-slate-300'"
                                    :title="preset.name" :aria-label="preset.name">
                                    <span x-text="preset.emoji"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Accent color: presets + custom --}}
                <div class="mt-6">
                    <p class="text-sm font-medium text-slate-700">School colors</p>
                    <p class="mt-0.5 text-xs text-slate-400">Pick a preset or set a custom color. The district navy stays reserved for the master brand.</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <template x-for="preset in $store.mdcps.accentPresets" :key="preset.value">
                            <button type="button" @click="applyColor(preset.value, preset.name); customColor = preset.value"
                                class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition"
                                :class="$store.mdcps.branding.accent.toLowerCase() === preset.value.toLowerCase() ? 'border-slate-900 text-slate-900' : 'border-slate-200 text-slate-600 hover:border-slate-300'">
                                <span class="h-4 w-4 rounded-full" :style="`background: ${preset.value}`"></span>
                                <span x-text="preset.name"></span>
                            </button>
                        </template>

                        {{-- Custom color --}}
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 transition hover:border-slate-300">
                            <input type="color" x-model="customColor" @input="applyColor(customColor, 'Custom')"
                                class="h-5 w-5 cursor-pointer rounded border-0 bg-transparent p-0" />
                            Custom
                        </label>
                        <button type="button" @click="applyColor('#0b5cab', 'Ocean Blue'); customColor = '#0b5cab'"
                            class="text-sm font-medium text-slate-500 hover:text-slate-700">Reset color</button>
                    </div>
                </div>

                {{-- Preview --}}
                <div class="mt-6 overflow-hidden rounded-lg border border-slate-200">
                    <div class="bg-[#0b3d6b] px-4 py-2 text-xs font-semibold text-blue-100">
                        Master Brand &mdash; Miami-Dade County Public Schools
                    </div>
                    <div class="flex items-center gap-3 px-4 py-3" :style="`background: color-mix(in srgb, ${$store.mdcps.branding.accent} 10%, white)`">
                        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full text-xl"
                            :style="$store.mdcps.branding.logo ? '' : `background: ${$store.mdcps.branding.accent}`">
                            <template x-if="$store.mdcps.branding.logo">
                                <img :src="$store.mdcps.branding.logo" alt="School logo preview" class="h-full w-full object-cover" />
                            </template>
                            <template x-if="!$store.mdcps.branding.logo">
                                <span x-text="$store.mdcps.branding.mascot"></span>
                            </template>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-900">Everglades Elementary School</p>
                            <p class="text-xs text-slate-500">
                                Home of the <span x-text="$store.mdcps.branding.mascotName + 's'"></span>
                                &middot; <span x-text="$store.mdcps.branding.accentName"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
