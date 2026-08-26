@php
    $navItems = [
        ['label' => 'Programs', 'href' => route('pcc-demo.programs'), 'active' => request()->routeIs('pcc-demo.programs', 'pcc-demo.program')],
        ['label' => 'Admissions', 'href' => route('pcc-demo.admissions'), 'active' => request()->routeIs('pcc-demo.admissions')],
        ['label' => 'Paying for College', 'href' => route('pcc-demo.paying'), 'active' => request()->routeIs('pcc-demo.paying')],
        ['label' => 'Student Life', 'href' => route('pcc-demo.student-life'), 'active' => request()->routeIs('pcc-demo.student-life')],
        ['label' => 'Workforce & Community', 'href' => route('pcc-demo.workforce'), 'active' => request()->routeIs('pcc-demo.workforce')],
        ['label' => 'About PCC', 'href' => route('pcc-demo.about'), 'active' => request()->routeIs('pcc-demo.about')],
    ];
@endphp

<div class="sticky top-0 z-50 bg-white shadow-[0_1px_0_rgba(12,46,82,.12)]">
    {{-- Utility bar — audience doors, per the "no wrong digital door" RFP goal --}}
    <div class="pcc-on-dark hidden justify-end gap-[22px] bg-[#0C2E52] px-8 py-1.5 text-[13px] text-[#C9DAEC] lg:flex" aria-label="Audience">
        <a href="{{ route('pcc-demo.admissions') }}" class="hover:text-white hover:underline">Future Students</a>
        <button type="button" @click="openStub('Current Students')" class="cursor-pointer hover:text-white hover:underline">Current Students</button>
        <button type="button" @click="openStub('Parents & Families')" class="cursor-pointer hover:text-white hover:underline">Parents &amp; Families</button>
        <a href="{{ route('pcc-demo.workforce') }}" class="hover:text-white hover:underline">Employers</a>
        <button type="button" @click="openStub('Faculty & Staff Intranet')" class="cursor-pointer hover:text-white hover:underline">Faculty &amp; Staff</button>
        <button type="button" @click="openStub('Give to PCC')" class="cursor-pointer hover:text-white hover:underline">Give</button>
    </div>

    <div class="mx-auto flex max-w-[1320px] items-center gap-7 px-4 py-3 md:px-8">
        <a href="{{ route('pcc-demo.home') }}" class="flex flex-shrink-0 items-center rounded-md bg-[#0C2E52] px-3.5 py-2">
            <img src="{{ asset('img/demos/pcc/logo-pcc.png') }}" alt="Pitt Community College — home" class="block h-10 lg:h-11" height="44">
        </a>

        <nav aria-label="Main" class="hidden flex-1 flex-wrap justify-center gap-[26px] lg:flex">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}"
                    @if ($item['active']) aria-current="page" @endif
                    class="border-b-[3px] px-0.5 py-2 text-[15.5px] font-bold text-[#0C2E52] no-underline hover:border-[#FFB71B] {{ $item['active'] ? 'border-[#FFB71B]' : 'border-transparent' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden flex-shrink-0 items-center gap-2.5 lg:flex">
            <a href="{{ route('pcc-demo.programs') }}" title="Search programs"
                class="pcc-hoverable inline-flex h-[38px] w-[38px] items-center justify-center rounded-full border-[1.5px] border-[#C9DAEC] text-[#0C2E52] hover:bg-[#EDF3FA]">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.8"/><path d="m10.5 10.5 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <span class="sr-only">Search programs</span>
            </a>
            <button type="button" @click="openStub('Request Information')"
                class="pcc-hoverable cursor-pointer rounded border-[1.5px] border-[#0C2E52] px-4 py-[9px] text-sm font-bold text-[#0C2E52] hover:bg-[#EDF3FA]">
                Request Info
            </button>
            <button type="button" @click="openStub('Apply to PCC')"
                class="pcc-hoverable cursor-pointer rounded bg-[#FFB71B] px-[18px] py-2.5 text-sm font-extrabold text-[#0C2E52] hover:bg-[#E8A404]">
                Apply Now
            </button>
        </div>

        <button type="button" @click="mobileOpen = true"
            class="ml-auto flex cursor-pointer flex-col gap-1 p-2.5 lg:hidden"
            :aria-expanded="mobileOpen" aria-controls="pcc-mobile-menu">
            <span class="sr-only">Open menu</span>
            <span class="h-[3px] w-[22px] rounded-sm bg-[#0C2E52]" aria-hidden="true"></span>
            <span class="h-[3px] w-[22px] rounded-sm bg-[#0C2E52]" aria-hidden="true"></span>
            <span class="h-[3px] w-[22px] rounded-sm bg-[#0C2E52]" aria-hidden="true"></span>
        </button>
    </div>
</div>

{{-- Mobile menu — full-screen navy overlay --}}
<div id="pcc-mobile-menu" x-cloak x-show="mobileOpen" x-pcc-trap="mobileOpen"
    class="pcc-on-dark pcc-anim-fade fixed inset-0 z-[90] flex flex-col gap-1.5 overflow-y-auto bg-[#0C2E52] p-6 text-white"
    role="dialog" aria-modal="true" aria-label="Menu">
    <div class="mb-3 flex items-center justify-between">
        <span class="pcc-display text-lg font-extrabold">Menu</span>
        <button type="button" @click="mobileOpen = false" class="cursor-pointer px-2.5 py-1 text-[26px]" data-autofocus>
            <span class="sr-only">Close menu</span><span aria-hidden="true">&times;</span>
        </button>
    </div>
    @foreach ($navItems as $item)
        <a href="{{ $item['href'] }}"
            class="pcc-display border-b border-white/15 py-2.5 text-[22px] font-bold text-white no-underline">
            {{ $item['label'] }}
        </a>
    @endforeach
    <div class="mt-4 flex flex-wrap gap-2.5">
        <button type="button" @click="openStub('Apply to PCC')" class="cursor-pointer rounded bg-[#FFB71B] px-5 py-3 font-extrabold text-[#0C2E52]">Apply Now</button>
        <button type="button" @click="openStub('Request Information')" class="cursor-pointer rounded border-[1.5px] border-white px-5 py-3 font-bold text-white">Request Info</button>
    </div>
</div>
