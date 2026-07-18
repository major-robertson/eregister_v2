{{-- ============ SiteSearch overlay ============ --}}
<div x-cloak x-show="searchOpen" class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-[12vh]" role="dialog" aria-modal="true" aria-label="Search">
    <div x-show="searchOpen" @click="searchOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
    <div x-show="searchOpen" x-cc-trap="searchOpen"
        class="cc-anim-fade relative w-full max-w-xl overflow-hidden rounded-xl border border-[#E0D9CB] bg-white shadow-[0_12px_32px_rgba(20,30,35,.18)]"
        @keydown.arrow-down.prevent="searchMove(1)"
        @keydown.arrow-up.prevent="searchMove(-1)"
        @keydown.enter.prevent="searchGo()">
        <div class="flex items-center gap-2.5 border-b border-[#EFE9DD] px-4 py-4">
            <svg class="flex-none" width="17" height="17" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="4.5" stroke="#5A646C" stroke-width="1.5"/><path d="m10.5 10.5 4 4" stroke="#5A646C" stroke-width="1.5" stroke-linecap="round"/></svg>
            <label for="cc-search-input" class="sr-only">Search destinations, trails, events, and FAQs</label>
            <input id="cc-search-input" type="search" x-model="query" @input="activeIndex = 0"
                placeholder="Search the parks — try &quot;camp&quot; or &quot;waterfall&quot;"
                autocomplete="off" data-autofocus
                class="w-full border-0 bg-transparent text-base text-[#232A2E] outline-none placeholder:text-[#8A9199]">
            <button type="button" @click="searchOpen = false" class="flex-none rounded border border-[#DCD4C4] px-1.5 py-0.5 text-[11px] text-[#8A9199]">ESC</button>
        </div>

        <div class="max-h-[50vh] overflow-y-auto p-2" role="listbox" aria-label="Search results">
            <template x-if="query.trim().length >= 2 && results.length">
                <div>
                    <p class="m-0 px-3 py-1.5 text-[11px] font-extrabold tracking-[.07em] text-[#5A646C] uppercase">Suggestions</p>
                    <template x-for="(hit, i) in results" :key="hit.url + hit.title">
                        <a :href="hit.url" role="option" :aria-selected="i === activeIndex"
                            @mouseenter="activeIndex = i"
                            class="flex items-center justify-between gap-3 rounded-lg px-3 py-[11px] no-underline"
                            :class="i === activeIndex ? 'bg-[#F2ECDF]' : ''">
                            <span class="text-[14.5px] font-semibold text-[#232A2E]" x-text="hit.title"></span>
                            <span class="flex-none text-[11px] text-[#8A9199]" x-text="hit.type"></span>
                        </a>
                    </template>
                </div>
            </template>

            <template x-if="query.trim().length >= 2 && ! results.length">
                <div class="flex flex-col items-center gap-3 px-6 py-9 text-center">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="6.5" stroke="#B9B0A0" stroke-width="1.8"/><path d="m15 15 5.5 5.5" stroke="#B9B0A0" stroke-width="1.8" stroke-linecap="round"/><path d="M7.5 10h5" stroke="#B9B0A0" stroke-width="1.8" stroke-linecap="round"/></svg>
                    <p class="m-0 text-[15px] font-extrabold text-[#0B3A4E]">No results for &ldquo;<span x-text="query"></span>&rdquo;</p>
                    <p class="m-0 max-w-[40ch] text-[13.5px] leading-relaxed text-[#5A646C]">Check the spelling, or try a broader word like &ldquo;swimming&rdquo; or &ldquo;beach&rdquo;. You can also browse everything in Explore.</p>
                    <a href="{{ route('clay-demo.explore') }}" class="rounded-lg bg-[#0E5A73] px-4 py-2.5 text-[13.5px] font-bold text-white no-underline hover:bg-[#0C4A5F]">Browse Explore</a>
                </div>
            </template>

            <template x-if="query.trim().length < 2">
                <p class="m-0 px-3 py-4 text-[13.5px] text-[#8A9199]">Type at least two letters to search destinations, trails, events, and FAQs.</p>
            </template>
        </div>
    </div>
</div>

