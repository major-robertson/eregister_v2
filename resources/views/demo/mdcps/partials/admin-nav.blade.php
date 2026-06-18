{{-- Shared CMS sidebar/header for the demo admin pages. --}}
@props(['active' => 'dashboard'])

@php
    $links = [
        'dashboard' => ['label' => 'Dashboard', 'route' => 'mdcps-demo.admin.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        'calendar' => ['label' => 'Calendar event', 'route' => 'mdcps-demo.admin.calendar', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        'alert' => ['label' => 'Emergency alert', 'route' => 'mdcps-demo.admin.alert', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        'media' => ['label' => 'Media & alt text', 'route' => 'mdcps-demo.admin.media', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ];
@endphp

<aside class="lg:w-64 lg:flex-shrink-0">
    <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="flex items-center gap-2 px-2 py-2">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#0b5cab] text-sm font-bold text-white">M</div>
            <div>
                <p class="text-sm font-semibold leading-tight text-slate-900">M-DCPS CMS</p>
                <p class="text-[11px] text-slate-500">Everglades Elementary</p>
            </div>
        </div>
        <nav class="mt-2 grid gap-1">
            @foreach ($links as $key => $link)
                <a href="{{ route($link['route']) }}"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $active === $key ? 'bg-[#0b5cab] text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}" />
                    </svg>
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="mt-3 border-t border-slate-100 pt-3">
            <a href="{{ route('mdcps-demo.home') }}" target="_blank"
                class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-[#0b5cab] hover:bg-slate-100">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                </svg>
                View public site
            </a>
            <button type="button"
                @click="if (confirm('Reset all demo data back to defaults?')) { $store.mdcps.reset(); $dispatch('mdcps-reset'); }"
                class="mt-1 flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-danger">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Reset demo data
            </button>
            <form method="POST" action="{{ route('mdcps-demo.admin.logout') }}" class="mt-1">
                @csrf
                <button type="submit"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-slate-500 hover:bg-slate-100 hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </div>
</aside>
