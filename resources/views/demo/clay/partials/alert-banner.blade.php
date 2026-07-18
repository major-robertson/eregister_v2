@php
    $bannerAlert = collect($alerts ?? [])->firstWhere('severity', '!=', 'info');
@endphp

@if ($bannerAlert)
    <div role="status"
        class="{{ $bannerAlert['severity'] === 'closure' ? 'border-[#E2A79D] bg-[#FBEAE7]' : 'border-[#E7C55C] bg-[#FCF1CF]' }} border-b">
        <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-4 py-2.5 md:px-8 xl:px-12">
            <div class="{{ $bannerAlert['severity'] === 'closure' ? 'text-[#7C2018]' : 'text-[#5A4200]' }} flex min-w-0 items-center gap-2.5 text-sm">
                @if ($bannerAlert['severity'] === 'closure')
                    <svg class="flex-none" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="7" fill="#A63024"/><path d="M5 5l6 6M11 5l-6 6" stroke="#FBEAE7" stroke-width="1.8" stroke-linecap="round"/></svg>
                @else
                    <svg class="flex-none" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5 15 14H1L8 1.5Z" fill="#7A5200"/><path d="M8 6v3.5" stroke="#FCF1CF" stroke-width="1.6" stroke-linecap="round"/><circle cx="8" cy="12" r="1" fill="#FCF1CF"/></svg>
                @endif
                <span class="flex-none font-bold">{{ $bannerAlert['severityLabel'] }}</span>
                <span class="hidden truncate md:inline">{{ $bannerAlert['title'] }} — {{ $bannerAlert['scope'] }}</span>
                <span class="truncate md:hidden">{{ $bannerAlert['title'] }}</span>
                <span class="{{ $bannerAlert['severity'] === 'closure' ? 'border-[#E2A79D] text-[#7C2018]' : 'border-[#E7C55C] text-[#7A5200]' }} hidden flex-none rounded border px-1.5 py-px text-[11px] font-bold tracking-[.06em] uppercase sm:inline">Prototype data</span>
            </div>
            <button type="button" @click="alertsOpen = true"
                class="{{ $bannerAlert['severity'] === 'closure' ? 'text-[#7C2018]' : 'text-[#5A4200]' }} flex-none text-sm font-bold underline underline-offset-2">
                View all alerts
            </button>
        </div>
    </div>
@endif