{{-- ============ AlertDrawer ============ --}}
<div x-cloak x-show="alertsOpen" class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="Alerts and conditions">
    <div x-show="alertsOpen" @click="alertsOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
    <div x-show="alertsOpen" x-cc-trap="alertsOpen"
        class="cc-anim-up absolute inset-x-0 bottom-0 flex max-h-[85vh] flex-col rounded-t-2xl bg-white md:inset-x-auto md:inset-y-0 md:right-0 md:max-h-none md:w-[420px] md:rounded-none">
        <div class="flex items-center justify-between border-b border-[#EFE9DD] px-5 py-4">
            <h2 class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">Alerts &amp; conditions</h2>
            <button type="button" @click="alertsOpen = false" aria-label="Close alerts"
                class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#F2ECDF] text-[17px] text-[#5A646C]">✕</button>
        </div>
        <div class="flex flex-col gap-3 overflow-y-auto px-5 py-4">
            @foreach (collect($alerts ?? [])->sortBy(fn ($a) => ['closure' => 0, 'advisory' => 1, 'info' => 2][$a['severity']]) as $alert)
                <div @class([
                    'rounded-[10px] border p-3.5',
                    'border-[#E2A79D] bg-[#FBEAE7]' => $alert['severity'] === 'closure',
                    'border-[#E7C55C] bg-[#FCF1CF]' => $alert['severity'] === 'advisory',
                    'border-[#E0D9CB] bg-[#FAF7F0]' => $alert['severity'] === 'info',
                ])>
                    <div class="mb-1.5 flex items-center gap-2">
                        @if ($alert['severity'] === 'closure')
                            <svg class="flex-none" width="14" height="14" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="7" fill="#A63024"/><path d="M5 5l6 6M11 5l-6 6" stroke="#FBEAE7" stroke-width="1.8" stroke-linecap="round"/></svg>
                            <span class="text-[13.5px] font-extrabold text-[#7C2018]">{{ $alert['severityLabel'] }} · {{ strtolower($alert['scope']) }}</span>
                        @elseif ($alert['severity'] === 'advisory')
                            <svg class="flex-none" width="14" height="14" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 1.5 15 14H1L8 1.5Z" fill="#7A5200"/></svg>
                            <span class="text-[13.5px] font-extrabold text-[#5A4200]">{{ $alert['severityLabel'] }} · {{ strtolower($alert['scope']) }}</span>
                        @else
                            <span class="h-2.5 w-2.5 flex-none rounded-full bg-[#256E3C]"></span>
                            <span class="text-[13.5px] font-extrabold text-[#1C4A28]">{{ $alert['severityLabel'] }} · {{ strtolower($alert['scope']) }}</span>
                        @endif
                        <span class="ml-auto flex-none rounded border border-[#E7C55C] px-1.5 text-[10px] font-bold tracking-[.05em] text-[#7A5200] uppercase">Prototype data</span>
                    </div>
                    <p class="m-0 text-[13.5px] leading-relaxed text-[#3C3129]">{{ $alert['body'] }}</p>
                    <p class="m-0 mt-1.5 text-xs text-[#8A9199]">Posted {{ $alert['posted'] }}</p>
                </div>
            @endforeach

            <div class="mt-1 flex flex-col gap-2.5 border-t border-[#EFE9DD] pt-3.5">
                <p class="m-0 text-sm font-bold text-[#0B3A4E]">Get these by email or text</p>
                <button type="button" @click="openNotify()"
                    class="rounded-lg bg-[#0E5A73] px-4 py-3 text-center text-sm font-bold text-white hover:bg-[#0C4A5F]">Sign up for alerts</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ NotificationModal ============ --}}
