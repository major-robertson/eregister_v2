{{-- Illustrative concept map of Clay County destinations. Included inside the ccExplore Alpine scope. --}}
<div class="relative h-[520px] overflow-hidden bg-[#E4EAE0] xl:h-[820px]">
    <div class="absolute inset-0 origin-center transition-transform" :style="'transform: scale(' + zoom + ')'">
        <svg viewBox="0 0 520 820" preserveAspectRatio="xMidYMid slice" class="block h-full w-full" role="img" aria-label="Illustrative concept map of Clay County destinations — not to scale">
            <rect width="520" height="820" fill="#E4EAE0"/>
            <path d="M180 90 C 150 180, 230 230, 200 320 C 175 400, 260 450, 240 540" fill="none" stroke="#A7C4D4" stroke-width="46" stroke-linecap="round"/>
            <path d="M180 90 C 150 180, 230 230, 200 320 C 175 400, 260 450, 240 540" fill="none" stroke="#BFD7E2" stroke-width="30" stroke-linecap="round"/>
            <path d="M60 640 L 460 640" stroke="#D8D2C2" stroke-width="10"/>
            <path d="M330 60 L 330 780" stroke="#D8D2C2" stroke-width="8"/>
            <path d="M60 340 L 470 300" stroke="#E0DACB" stroke-width="6"/>
            <text x="115" y="155" font-family="Public Sans, sans-serif" font-size="13" font-weight="600" fill="#5B7285" transform="rotate(-72 115 155)">Smithville Lake</text>
            <text x="336" y="720" font-family="Public Sans, sans-serif" font-size="11" fill="#8A8471">US-169</text>
            <text x="70" y="628" font-family="Public Sans, sans-serif" font-size="11" fill="#8A8471">MO-92 · Kearney → Liberty</text>
        </svg>

        <template x-for="dest in filtered" :key="'marker-' + dest.slug">
            <button type="button" @click="goTo(dest.slug)"
                class="absolute flex -translate-x-1/2 -translate-y-full flex-col items-center gap-0.5"
                :style="'left:' + dest.map.x + '%; top:' + dest.map.y + '%'"
                :aria-label="'Show ' + dest.name + ' in the list'">
                <span class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold shadow-[0_2px_8px_rgba(20,30,35,.3)]"
                    :class="highlight === dest.slug ? 'border-2 border-white text-white ' + markerClass(dest) : 'bg-white text-[#232A2E]'"
                    x-text="dest.name.replace('Smithville Lake Nature Center', 'Nature Center').replace('Jesse James Bank Museum', 'Bank Museum · Liberty')"></span>
                <span class="h-2.5 w-2.5 rounded-full border-2 border-white" :class="markerClass(dest)" aria-hidden="true"></span>
            </button>
        </template>
    </div>

    <div class="absolute bottom-4 left-4 flex flex-col gap-1.5 rounded-lg border border-[#E0D9CB] bg-white/95 px-3.5 py-2.5 text-xs text-[#5A646C]">
        <span class="text-[11px] font-extrabold tracking-[.06em] text-[#232A2E] uppercase">Legend</span>
        <span class="flex items-center gap-2"><span class="h-[9px] w-[9px] rounded-full bg-[#0E5A73]" aria-hidden="true"></span>Lake &amp; recreation</span>
        <span class="flex items-center gap-2"><span class="h-[9px] w-[9px] rounded-full bg-[#B98A54]" aria-hidden="true"></span>Beaches &amp; parks</span>
        <span class="flex items-center gap-2"><span class="h-[9px] w-[9px] rounded-full bg-[#93402A]" aria-hidden="true"></span>Historic sites</span>
        <span class="mt-0.5 max-w-[190px] text-[11px] text-[#8A9199]">Illustrative concept map — production uses interactive mapping</span>
    </div>

    <div class="absolute right-4 top-4 flex flex-col gap-2">
        <button type="button" @click="zoom = Math.min(1.6, Math.round((zoom + 0.2) * 10) / 10)" aria-label="Zoom in"
            class="h-10 w-10 rounded-lg border border-[#E0D9CB] bg-white text-[19px] font-bold text-[#232A2E] shadow-[0_2px_6px_rgba(20,30,35,.12)]">+</button>
        <button type="button" @click="zoom = Math.max(1, Math.round((zoom - 0.2) * 10) / 10)" aria-label="Zoom out"
            class="h-10 w-10 rounded-lg border border-[#E0D9CB] bg-white text-[19px] font-bold text-[#232A2E] shadow-[0_2px_6px_rgba(20,30,35,.12)]">−</button>
    </div>
</div>
