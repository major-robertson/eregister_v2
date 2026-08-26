@extends('demo.pcc.layout', [
    'title' => 'Pitt Community College — Your Future Is Closer Than You Think (Concept Demo)',
    'metaDescription' => 'Redesign concept for PittCC.edu: audience-first pathways, program discovery, storytelling, and clear Apply / Request Info / Visit next steps. Concept demo for RFP 115-6181.',
])

@section('content')
    {{-- ============ Hero — video-style still with text overlay (Cape Fear pattern) ============ --}}
    <div class="pcc-on-dark relative min-h-[600px] bg-[#081F3A]">
        <div class="absolute inset-0 overflow-hidden">
            <img src="{{ asset('img/demos/pcc/hero-bruisers-crew.jpg') }}"
                alt=""
                class="pcc-hero-zoom absolute inset-0 h-full w-full object-cover">
        </div>
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(100deg,rgba(8,31,58,.92)_0%,rgba(8,31,58,.72)_45%,rgba(8,31,58,.15)_100%)]"></div>

        <div class="relative mx-auto max-w-[1320px] px-4 pb-[110px] pt-[90px] md:px-8">
            <div class="flex max-w-[640px] flex-col gap-[22px]">
                <p class="m-0 inline-flex items-center gap-2 text-[13px] font-bold uppercase tracking-[.14em] text-[#FFB71B]">
                    <span class="pcc-pulse-dot h-2 w-2 rounded-full bg-[#FFB71B]" aria-hidden="true"></span>
                    Fall registration is open
                </p>

                <button type="button" @click="openStub('Campus Video: This is PCC')"
                    class="pcc-hoverable inline-flex cursor-pointer items-center gap-2.5 self-start rounded-full border border-white/35 bg-white/10 py-1.5 pl-1.5 pr-4 text-[13.5px] font-bold text-white backdrop-blur-sm hover:bg-white/20">
                    <span class="inline-flex h-[30px] w-[30px] items-center justify-center rounded-full bg-[#FFB71B] pl-0.5 text-xs text-[#0C2E52]" aria-hidden="true">
                        <svg width="11" height="12" viewBox="0 0 11 12" fill="currentColor" aria-hidden="true"><path d="M0 0.5 11 6 0 11.5Z"/></svg>
                    </span>
                    Watch: This is PCC &middot; 0:47
                </button>

                <h1 class="pcc-display m-0 text-[clamp(38px,4.6vw,62px)] font-black leading-[1.04] text-white">Your future is closer than you think.</h1>
                <p class="m-0 max-w-[520px] text-[19px] leading-relaxed text-[#C9DAEC]">Since 1961, PCC has been educating and empowering people for success — with 100+ programs, real support, and a clear path from day one to your first day on the job.</p>

                <div class="flex flex-wrap gap-3">
                    <button type="button" @click="openStub('Apply to PCC')" class="pcc-hoverable cursor-pointer rounded bg-[#FFB71B] px-[26px] py-3.5 text-base font-extrabold text-[#0C2E52] hover:bg-[#E8A404]">Apply Now</button>
                    <button type="button" @click="openStub('Request Information')" class="pcc-hoverable cursor-pointer rounded border-2 border-white px-6 py-[13px] text-base font-bold text-white hover:bg-white/10">Request Information</button>
                    <button type="button" @click="openStub('Visit Campus')" class="pcc-hoverable cursor-pointer rounded border-2 border-white/40 px-6 py-[13px] text-base font-bold text-white hover:border-white">Visit Campus</button>
                </div>

                {{-- Prominent homepage search (Lenoir pattern) --}}
                <form action="{{ route('pcc-demo.programs') }}" method="get" role="search"
                    class="mt-2 flex max-w-[520px] overflow-hidden rounded-md bg-white shadow-[0_12px_32px_rgba(8,31,58,.35)]">
                    <label for="pcc-hero-q" class="sr-only">What do you want to study?</label>
                    <input id="pcc-hero-q" type="search" name="q" placeholder="What do you want to study?"
                        class="min-w-0 flex-1 border-none px-[18px] py-4 text-base text-[#1A2733] outline-none placeholder:text-[#5E7288]">
                    <button type="submit" class="pcc-hoverable flex cursor-pointer items-center gap-1.5 bg-[#0C2E52] px-6 font-bold text-white hover:bg-[#123E6B]">
                        <svg width="15" height="15" viewBox="0 0 16 16" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="4.5" stroke="currentColor" stroke-width="1.8"/><path d="m10.5 10.5 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        Search
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- ============ Audience pathways — "no wrong door" (Carteret pattern) ============ --}}
    <div class="bg-[#EDF3FA] px-4 py-14 md:px-8">
        <div class="mx-auto flex max-w-[1320px] flex-col gap-7">
            <div class="flex flex-wrap items-baseline justify-between gap-4">
                <h2 class="pcc-display m-0 text-[30px] font-extrabold text-[#0C2E52]">There's no wrong door at PCC.</h2>
                <span class="text-[15px] text-[#4A5A6A]">Tell us who you are — we'll take you where you need to go.</span>
            </div>

            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                @foreach ([
                    ['label' => 'Future Student', 'hint' => 'Programs, admissions & aid', 'href' => route('pcc-demo.admissions')],
                    ['label' => 'Current Student', 'hint' => 'Registration, advising, myPittCC', 'stub' => 'Current Students'],
                    ['label' => 'Parent or Family', 'hint' => 'How to support your student', 'stub' => 'Parents & Families'],
                    ['label' => 'Employer or Partner', 'hint' => 'Train and hire local talent', 'href' => route('pcc-demo.workforce')],
                    ['label' => 'Alum or Donor', 'hint' => 'Stay connected, give back', 'stub' => 'Alumni & Giving'],
                    ['label' => 'Job Seeker at PCC', 'hint' => 'Work with us', 'stub' => 'Careers at PCC'],
                ] as $tile)
                    @php
                        $tileClasses = 'pcc-hoverable flex flex-col gap-1.5 rounded-lg border-[1.5px] border-transparent bg-white px-5 py-[22px] text-left no-underline shadow-[0_2px_8px_rgba(12,46,82,.06)] hover:-translate-y-[3px] hover:border-[#0C2E52]';
                    @endphp
                    @isset ($tile['href'])
                        <a href="{{ $tile['href'] }}" class="{{ $tileClasses }}">
                    @else
                        <button type="button" @click="openStub('{{ $tile['stub'] }}')" class="{{ $tileClasses }} cursor-pointer">
                    @endisset
                        <span class="text-xs font-bold uppercase tracking-[.12em] text-[#5E7288]">I am a&hellip;</span>
                        <span class="pcc-display text-[19px] font-extrabold text-[#0C2E52]">{{ $tile['label'] }}</span>
                        <span class="text-sm leading-snug text-[#4A5A6A]">{{ $tile['hint'] }}</span>
                    @isset ($tile['href'])
                        </a>
                    @else
                        </button>
                    @endisset
                @endforeach
            </div>

            <div class="pcc-on-dark flex flex-wrap items-center gap-3 rounded-lg bg-[#0C2E52] px-[22px] py-[18px]">
                <span class="pcc-display mr-1.5 text-[17px] font-extrabold text-[#FFB71B]">I want to&hellip;</span>
                @foreach ([
                    ['label' => 'Find a program', 'href' => route('pcc-demo.programs')],
                    ['label' => 'Apply', 'stub' => 'Apply to PCC'],
                    ['label' => 'Pay for college', 'href' => route('pcc-demo.paying')],
                    ['label' => 'Register for classes', 'stub' => 'Registration'],
                    ['label' => 'Get short-term training', 'href' => route('pcc-demo.programs', ['area' => 'Continuing Education'])],
                    ['label' => 'Train my team', 'href' => route('pcc-demo.workforce')],
                    ['label' => 'Visit campus', 'stub' => 'Visit Campus'],
                    ['label' => 'Give', 'stub' => 'Give to PCC'],
                ] as $intent)
                    @php
                        $intentClasses = 'pcc-hoverable rounded-full border border-white/30 bg-white/10 px-[18px] py-2 text-[14.5px] font-semibold text-white no-underline hover:border-[#FFB71B] hover:bg-[#FFB71B] hover:text-[#0C2E52]';
                    @endphp
                    @isset ($intent['href'])
                        <a href="{{ $intent['href'] }}" class="{{ $intentClasses }}">{{ $intent['label'] }}</a>
                    @else
                        <button type="button" @click="openStub('{{ $intent['stub'] }}')" class="{{ $intentClasses }} cursor-pointer">{{ $intent['label'] }}</button>
                    @endisset
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ At a glance ============ --}}
    <div class="mx-auto max-w-[1320px] px-4 pb-6 pt-16 md:px-8">
        @include('demo.pcc.partials.stat-row', ['stats' => [
            ['value' => '7,400+', 'label' => 'students choosing PCC every year'],
            ['value' => '100+', 'label' => 'degree, diploma and certificate programs'],
            ['value' => '$76', 'label' => 'per credit hour for NC residents — a fraction of university cost'],
            ['value' => '1961', 'label' => 'educating and empowering people for success ever since'],
        ]])
    </div>

    {{-- ============ Interest areas — programs browsable like products ============ --}}
    <div class="mx-auto max-w-[1320px] px-4 py-14 md:px-8">
        <div class="mb-[26px] flex flex-wrap items-baseline justify-between gap-4">
            <h2 class="pcc-display m-0 text-[30px] font-extrabold text-[#0C2E52]">What are you interested in?</h2>
            <a href="{{ route('pcc-demo.programs') }}" class="text-[15px] font-bold text-[#0C5DA5] hover:text-[#083E70]">Browse all programs &rarr;</a>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($areas as $area)
                <a href="{{ route('pcc-demo.programs', ['area' => $area['name']]) }}"
                    class="pcc-on-dark pcc-hoverable relative flex min-h-[170px] items-end overflow-hidden rounded-[10px] bg-[linear-gradient(160deg,#123E6B,#081F3A)] no-underline hover:-translate-y-[3px] hover:shadow-[0_10px_24px_rgba(12,46,82,.3)]">
                    <span class="pcc-display absolute right-[18px] top-4 text-[44px] font-black leading-none text-[#FFB71B]/90" aria-hidden="true">{{ $area['count'] }}</span>
                    <span class="relative flex flex-col gap-1 p-5">
                        <span class="pcc-display text-[21px] font-extrabold text-white">{{ $area['name'] }}</span>
                        <span class="text-sm text-[#C9DAEC]">{{ $area['hint'] }} &rarr;</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ============ Story spotlight — authentic storytelling (Centre pattern) ============ --}}
    <div class="pcc-on-dark bg-[#0C2E52] px-4 py-[72px] md:px-8">
        <div class="mx-auto grid max-w-[1100px] grid-cols-1 items-center gap-12 lg:grid-cols-2">
            <div class="flex justify-center">
                <img src="{{ asset('img/demos/pcc/story-student.jpg') }}"
                    alt="PCC students on campus"
                    class="h-[320px] w-full max-w-[420px] rounded-[14px] object-cover sm:h-[420px]">
            </div>
            <figure class="m-0 flex flex-col gap-[18px]">
                <span class="text-[13px] font-bold uppercase tracking-[.14em] text-[#FFB71B]">Bulldog Stories</span>
                <blockquote class="pcc-display m-0 text-[clamp(22px,2.4vw,30px)] font-bold leading-snug text-white">&ldquo;I wanted a school that had great academics, was affordable, and allowed me to get involved outside of the classroom. PCC has allowed me to not only further my education but to become a leader among my peers.&rdquo;</blockquote>
                <figcaption>
                    <div class="text-base font-bold text-white">Aatman Sharma</div>
                    <div class="text-[14.5px] text-[#C9DAEC]">University Transfer &middot; 2026-27 SGA President</div>
                </figcaption>
                <div class="mt-1.5 flex flex-wrap gap-3">
                    <a href="{{ route('pcc-demo.program', $transferProgram['slug']) }}" class="pcc-hoverable rounded bg-[#FFB71B] px-5 py-3 font-extrabold text-[#0C2E52] no-underline hover:bg-[#E8A404]">Explore University Transfer</a>
                    <button type="button" @click="openStub('Student Stories')" class="pcc-hoverable cursor-pointer rounded border-[1.5px] border-white/50 px-5 py-3 font-bold text-white hover:border-white">More student stories</button>
                </div>
            </figure>
        </div>
    </div>

    {{-- ============ News + events ============ --}}
    <div class="mx-auto grid max-w-[1320px] grid-cols-1 gap-10 px-4 py-16 md:px-8 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="mb-5 flex items-baseline justify-between">
                <h2 class="pcc-display m-0 text-[26px] font-extrabold text-[#0C2E52]">Latest at PCC</h2>
                <button type="button" @click="openStub('News & Announcements')" class="cursor-pointer text-[14.5px] font-bold text-[#0C5DA5] hover:text-[#083E70]">All news &rarr;</button>
            </div>
            <div class="flex flex-col">
                @foreach ($news as $item)
                    <button type="button" @click="openStub('News & Announcements')"
                        class="flex cursor-pointer items-baseline gap-[18px] border-b border-[#E3EAF2] px-1 py-3.5 text-left hover:bg-[#F6F9FC]">
                        <span class="whitespace-nowrap text-[13px] font-bold text-[#5E7288]">{{ $item['date'] }}</span>
                        <span class="text-[16.5px] font-bold leading-normal text-[#0C2E52]">{{ $item['title'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
        <div>
            <div class="mb-5 flex items-baseline justify-between">
                <h2 class="pcc-display m-0 text-[26px] font-extrabold text-[#0C2E52]">Events</h2>
                <button type="button" @click="openStub('Events Calendar')" class="cursor-pointer text-[14.5px] font-bold text-[#0C5DA5] hover:text-[#083E70]">Calendar &rarr;</button>
            </div>
            <div class="flex flex-col gap-3">
                @foreach ($events as $event)
                    <button type="button" @click="openStub('Events Calendar')"
                        class="pcc-hoverable flex cursor-pointer items-center gap-3.5 rounded-lg bg-[#EDF3FA] px-4 py-3 text-left hover:bg-[#DFEAF6]">
                        <span class="pcc-display rounded-md bg-[#0C2E52] px-2.5 py-2 text-center text-[13px] font-extrabold leading-tight text-[#FFB71B]">{{ $event['month'] }}<br>{{ $event['day'] }}</span>
                        <span class="text-[15px] font-bold leading-snug text-[#0C2E52]">{{ $event['title'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ============ Take the next step ============ --}}
    @include('demo.pcc.partials.cta-band', [
        'heading' => 'Take the next step.',
        'sub' => 'Applying is free, and our admissions team walks with you the whole way — no wrong door, no dead ends.',
        'ctas' => [
            ['label' => 'Apply Now', 'stub' => 'Apply to PCC', 'style' => 'gold'],
            ['label' => 'Request Information', 'stub' => 'Request Information', 'style' => 'outline'],
            ['label' => 'Visit Campus', 'stub' => 'Visit Campus', 'style' => 'outline-dim'],
        ],
    ])
@endsection
