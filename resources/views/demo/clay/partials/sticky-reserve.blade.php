{{-- Mobile-only sticky reservation bar (homepage + Smithville Lake) --}}
<div class="h-[76px] lg:hidden" aria-hidden="true"></div>
<div class="fixed inset-x-0 bottom-0 z-40 border-t border-[#E0D9CB] bg-white/95 backdrop-blur px-4 pb-[max(12px,env(safe-area-inset-bottom))] pt-3 lg:hidden">
    <div class="flex gap-2.5">
        <button type="button" @click="openWebtrac(@if (isset($reserveContext))@js($reserveContext[0]), @js($reserveContext[1])@endif)"
            class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-lg bg-[#E7C55C] px-4 text-[15px] font-extrabold text-[#2A2000]">
            Reserve
            <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
        <button type="button" @click="alertsOpen = true"
            class="flex min-h-12 flex-1 items-center justify-center rounded-lg border-2 border-[#0E5A73] px-4 text-[15px] font-bold text-[#0E5A73]">
            Conditions
        </button>
    </div>
</div>