<div x-cloak x-show="notifyOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="cc-notify-title">
    <div x-show="notifyOpen" @click="notifyOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
    <div x-show="notifyOpen" x-cc-trap="notifyOpen"
        class="cc-anim-fade relative w-full max-w-md overflow-hidden rounded-xl border border-[#E0D9CB] bg-white shadow-[0_16px_40px_rgba(20,30,35,.22)]">
        <div class="flex items-center justify-between px-5 pt-4">
            <h2 id="cc-notify-title" class="m-0 text-[19px] font-extrabold text-[#0B3A4E]">Sign up for alerts</h2>
            <button type="button" @click="notifyOpen = false" aria-label="Close signup"
                class="flex h-10 w-10 items-center justify-center rounded-lg bg-[#F2ECDF] text-[17px] text-[#5A646C]">✕</button>
        </div>

        <form x-show="! notifySent" @submit.prevent="notifySent = true" class="flex flex-col gap-3.5 px-5 pb-5 pt-3">
            <p class="m-0 text-[13.5px] leading-relaxed text-[#5A646C]">Choose your topics — we only send what you pick.</p>
            <div class="flex flex-wrap gap-2" role="group" aria-label="Alert topics">
                <template x-for="topic in ['Lake conditions', 'Trails', 'Camping', 'Events']" :key="topic">
                    <button type="button" @click="toggleTopic(topic)" :aria-pressed="notifyTopics.includes(topic)"
                        class="flex min-h-10 items-center gap-1.5 rounded-full border-[1.5px] px-3.5 py-2 text-[13px]"
                        :class="notifyTopics.includes(topic) ? 'border-[#0E5A73] bg-[#E3EEF2] font-bold text-[#0B3A4E]' : 'border-[#B9B0A0] font-semibold text-[#232A2E]'">
                        <svg x-show="notifyTopics.includes(topic)" width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m2.5 8.5 3.5 3.5 7.5-8" stroke="#0E5A73" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span x-text="topic"></span>
                    </button>
                </template>
            </div>
            <div class="flex flex-col gap-1.5">
                <label for="cc-notify-contact" class="text-[12.5px] font-bold text-[#3C454C]">Email or mobile number</label>
                <input id="cc-notify-contact" type="text" required placeholder="you@example.com"
                    class="rounded-lg border-[1.5px] border-[#B9B0A0] bg-white px-3.5 py-3 text-[14.5px] text-[#232A2E] placeholder:text-[#8A9199]">
            </div>
            <button type="submit" class="rounded-lg bg-[#0E5A73] px-4 py-3.5 text-[15px] font-extrabold text-white hover:bg-[#0C4A5F]">Subscribe</button>
            <p class="m-0 text-center text-[11.5px] text-[#8A9199]">Demo only — nothing is sent or stored. Production uses opt-in email, SMS, or push per RFP 2.5.</p>
        </form>

        <div x-show="notifySent" x-cloak class="flex flex-col items-center gap-3 px-5 pb-6 pt-3 text-center">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" fill="#E5F0E4"/><path d="m7.5 12.5 3 3 6-7" stroke="#256E3C" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <p class="m-0 text-[17px] font-extrabold text-[#0B3A4E]">You're on the list — in the demo, anyway.</p>
            <p class="m-0 max-w-[38ch] text-[13.5px] leading-relaxed text-[#5A646C]">This prototype doesn't send or store anything. On the production site, you'd now get <span class="font-bold" x-text="notifyTopics.join(', ').toLowerCase() || 'your chosen'"></span> updates.</p>
            <button type="button" @click="notifyOpen = false" class="rounded-lg bg-[#0E5A73] px-5 py-2.5 text-sm font-bold text-white hover:bg-[#0C4A5F]">Done</button>
        </div>
    </div>
</div>

{{-- ============ WebTrac external handoff ============ --}}
<div x-cloak x-show="webtracOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="cc-webtrac-title">
    <div x-show="webtracOpen" @click="webtracOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
    <div x-show="webtracOpen" x-cc-trap="webtracOpen"
        class="cc-anim-fade relative w-full max-w-sm overflow-hidden rounded-xl border border-[#E0D9CB] bg-white shadow-[0_16px_40px_rgba(20,30,35,.22)]">
        <div class="flex flex-col gap-3 p-5">
            <div class="flex items-center gap-2.5">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M13 5h6v6M19 5 9 15" stroke="#0E5A73" stroke-width="2" stroke-linecap="round"/><path d="M17 13v6H5V7h6" stroke="#0E5A73" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <h2 id="cc-webtrac-title" class="m-0 text-lg font-extrabold text-[#0B3A4E]">Heading to reservations</h2>
            </div>
            <p class="m-0 text-sm leading-relaxed text-[#3C3129]">You're leaving this concept site to finish in <strong>WebTrac</strong>, the county's existing reservation system, in a new tab.</p>
            <div class="rounded-lg bg-[#F2ECDF] px-3.5 py-3 text-[13px] text-[#3C454C]">
                <strong x-text="webtracContext.name"></strong>
                <span class="mt-0.5 block" x-text="webtracContext.detail"></span>
            </div>
            <a href="https://moclaycountyweb.myvscloud.com/webtrac/web/splash.html" target="_blank" rel="noopener"
                @click="webtracOpen = false"
                class="flex items-center justify-center gap-2 rounded-lg bg-[#E7C55C] px-4 py-3.5 text-[15px] font-extrabold text-[#2A2000] no-underline hover:bg-[#F0D276]">
                Continue to WebTrac
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span class="sr-only">(opens external site in a new tab)</span>
            </a>
            <button type="button" @click="webtracOpen = false" class="text-center text-[13px] font-bold text-[#0E5A73] underline underline-offset-2">Stay on this page</button>
        </div>
    </div>
</div>
