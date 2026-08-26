@extends('demo.pcc.layout', [
    'title' => 'Workforce & Community — Train, Hire, Partner | Pitt Community College (Concept Demo)',
    'metaDescription' => 'Customized training, apprenticeships, hiring PCC talent, the Small Business Center, and fast short-term credentials. Concept demo for RFP 115-6181.',
])

@section('content')
    @include('demo.pcc.partials.page-hero', [
        'eyebrow' => 'Workforce & Community',
        'heading' => "Eastern North Carolina's talent partner.",
        'intro' => "From customized training for your team to fast, affordable credentials for career changers — PCC is where the region's workforce gets built.",
        'ctas' => [
            ['label' => 'Partner with PCC', 'stub' => 'Partner with PCC', 'style' => 'gold'],
            ['label' => 'Browse short-term training', 'href' => route('pcc-demo.programs', ['area' => 'Continuing Education']), 'style' => 'outline'],
        ],
    ])

    <div class="mx-auto flex max-w-[1100px] flex-col gap-11 px-4 pb-[72px] pt-11 md:px-8">
        <section>
            <h2 class="pcc-display m-0 mb-4 text-[28px] font-extrabold text-[#0C2E52]">For employers &amp; partners</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ([
                    ['title' => 'Customized training', 'desc' => 'We design and deliver training for your team — on campus, on site or online — often grant-funded.', 'cta' => 'Train your team', 'stub' => 'Customized Training'],
                    ['title' => 'Apprenticeships & work-based learning', 'desc' => 'Grow your own talent pipeline with registered apprenticeships and student internships.', 'cta' => 'Build a pipeline', 'stub' => 'Apprenticeships'],
                    ['title' => 'Hire PCC talent', 'desc' => 'Post jobs, join career fairs and recruit graduates from 100+ programs.', 'cta' => 'Recruit here', 'stub' => 'Hire PCC Talent'],
                    ['title' => 'Small Business Center', 'desc' => 'Free confidential counseling and seminars for startups and small businesses in Pitt County.', 'cta' => 'Start something', 'stub' => 'Small Business Center'],
                ] as $card)
                    <button type="button" @click="openStub('{{ $card['stub'] }}')"
                        class="pcc-hoverable flex cursor-pointer flex-col gap-1.5 rounded-[10px] border-[1.5px] border-[#E3EAF2] bg-white p-[22px] text-left hover:border-[#0C2E52] hover:shadow-[0_10px_24px_rgba(12,46,82,.1)]">
                        <span class="pcc-display text-[19px] font-extrabold text-[#0C2E52]">{{ $card['title'] }}</span>
                        <span class="text-[14.5px] leading-normal text-[#4A5A6A]">{{ $card['desc'] }}</span>
                        <span class="mt-auto pt-1 text-sm font-bold text-[#0C5DA5]">{{ $card['cta'] }} &rarr;</span>
                    </button>
                @endforeach
            </div>
        </section>

        {{-- Partner spotlight — regional impact storytelling --}}
        <figure class="pcc-on-dark m-0 flex flex-col gap-3.5 rounded-xl bg-[#0C2E52] p-9">
            <span class="text-[13px] font-bold uppercase tracking-[.14em] text-[#FFB71B]">Small Business Center</span>
            <blockquote class="pcc-display m-0 max-w-[840px] text-[clamp(19px,2vw,24px)] font-bold leading-snug text-white">&ldquo;I am forever grateful for all that the SBC team at Pitt Community College has done for me and their amazing resources. They have shown me that I am capable of stepping into these shoes.&rdquo;</blockquote>
            <figcaption>
                <div class="text-[15.5px] font-bold text-white">Logann Miller</div>
                <div class="text-sm text-[#C9DAEC]">PCC Small Business Center Client &middot; 2026 SBCN Startup Showdown Finalist</div>
            </figcaption>
        </figure>

        <section>
            <h2 class="pcc-display m-0 mb-1.5 text-[28px] font-extrabold text-[#0C2E52]">Short-term training, real jobs</h2>
            <p class="m-0 mb-4 text-base text-[#4A5A6A]">Weeks — not years — to a paycheck. State workforce scholarships can cover most or all of the cost.</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @foreach ($shortTermPrograms as $program)
                    @include('demo.pcc.partials.program-card', [
                        'program' => $program,
                        'eyebrow' => $program['length'],
                    ])
                @endforeach
            </div>
        </section>
    </div>
@endsection
