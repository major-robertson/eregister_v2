@extends('demo.pcc.layout', [
    'title' => 'Paying for College — Aid, Grants & Scholarships | Pitt Community College (Concept Demo)',
    'metaDescription' => 'Tuition, financial aid steps and every way to pay for PCC — federal grants, NC grants, Foundation scholarships and workforce scholarships. Concept demo for RFP 115-6181.',
])

@section('content')
    @include('demo.pcc.partials.page-hero', [
        'eyebrow' => 'Paying for College',
        'heading' => 'College you can actually afford.',
        'intro' => 'Between federal grants, state aid and PCC Foundation scholarships, most of our students pay far less than the sticker price — and many attend tuition-free.',
        'ctas' => [
            ['label' => 'Start the FAFSA', 'stub' => 'FAFSA (external link)', 'style' => 'gold'],
            ['label' => 'Estimate my cost', 'stub' => 'Net Price Calculator', 'style' => 'outline'],
        ],
    ])

    <div class="mx-auto flex max-w-[1100px] flex-col gap-11 px-4 pb-[72px] pt-11 md:px-8">
        {{-- Cost at a glance — financial info placed early (Centre pattern) --}}
        @include('demo.pcc.partials.stat-row', ['stats' => [
            ['value' => '$76', 'label' => 'per credit hour for NC residents'],
            ['value' => '~$2,432', 'label' => 'a full-time year of tuition & fees — about a third of a public university'],
            ['value' => '$0', 'label' => 'what many students pay after grants and scholarships'],
            ['value' => 'Free', 'label' => 'to apply for admission and for aid'],
        ]])

        <section>
            <h2 class="pcc-display m-0 mb-1.5 text-[28px] font-extrabold text-[#0C2E52]">Four steps to your aid</h2>
            <p class="m-0 mb-2.5 text-base text-[#4A5A6A]">One free application unlocks federal grants, state grants and most scholarships.</p>
            @include('demo.pcc.partials.step-list', ['steps' => [
                ['title' => "File the FAFSA — it's free", 'desc' => 'Complete the Free Application for Federal Student Aid and add Pitt Community College as a recipient. It\'s the single application behind federal grants, state grants and work-study.'],
                ['title' => 'Apply for PCC Foundation scholarships', 'desc' => 'One general application puts you in the running for dozens of Foundation scholarships — academic, program-specific and need-based.'],
                ['title' => 'Review your award letter', 'desc' => 'The Financial Aid Office packages everything you qualify for and sends your award. Finish your steps by the Early Advantage date and it arrives faster.'],
                ['title' => 'Accept your aid and enroll', 'desc' => 'Accept your award in myPittCC, register for classes, and your aid applies straight to your bill — including books at the bookstore.'],
            ]])
        </section>

        <section>
            <h2 class="pcc-display m-0 mb-4 text-[28px] font-extrabold text-[#0C2E52]">Ways to pay</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['title' => 'Federal grants', 'desc' => 'Pell and other federal grants — money you never pay back, based on the FAFSA.'],
                    ['title' => 'North Carolina state grants', 'desc' => 'Need-based aid for NC residents, awarded automatically from your FAFSA.'],
                    ['title' => 'PCC Foundation scholarships', 'desc' => 'Dozens of scholarships from local donors and partners — one application covers them all.'],
                    ['title' => 'Workforce scholarships', 'desc' => 'State scholarships that can cover most or all of short-term training like CDL and Nurse Aide.'],
                    ['title' => 'Veterans benefits', 'desc' => 'GI Bill® and military education benefits, with a dedicated Veterans Affairs office to help.'],
                    ['title' => 'Federal work-study', 'desc' => 'Earn while you learn with part-time campus jobs that fit your class schedule.'],
                ] as $aid)
                    <div class="flex flex-col gap-1.5 rounded-[10px] bg-[#EDF3FA] p-[22px]">
                        <span class="pcc-display text-lg font-extrabold text-[#0C2E52]">{{ $aid['title'] }}</span>
                        <span class="text-[14.5px] leading-normal text-[#33424F]">{{ $aid['desc'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        @include('demo.pcc.partials.cta-band', [
            'variant' => 'card',
            'heading' => 'Not sure what you qualify for?',
            'sub' => 'The Financial Aid Office will walk your options with you — no appointment needed.',
            'ctas' => [
                ['label' => 'Contact Financial Aid', 'stub' => 'Contact Financial Aid', 'style' => 'gold'],
                ['label' => 'See admissions steps', 'href' => route('pcc-demo.admissions'), 'style' => 'outline'],
            ],
        ])
    </div>
@endsection
