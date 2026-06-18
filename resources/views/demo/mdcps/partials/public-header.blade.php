{{-- Shared public-site header: fixed district master brand + school identity. --}}
{{-- Relies on the layout body x-data (mobileMenuOpen, lang, showDemoNote) and $store.mdcps. --}}
@props(['active' => 'home'])

{{-- District master-brand bar (fixed M-DCPS master brand) --}}
<div class="bg-[#0b3d6b] text-white">
    <div class="mx-auto flex max-w-screen-xl items-center justify-between gap-4 px-4 py-2 text-xs sm:px-6">
        <button type="button" @click="showDemoNote('Miami-Dade County Public Schools')" class="flex items-center gap-2 font-semibold tracking-wide">
            <span class="inline-flex h-5 w-5 items-center justify-center rounded bg-white text-[10px] font-bold text-[#0b3d6b]">M</span>
            Miami-Dade County Public Schools
        </button>
        <div class="flex items-center gap-4">
            <button type="button" @click="showDemoNote('Find a School')" class="hidden text-blue-100 hover:text-white sm:inline">Find a School</button>
            <button type="button" @click="showDemoNote('Parent Portal')" class="hidden text-blue-100 hover:text-white sm:inline">Parent Portal</button>
            <div class="relative">
                <label for="lang" class="sr-only">Select language</label>
                <select id="lang" x-model="lang" @change="setLang(lang)"
                    class="rounded border border-white/30 bg-white/10 py-1 pl-2 pr-7 text-xs text-white focus:outline-none focus:ring-2 focus:ring-white/50">
                    <template x-for="(label, code) in langs" :key="code">
                        <option :value="code" x-text="label" class="text-slate-900"></option>
                    </template>
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Language note (mock) --}}
<div x-cloak x-show="langNote" x-transition class="bg-blue-50 text-center text-xs text-[#0b3d6b]">
    <p class="px-4 py-1">Page translated to <span class="font-semibold" x-text="langs[lang]"></span> (demo placeholder).</p>
</div>

{{-- School header (school identity: logo/mascot + accent) --}}
@php
    $navItems = [
        ['label' => 'Home', 'key' => 'home', 'route' => 'mdcps-demo.home'],
        ['label' => 'About', 'key' => 'about', 'route' => null],
        ['label' => 'Academics', 'key' => 'academics', 'route' => null],
        ['label' => 'Calendar', 'key' => 'calendar', 'route' => 'mdcps-demo.calendar'],
        ['label' => 'News', 'key' => 'news', 'route' => null],
        ['label' => 'Contact', 'key' => 'contact', 'route' => null],
    ];
@endphp
<header class="sticky top-0 z-40 border-b border-slate-200 bg-white shadow-sm">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6">
        <div class="flex h-20 items-center justify-between gap-4">
            <a href="{{ route('mdcps-demo.home') }}" class="flex items-center gap-3">
                <template x-if="$store.mdcps.branding.logo">
                    <img :src="$store.mdcps.branding.logo" alt="School logo" class="h-12 w-12 rounded-full object-cover shadow-sm" />
                </template>
                <template x-if="!$store.mdcps.branding.logo">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full text-2xl shadow-sm"
                        :style="`background: linear-gradient(to bottom right, ${$store.mdcps.branding.accent}, #0b3d6b)`" aria-hidden="true">
                        <span x-text="$store.mdcps.branding.mascot"></span>
                    </div>
                </template>
                <div>
                    <p class="text-base font-bold leading-tight text-slate-900 sm:text-lg">Everglades Elementary School</p>
                    <p class="text-xs font-medium text-slate-500">Home of the <span x-text="$store.mdcps.branding.mascotName + 's'"></span></p>
                </div>
            </a>

            <nav class="hidden items-center gap-1 md:flex">
                @foreach ($navItems as $item)
                    @php $isActive = $active === $item['key']; @endphp
                    @if ($item['route'])
                        <a href="{{ route($item['route']) }}"
                            class="rounded-md px-3 py-2 text-sm font-medium transition {{ $isActive ? '' : 'text-slate-600 hover:text-slate-900' }}"
                            @if ($isActive) :style="`color: ${$store.mdcps.branding.accent}`" @endif>{{ $item['label'] }}</a>
                    @else
                        <button type="button" @click="showDemoNote('{{ $item['label'] }}')"
                            class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition hover:text-slate-900">{{ $item['label'] }}</button>
                    @endif
                @endforeach
            </nav>

            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="inline-flex h-10 w-10 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 md:hidden" aria-label="Toggle menu">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </div>
    {{-- Mobile nav --}}
    <div x-cloak x-show="mobileMenuOpen" x-transition class="border-t border-slate-100 md:hidden">
        <nav class="grid gap-1 px-4 py-3">
            @foreach ($navItems as $item)
                @if ($item['route'])
                    <a href="{{ route($item['route']) }}" class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">{{ $item['label'] }}</a>
                @else
                    <button type="button" @click="mobileMenuOpen = false; showDemoNote('{{ $item['label'] }}')"
                        class="rounded-md px-3 py-2 text-left text-sm font-medium text-slate-700 hover:bg-slate-100">{{ $item['label'] }}</button>
                @endif
            @endforeach
        </nav>
    </div>
</header>
