@php
    $active = $active ?? '';
    $navItems = [
        ['key' => 'lake', 'label' => 'Smithville Lake', 'href' => route('clay-demo.smithville-lake')],
        ['key' => 'trails', 'label' => 'Trails', 'href' => route('clay-demo.trails')],
        ['key' => 'historic', 'label' => 'Historic Sites', 'href' => route('clay-demo.historic-sites')],
        ['key' => 'events', 'label' => 'Events', 'href' => route('clay-demo.events')],
    ];
    $alertCount = count(array_filter($alerts ?? [], fn ($a) => $a['severity'] !== 'info'));
@endphp

{{-- ============ UtilityNav (desktop) ============ --}}
<div class="cc-on-dark hidden bg-[#0B3A4E] text-[#DCE8ED] md:block">
    <div class="mx-auto flex h-10 max-w-[1440px] items-center justify-between px-4 text-[13px] md:px-8 xl:px-12">
        <div class="flex items-center gap-2.5">
            <span class="font-semibold tracking-[.02em]">Clay County, Missouri</span>
            <span class="opacity-50" aria-hidden="true">|</span>
            <span class="opacity-85">Parks, Recreation &amp; Historic Sites</span>
        </div>
        <div class="flex items-center gap-1.5">
            <button type="button" @click="alertsOpen = true" class="cc-hoverable flex min-h-10 items-center gap-1.5 px-2.5 hover:text-white hover:underline">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M1 10c1.5-1.6 3.5-1.6 5 0s3.5 1.6 5 0 3.5-1.6 5 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M1 13.5c1.5-1.6 3.5-1.6 5 0s3.5 1.6 5 0 3.5-1.6 5 0" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Lake Conditions
            </button>
            <button type="button" @click="alertsOpen = true" class="cc-hoverable flex min-h-10 items-center gap-1.5 px-2.5 hover:text-white hover:underline">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M8 1.5 15 14H1L8 1.5Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><path d="M8 6.5v3.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="8" cy="12" r=".9" fill="currentColor"/></svg>
                Alerts
                @if ($alertCount > 0)
                    <span class="rounded-full bg-[#E7C55C] px-[7px] py-px text-[11px] font-bold text-[#3E2E00]">{{ $alertCount }}</span>
                @endif
            </button>
            <button type="button" @click="openSearch()" class="cc-hoverable flex min-h-10 items-center gap-1.5 px-2.5 hover:text-white hover:underline">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.5"/><path d="m10.5 10.5 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                Search
            </button>
            <button type="button" @click="openWebtrac()" class="cc-hoverable ml-2 flex items-center gap-1.5 rounded-md bg-[#E7C55C] px-3.5 py-[5px] font-bold text-[#2A2000] hover:bg-[#F0D276]">
                Reserve
                <svg width="12" height="12" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M6 3h7v7M13 3 3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span class="sr-only">(opens the WebTrac reservation system)</span>
            </button>
        </div>
    </div>
</div>

