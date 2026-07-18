@extends('demo.clay.layout', [
    'active' => 'plan',
    'title' => 'Plan Your Visit — Hours, Rules, Reservations & FAQs | Clay County Parks (Concept Demo)',
    'metaDescription' => 'Everything to know before you go: campground check-in times, beach seasons, park rules, reservations through WebTrac, and answers to the most-asked questions.',
    'ogImage' => 'camping.webp',
])

@section('content')

    {{-- Page header --}}
    <div class="mx-auto max-w-[1440px] px-4 pb-6 pt-7 md:px-8 xl:px-12">
        <nav aria-label="Breadcrumb" class="mb-2 text-[13px] text-[#5A646C]">
            <a href="{{ route('clay-demo.home') }}">Home</a>
            <span class="mx-1.5 text-[#B9B0A0]" aria-hidden="true">/</span>
            <span class="font-semibold text-[#232A2E]" aria-current="page">Plan Your Visit</span>
        </nav>
        <h1 class="m-0 text-[28px] font-extrabold tracking-[-.02em] text-[#0B3A4E] xl:text-4xl">Plan your visit</h1>
        <p class="m-0 mt-2 max-w-[64ch] text-[15px] leading-relaxed text-[#5A646C]">The practical stuff, all in one place — when things are open, what the rules are, and how to reserve. High-traffic answers live here so they're never more than two clicks away.</p>
    </div>

    {{-- Quick action cards --}}
    <div class="mx-auto grid max-w-[1440px] grid-cols-1 gap-4 px-4 pb-4 sm:grid-cols-2 md:px-8 xl:grid-cols-4 xl:px-12">
        <button type="button" @click="openWebtrac()"
            class="cc-hoverable flex flex-col gap-2 rounded-xl border border-[#E0D9CB] bg-white p-5 text-left hover:border-[#0E5A73] hover:shadow-[0_6px_18px_rgba(20,30,35,.1)]">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 3 19h18L12 3Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 10v9" stroke="#0E5A73" stroke-width="1.8"/></svg>
            <span class="text-[15.5px] font-extrabold text-[#0B3A4E]">Reservations</span>
            <span class="text-[13.5px] leading-normal text-[#5A646C]">Campsites, shelters, and slips through WebTrac, or call 816-407-3400 (option 3).</span>
        </button>
        <button type="button" @click="alertsOpen = true"
            class="cc-hoverable flex flex-col gap-2 rounded-xl border border-[#E0D9CB] bg-white p-5 text-left hover:border-[#0E5A73] hover:shadow-[0_6px_18px_rgba(20,30,35,.1)]">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2 14c2-2 4.5-2 6.5 0s4.5 2 6.5 0 4.5-2 7 0" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/><path d="M2 19c2-2 4.5-2 6.5 0s4.5 2 6.5 0 4.5-2 7 0" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="text-[15.5px] font-extrabold text-[#0B3A4E]">Conditions &amp; alerts</span>
            <span class="text-[13.5px] leading-normal text-[#5A646C]">Lake level, ramps, beaches, and closures — and alerts by email or text.</span>
        </button>
        <a href="https://www.google.com/maps/search/?api=1&query=17201+Paradesian+Smithville+MO+64089" target="_blank" rel="noopener"
            class="cc-hoverable flex flex-col gap-2 rounded-xl border border-[#E0D9CB] bg-white p-5 no-underline hover:border-[#0E5A73] hover:shadow-[0_6px_18px_rgba(20,30,35,.1)]">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-6.1-7-11a7 7 0 0 1 14 0c0 4.9-7 11-7 11Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.6" stroke="#0E5A73" stroke-width="1.8"/></svg>
            <span class="text-[15.5px] font-extrabold text-[#0B3A4E]">Getting here<span class="sr-only"> (opens external map)</span></span>
            <span class="text-[13.5px] leading-normal text-[#5A646C]">30 minutes from downtown KC; 15 from Smithville and Kearney. Park office: 17201 Paradesian.</span>
        </a>
        <a href="https://www.claycountymo.gov/165/Parks-Recreation" target="_blank" rel="noopener"
            class="cc-hoverable flex flex-col gap-2 rounded-xl border border-[#E0D9CB] bg-white p-5 no-underline hover:border-[#0E5A73] hover:shadow-[0_6px_18px_rgba(20,30,35,.1)]">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="2" stroke="#0E5A73" stroke-width="1.8"/><path d="M8 9h8M8 13h8M8 17h5" stroke="#0E5A73" stroke-width="1.8" stroke-linecap="round"/></svg>
            <span class="text-[15.5px] font-extrabold text-[#0B3A4E]">Fees &amp; permits<span class="sr-only"> (opens external site)</span></span>
            <span class="text-[13.5px] leading-normal text-[#5A646C]">Current fees are published on the county's official pages — always up to date there.</span>
        </a>
    </div>

    {{-- Key rules --}}
    <section aria-label="Hours and rules" class="mx-auto max-w-[1440px] px-4 pb-4 pt-8 md:px-8 xl:px-12">
        <h2 class="m-0 mb-5 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[26px]">Hours &amp; ground rules</h2>
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-[#E0D9CB] bg-white p-5">
                <h3 class="m-0 mb-2.5 flex items-center gap-2 text-[15.5px] font-extrabold text-[#0B3A4E]">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 3 19h18L12 3Z" stroke="#0E5A73" stroke-width="1.8" stroke-linejoin="round"/><path d="M12 10v9" stroke="#0E5A73" stroke-width="1.8"/></svg>
                    Camping
                </h3>
                <ul class="m-0 flex list-none flex-col gap-2 p-0 text-[13.5px] leading-normal text-[#3C454C]">
                    <li>Check-in 1:00 PM · check-out 11:00 AM (Sun 5:00/3:00)</li>
                    <li>Two-night minimum on weekends; 15-day max per 30 days</li>
                    <li>Up to 6 people, 2 tents or tent + camper per site</li>
                    <li>Quiet hours 10:00 PM – 6:00 AM</li>
                    <li>Firewood must be burned on site (Emerald Ash Borer)</li>
                </ul>
            </div>
            <div class="rounded-xl border border-[#E0D9CB] bg-white p-5">
                <h3 class="m-0 mb-2.5 flex items-center gap-2 text-[15.5px] font-extrabold text-[#0B3A4E]">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="8" r="4" stroke="#B98A54" stroke-width="1.8"/><path d="M3 20c2-2 5-3 9-3s7 1 9 3" stroke="#B98A54" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Beaches
                </h3>
                <ul class="m-0 flex list-none flex-col gap-2 p-0 text-[13.5px] leading-normal text-[#3C454C]">
                    <li>Open May 1 – September 15, 8:30 AM to sunset</li>
                    <li>No lifeguard — swim at your own risk</li>
                    <li>No pets, glass, or alcohol on the beaches</li>
                    <li>Water quality sampled biweekly in season</li>
                </ul>
            </div>
            <div class="rounded-xl border border-[#E0D9CB] bg-white p-5">
                <h3 class="m-0 mb-2.5 flex items-center gap-2 text-[15.5px] font-extrabold text-[#0B3A4E]">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="#35663C" stroke-width="1.8"/><path d="M8 12h8M12 8v8" stroke="#35663C" stroke-width="1.8" stroke-linecap="round"/></svg>
                    Everywhere
                </h3>
                <ul class="m-0 flex list-none flex-col gap-2 p-0 text-[13.5px] leading-normal text-[#3C454C]">
                    <li>Pets on a 6-ft leash, max 2 dogs (off-leash dog park at the lake)</li>
                    <li>No fireworks, golf carts, or ATVs</li>
                    <li>Cash, check, Mastercard, Visa &amp; Discover accepted</li>
                    <li>911 emergency markers along all trails</li>
                </ul>
            </div>
        </div>
        <p class="m-0 mt-3.5 text-[13px] text-[#8A9199]">Summarized from the county's published policies — the <a href="https://www.claycountymo.gov/171/Camping-Shelters-Reservations" target="_blank" rel="noopener">official rules pages<span class="sr-only"> (external site)</span></a> govern.</p>
    </section>

    {{-- FAQ --}}
    <section id="faq" aria-label="Frequently asked questions" class="mx-auto max-w-[1440px] scroll-mt-24 px-4 pb-8 pt-8 md:px-8 xl:px-12">
        <h2 class="m-0 mb-5 text-[22px] font-extrabold tracking-[-.01em] text-[#0B3A4E] xl:text-[26px]">Frequently asked questions</h2>
        <div class="flex max-w-[860px] flex-col gap-2.5">
            @foreach ($faqs as $faq)
                <details class="rounded-[10px] border border-[#E0D9CB] bg-white px-5">
                    <summary class="flex min-h-11 items-center justify-between gap-3 py-4 text-[15.5px] font-bold text-[#0B3A4E]">
                        {{ $faq['question'] }}
                        <svg class="cc-chevron flex-none" width="14" height="14" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="m3 6 5 5 5-5" stroke="#5A646C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </summary>
                    <p class="m-0 pb-4 pl-1 text-[14.5px] leading-relaxed text-[#3C454C]">{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- Contact band --}}
    <section aria-label="Contact the department" class="mx-auto max-w-[1440px] px-4 pb-14 md:px-8 xl:px-12">
        <div class="cc-on-dark flex flex-col items-start justify-between gap-6 rounded-2xl bg-[#0B3A4E] p-7 text-[#FAF7F0] md:flex-row md:items-center md:p-10">
            <div>
                <h2 class="m-0 text-[21px] font-extrabold">Still have a question?</h2>
                <p class="m-0 mt-1.5 max-w-[52ch] text-[15px] leading-relaxed text-[#C9DAE1]">The parks team is at 17201 Paradesian, Smithville — <a href="tel:816-407-3400" class="font-bold text-[#E7C55C]">816-407-3400</a> or <a href="mailto:parks@claycountymo.gov" class="font-bold text-[#E7C55C]">parks@claycountymo.gov</a>. For historic sites, call <a href="tel:816-736-8500" class="font-bold text-[#E7C55C]">816-736-8500</a>.</p>
            </div>
            <button type="button" @click="openNotify()"
                class="cc-hoverable flex-none rounded-lg bg-[#E7C55C] px-6 py-3.5 text-[15px] font-extrabold text-[#2A2000] hover:bg-[#F0D276]">Sign up for alerts</button>
        </div>
    </section>
@endsection
