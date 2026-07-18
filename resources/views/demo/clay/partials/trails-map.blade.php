{{-- Illustrative concept map — production would use interactive mapping (Leaflet/Google). Included inside the ccTrails Alpine scope. --}}
<div class="relative h-[520px] overflow-hidden bg-[#E4EAE0] xl:h-[780px]">
    <div class="absolute inset-0 origin-center transition-transform" :style="'transform: scale(' + zoom + ')'">
        <svg viewBox="0 0 520 780" preserveAspectRatio="xMidYMid slice" class="block h-full w-full" role="img" aria-label="Illustrative concept map of Smithville Lake trail systems — not to scale">
            <rect width="520" height="780" fill="#E4EAE0"/>
            <path d="M200 60 C 160 170, 250 240, 210 340 C 180 430, 280 480, 250 590" fill="none" stroke="#A7C4D4" stroke-width="52" stroke-linecap="round"/>
            <path d="M200 60 C 160 170, 250 240, 210 340 C 180 430, 280 480, 250 590" fill="none" stroke="#BFD7E2" stroke-width="34" stroke-linecap="round"/>
            <path d="M110 200 C 60 260, 90 340, 140 380 C 190 420, 150 480, 110 520" fill="none" stroke="#256E3C" stroke-width="4" stroke-dasharray="1 7" stroke-linecap="round"/>
            <path d="M300 120 C 360 160, 330 240, 380 280 C 430 320, 400 380, 360 400" fill="none" stroke="#0B5A8A" stroke-width="4" stroke-linecap="round"/>
            <path d="M320 480 C 380 500, 360 570, 420 600" fill="none" stroke="#93402A" stroke-width="4" stroke-dasharray="8 6" stroke-linecap="round"/>
            <path d="M150 640 C 200 660, 230 700, 210 740" fill="none" stroke="#256E3C" stroke-width="4" stroke-dasharray="1 7" stroke-linecap="round"/>
            <text x="150" y="90" font-family="Public Sans, sans-serif" font-size="13" font-weight="600" fill="#5B7285">Smithville Lake</text>
        </svg>

        {{-- Trail markers --}}
        <template x-for="trail in filtered" :key="'marker-' + trail.slug">
            <button type="button" @click="selected = selected === trail.slug ? null : trail.slug; view = 'list'"
                class="absolute flex -translate-x-1/2 -translate-y-full flex-col items-center gap-0.5"
                :style="'left:' + trail.map.x + '%; top:' + trail.map.y + '%'"
                :aria-label="'Show details for ' + trail.name"
                :aria-pressed="selected === trail.slug">
                <span class="whitespace-nowrap rounded-full px-3 py-1.5 text-xs font-bold shadow-[0_2px_8px_rgba(20,30,35,.25)]"
                    :class="selected === trail.slug ? 'border-2 border-white bg-[#0E5A73] text-white' : 'bg-white text-[#232A2E]'"
                    x-text="(selected === trail.slug ? '▶ ' : '') + trail.name.replace(' Trail System', '').replace(' Trail', '')"></span>
                <span class="h-2.5 w-2.5 rounded-full border-2 border-white"
                    :class="{
                        'bg-[#0E5A73]': selected === trail.slug,
                        'bg-[#256E3C]': selected !== trail.slug && trail.activity === 'walk-bike',
                        'bg-[#0B5A8A]': selected !== trail.slug && trail.activity === 'mtb',
                        'bg-[#93402A]': selected !== trail.slug && trail.activity === 'equestrian',
                    }" aria-hidden="true"></span>
            </button>
        </template>
    </div>

    {{-- Legend --}}
    <div class="absolute bottom-4 left-4 flex flex-col gap-1.5 rounded-lg border border-[#E0D9CB] bg-white/95 px-3.5 py-2.5 text-xs text-[#5A646C]">
        <span class="text-[11px] font-extrabold tracking-[.06em] text-[#232A2E] uppercase">Trail types</span>
        <span class="flex items-center gap-2"><span class="h-[3px] w-4 rounded bg-[#256E3C]" aria-hidden="true"></span>Paved walk &amp; bike</span>
        <span class="flex items-center gap-2"><span class="h-[3px] w-4 rounded bg-[#0B5A8A]" aria-hidden="true"></span>Mountain bike</span>
        <span class="flex items-center gap-2"><span class="h-[3px] w-4 rounded bg-[#93402A]" aria-hidden="true"></span>Equestrian</span>
        <span class="mt-0.5 max-w-[190px] text-[11px] text-[#8A9199]">Illustrative concept map — production uses interactive mapping</span>
        <a href="https://www.claycountymo.gov/DocumentCenter/View/776" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-[11.5px] font-bold">
            Official trail map (PDF)
            <svg width="10" height="10" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="sr-only">(opens external site)</span>
        </a>
    </div>

    {{-- Zoom controls --}}
    <div class="absolute right-4 top-4 flex flex-col gap-2">
        <button type="button" @click="zoom = Math.min(1.6, Math.round((zoom + 0.2) * 10) / 10)" aria-label="Zoom in"
            class="h-10 w-10 rounded-lg border border-[#E0D9CB] bg-white text-[19px] font-bold text-[#232A2E] shadow-[0_2px_6px_rgba(20,30,35,.12)]">+</button>
        <button type="button" @click="zoom = Math.max(1, Math.round((zoom - 0.2) * 10) / 10)" aria-label="Zoom out"
            class="h-10 w-10 rounded-lg border border-[#E0D9CB] bg-white text-[19px] font-bold text-[#232A2E] shadow-[0_2px_6px_rgba(20,30,35,.12)]">−</button>
    </div>
</div>