{{-- ============ SiteHeader ============ --}}
<header class="sticky top-0 z-40 border-b border-[#E0D9CB] bg-white lg:static">
    <div class="mx-auto flex h-[60px] max-w-[1440px] items-center justify-between px-4 md:px-8 lg:h-[76px] xl:px-12">
        <a href="{{ route('clay-demo.home') }}" class="flex items-center gap-3 no-underline">
            <img src="{{ asset('img/demos/clay-county/seal-clay-county.png') }}" alt="Seal of Clay County, Missouri" class="h-10 w-10 flex-none rounded-full lg:h-[46px] lg:w-[46px]" width="46" height="46">
            <span>
                <span class="block text-base font-extrabold tracking-[-.01em] text-[#0B3A4E] lg:text-lg">Clay County Parks</span>
                <span class="block text-[10px] font-semibold tracking-[.04em] text-[#5A646C] uppercase lg:text-xs">Recreation &amp; Historic Sites</span>
            </span>
        </a>

        {{-- Desktop navigation --}}
        <nav aria-label="Main" class="relative hidden items-center gap-1 lg:flex">
            <div class="relative">
                <button type="button"
                    @click="exploreOpen = !exploreOpen"
                    @click.outside="exploreOpen = false"
                    :aria-expanded="exploreOpen"
                    aria-haspopup="true"
                    class="cc-hoverable flex items-center gap-1 rounded-lg px-3.5 py-2.5 text-[15px] font-semibold text-[#232A2E] hover:bg-[#F2ECDF] hover:text-[#0B3A4E] {{ $active === 'explore' ? 'bg-[#F2ECDF] text-[#0B3A4E] shadow-[inset_0_-3px_0_#0E5A73]' : '' }}">
                    Explore
                    <svg width="10" height="10" viewBox="0 0 16 16" fill="none" aria-hidden="true" :style="exploreOpen ? 'transform: rotate(180deg)' : ''"><path d="m3 6 5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>

                {{-- Mega-dropdown --}}
                <div x-cloak x-show="exploreOpen"
                    class="cc-anim-fade absolute left-0 top-full z-50 mt-2 w-[560px] overflow-hidden rounded-xl border border-[#E0D9CB] bg-white shadow-[0_8px_28px_rgba(20,30,35,.12)]">
                    <div class="grid grid-cols-3 gap-1 p-4">
                        <div class="flex flex-col gap-0.5">
                            <span class="px-2.5 py-1.5 text-[11px] font-extrabold tracking-[.07em] text-[#5A646C] uppercase">By place</span>
                            <a href="{{ route('clay-demo.smithville-lake') }}" class="rounded-md px-2.5 py-2 text-sm font-semibold text-[#232A2E] no-underline hover:bg-[#F2ECDF]">Smithville Lake</a>
                            <a href="{{ route('clay-demo.explore') }}" class="rounded-md px-2.5 py-2 text-sm font-semibold text-[#232A2E] no-underline hover:bg-[#F2ECDF]">Parks directory</a>
                            <a href="{{ route('clay-demo.historic-sites') }}" class="rounded-md px-2.5 py-2 text-sm font-semibold text-[#232A2E] no-underline hover:bg-[#F2ECDF]">Historic sites</a>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="px-2.5 py-1.5 text-[11px] font-extrabold tracking-[.07em] text-[#5A646C] uppercase">By activity</span>
                            <a href="{{ route('clay-demo.smithville-lake') }}#camping" class="rounded-md px-2.5 py-2 text-sm font-semibold text-[#232A2E] no-underline hover:bg-[#F2ECDF]">Camping</a>
                            <a href="{{ route('clay-demo.smithville-lake') }}#activities" class="rounded-md px-2.5 py-2 text-sm font-semibold text-[#232A2E] no-underline hover:bg-[#F2ECDF]">Boating &amp; fishing</a>
                            <a href="{{ route('clay-demo.smithville-lake') }}#beaches" class="rounded-md px-2.5 py-2 text-sm font-semibold text-[#232A2E] no-underline hover:bg-[#F2ECDF]">Beaches &amp; marinas</a>
                        </div>
                        <div class="flex flex-col gap-0.5">
                            <span class="px-2.5 py-1.5 text-[11px] font-extrabold tracking-[.07em] text-[#5A646C] uppercase">Plan</span>
                            <button type="button" @click="alertsOpen = true; exploreOpen = false" class="rounded-md px-2.5 py-2 text-left text-sm font-semibold text-[#232A2E] hover:bg-[#F2ECDF]">Lake conditions</button>
                            <button type="button" @click="openWebtrac()" class="rounded-md px-2.5 py-2 text-left text-sm font-semibold text-[#232A2E] hover:bg-[#F2ECDF]">Reservations</button>
                            <a href="{{ route('clay-demo.events') }}" class="rounded-md px-2.5 py-2 text-sm font-semibold text-[#232A2E] no-underline hover:bg-[#F2ECDF]">Events</a>
                        </div>
                    </div>
                </div>
            </div>

            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}"
                    @if ($active === $item['key']) aria-current="page" @endif
                    class="cc-hoverable rounded-lg px-3.5 py-2.5 text-[15px] font-semibold no-underline hover:bg-[#F2ECDF] hover:text-[#0B3A4E] {{ $active === $item['key'] ? 'bg-[#F2ECDF] text-[#0B3A4E] shadow-[inset_0_-3px_0_#0E5A73]' : 'text-[#232A2E]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach

            <a href="{{ route('clay-demo.plan-your-visit') }}"
                @if ($active === 'plan') aria-current="page" @endif
                class="cc-hoverable ml-2 rounded-lg bg-[#0E5A73] px-5 py-[11px] text-[15px] font-bold text-white no-underline hover:bg-[#0C4A5F]">
                Plan Your Visit
            </a>
        </nav>

        {{-- Mobile actions --}}
        <div class="flex items-center gap-1 lg:hidden">
            <button type="button" @click="openSearch()" aria-label="Search"
                class="flex h-11 w-11 items-center justify-center rounded-lg text-[#0B3A4E] hover:bg-[#F2ECDF]">
                <svg width="20" height="20" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.5"/><path d="m10.5 10.5 4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>
            <button type="button" @click="mobileOpen = true" aria-label="Open menu" :aria-expanded="mobileOpen"
                class="flex h-11 w-11 items-center justify-center rounded-lg text-[#0B3A4E] hover:bg-[#F2ECDF]">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>
