<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head', ['title' => ($title ?? 'Everglades Elementary School')])
    <meta name="robots" content="noindex, nofollow" />
    <style>
        [x-cloak] { display: none !important; }
    </style>
    @include('demo.mdcps.partials.store')
</head>

    <body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased"
        x-data="{
            demoNoteOpen: false,
            demoNoteLabel: '',
            showDemoNote(label) {
                this.demoNoteLabel = label || '';
                this.demoNoteOpen = true;
            },
            mobileMenuOpen: false,
            lang: 'en',
            langs: { en: 'English', es: 'Español', ht: 'Kreyòl Ayisyen', pt: 'Português' },
            langNote: false,
            setLang(code) {
                this.lang = code;
                this.langNote = true;
                setTimeout(() => this.langNote = false, 2200);
            },
        }"
        @keydown.escape.window="demoNoteOpen = false">

    @yield('body')

    {{-- ============================================================ --}}
    {{-- Shared "not part of the preview" popup for non-wired links   --}}
    {{-- ============================================================ --}}
    <div x-cloak x-show="demoNoteOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog" aria-modal="true" aria-labelledby="demo-note-title">
        <div x-show="demoNoteOpen" x-transition.opacity @click="demoNoteOpen = false"
            class="absolute inset-0 bg-slate-900/60"></div>

        <div x-show="demoNoteOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="relative w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="border-b-4 border-[#0b5cab] bg-[#0b3d6b] px-6 py-4">
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-blue-200">Interactive preview</p>
                <h2 id="demo-note-title" class="mt-1 text-xl font-semibold text-white">
                    <span x-text="demoNoteLabel || 'This link'"></span>
                </h2>
            </div>
            <div class="px-6 py-6">
                <p class="text-sm leading-relaxed text-slate-600">
                    This page isn't wired up in the interactive preview. The live, clickable actions in this
                    sandbox are managed from the CMS:
                </p>
                <ul class="mt-3 space-y-1.5 text-sm text-slate-700">
                    <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-[#0b5cab]"></span> Update a school calendar event</li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-[#0b5cab]"></span> Post an emergency alert banner</li>
                    <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-[#0b5cab]"></span> Upload an image with accessible alt text</li>
                </ul>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('mdcps-demo.admin.dashboard') }}"
                        class="text-sm font-semibold text-[#0b5cab] hover:underline">Open the CMS</a>
                    <button type="button" @click="demoNoteOpen = false"
                        class="rounded-md bg-[#0b3d6b] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#0b5cab]">
                        Back to site
                    </button>
                </div>
            </div>
        </div>
    </div>

    @fluxScripts
</body>

</html>