</header>

{{-- ============ MobileNavigation sheet ============ --}}
<div x-cloak x-show="mobileOpen" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
    <div x-show="mobileOpen" @click="mobileOpen = false" class="absolute inset-0 bg-[#232A2E]/60"></div>
    <div x-show="mobileOpen" x-cc-trap="mobileOpen"
        class="cc-anim-right cc-on-dark absolute inset-y-0 right-0 flex w-80 max-w-[85vw] flex-col bg-[#0B3A4E]">
        <div class="flex items-center justify-between border-b border-[#9CC3D2]/25 px-4 py-3.5">
            <span class="text-[15px] font-extrabold text-[#FAF7F0]">Menu</span>
            <button type="button" @click="mobileOpen = false" aria-label="Close menu"
                class="flex h-10 w-10 items-center justify-center rounded-lg border border-[#9CC3D2]/40 text-lg text-[#FAF7F0]">✕</button>
        </div>
        <nav aria-label="Mobile" class="flex flex-1 flex-col overflow-y-auto px-2 pt-2.5">
            <a href="{{ route('clay-demo.explore') }}" class="rounded-lg px-3 py-3.5 text-base font-bold text-[#FAF7F0] no-underline {{ $active === 'explore' ? 'bg-[#FAF7F0]/10' : '' }}">Explore</a>
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}"
                    @if ($active === $item['key']) aria-current="page" @endif
                    class="rounded-lg px-3 py-3.5 text-base font-semibold no-underline {{ $active === $item['key'] ? 'bg-[#FAF7F0]/10 text-[#FAF7F0]' : 'text-[#C9DAE1]' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
            <a href="{{ route('clay-demo.plan-your-visit') }}" class="rounded-lg px-3 py-3.5 text-base font-semibold no-underline {{ $active === 'plan' ? 'bg-[#FAF7F0]/10 text-[#FAF7F0]' : 'text-[#C9DAE1]' }}">Plan Your Visit</a>
        </nav>
        <div class="flex gap-2.5 border-t border-[#9CC3D2]/25 px-4 pb-5 pt-3">
            <button type="button" @click="openWebtrac()" class="flex-1 rounded-lg bg-[#E7C55C] px-3 py-3 text-sm font-extrabold text-[#2A2000]">Reserve</button>
            <button type="button" @click="mobileOpen = false; alertsOpen = true" class="flex-1 rounded-lg border-2 border-[#6F97A8] px-3 py-[10px] text-sm font-bold text-[#FAF7F0]">Conditions</button>
        </div>
    </div>
</div>
