@extends('demo.pcc.layout', [
    'title' => 'Admissions — Pick the Path That Fits You | Pitt Community College (Concept Demo)',
    'metaDescription' => 'Step-by-step admissions pathways for first-time students, transfers, adult learners and high school students. Concept demo for RFP 115-6181.',
])

@section('content')
    @include('demo.pcc.partials.page-hero', [
        'eyebrow' => 'Admissions',
        'heading' => 'Your path starts here — pick the one that fits you.',
        'intro' => "Every student's road to PCC looks a little different. Choose who you are and we'll show you exactly what to do, step by step.",
    ])

    <div class="mx-auto flex max-w-[1100px] flex-col gap-[30px] px-4 pb-[72px] pt-9 md:px-8"
        x-data="{ type: @js($initialType) }">

        {{-- Student-type switcher (Cape Fear admissions-steps pattern) --}}
        <div class="flex flex-wrap gap-2.5" role="group" aria-label="Choose your student type">
            @foreach ($pathways as $key => $pathway)
                {{-- State classes live only in the Alpine binding: static copies
                     would linger after a click and fight the bound ones. --}}
                <button type="button" @click="type = '{{ $key }}'"
                    :class="type === '{{ $key }}' ? 'border-[#0C2E52] bg-[#0C2E52] text-white' : 'border-[#C9DAEC] bg-white text-[#0C2E52]'"
                    :aria-pressed="type === '{{ $key }}'"
                    class="pcc-display pcc-hoverable cursor-pointer rounded-md border-[1.5px] px-5 py-3 text-[15.5px] font-extrabold hover:border-[#0C2E52]">
                    {{ $pathway['label'] }}
                </button>
            @endforeach
        </div>

        @foreach ($pathways as $key => $pathway)
            <div x-show="type === '{{ $key }}'" style="{{ $key === $initialType ? '' : 'display:none' }}" class="flex flex-col gap-[30px]">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <p class="m-0 rounded-[10px] bg-[#EDF3FA] px-6 py-5 text-base font-bold leading-normal text-[#0C2E52]">{{ $pathway['intro'] }}</p>
                    <div class="flex flex-col gap-2 rounded-[10px] border-[1.5px] border-[#E3EAF2] bg-white px-6 py-5">
                        <span class="text-sm font-extrabold uppercase tracking-[.08em] text-[#0C2E52]">You're in the right place if&hellip;</span>
                        @foreach ($pathway['fit'] as $item)
                            <div class="flex items-baseline gap-2.5">
                                <span class="font-black text-[#E8A404]" aria-hidden="true">&check;</span>
                                <span class="text-[15px] leading-normal text-[#33424F]">{{ $item }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                @include('demo.pcc.partials.step-list', ['steps' => $pathway['steps']])
            </div>
        @endforeach

        {{-- Early Advantage callout --}}
        <div class="flex flex-wrap items-center gap-5 rounded-xl border-[1.5px] border-[#F2D48A] bg-[#FFF4DC] px-7 py-6">
            <span class="pcc-display whitespace-nowrap rounded-md bg-[#FFB71B] px-3.5 py-2.5 text-sm font-black uppercase tracking-[.06em] text-[#0C2E52]">Early Advantage</span>
            <span class="min-w-[260px] flex-1 text-[15.5px] leading-normal text-[#5A430A]">Finish your admissions steps early and you'll be awarded financial aid faster, get first-choice course sections, and complete New Student Orientation ahead of your classmates.</span>
        </div>

        {{-- Key dates --}}
        <dl class="m-0 grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['date' => 'Anytime', 'label' => 'Applications accepted year-round — no fee'],
                ['date' => 'Jun 1', 'label' => 'Early Advantage priority date'],
                ['date' => 'Aug 18', 'label' => 'Fall classes begin'],
                ['date' => 'Jan 8', 'label' => 'Spring classes begin'],
            ] as $date)
                <div class="border-t-4 border-[#FFB71B] px-1 pt-3.5">
                    <dd class="pcc-display m-0 text-2xl font-black text-[#0C2E52]">{{ $date['date'] }}</dd>
                    <dt class="mt-0.5 text-[14.5px] text-[#4A5A6A]">{{ $date['label'] }}</dt>
                </div>
            @endforeach
        </dl>

        {{-- Good-to-know cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <section class="rounded-[10px] bg-[#EDF3FA] p-[22px]">
                <h2 class="pcc-display m-0 mb-1.5 text-[17px] font-extrabold text-[#0C2E52]">Transcripts</h2>
                <p class="m-0 text-[14.5px] leading-normal text-[#33424F]">Send your high school (or GED/HiSET) transcript to Admissions &amp; Records — unofficial copies can get you started while officials arrive.</p>
            </section>
            <section class="rounded-[10px] bg-[#EDF3FA] p-[22px]">
                <h2 class="pcc-display m-0 mb-1.5 text-[17px] font-extrabold text-[#0C2E52]">Placement</h2>
                <p class="m-0 text-[14.5px] leading-normal text-[#33424F]">Most students place with their high school GPA or prior college English/math — no test needed. Otherwise the free RISE assessment is offered on campus.</p>
            </section>
            <section class="rounded-[10px] bg-[#EDF3FA] p-[22px]">
                <h2 class="pcc-display m-0 mb-1.5 text-[17px] font-extrabold text-[#0C2E52]">Health Sciences programs</h2>
                <p class="m-0 text-[14.5px] leading-normal text-[#33424F]">Nursing, Dental Hygiene, Radiography and similar programs use a separate competitive admissions process with spring application windows. <a href="{{ route('pcc-demo.programs', ['area' => 'Health Sciences']) }}" class="font-semibold text-[#0C5DA5] hover:text-[#083E70]">Browse Health Sciences &rarr;</a></p>
            </section>
            <section class="rounded-[10px] bg-[#EDF3FA] p-[22px]">
                <h2 class="pcc-display m-0 mb-1.5 text-[17px] font-extrabold text-[#0C2E52]">Visiting students</h2>
                <p class="m-0 text-[14.5px] leading-normal text-[#33424F]">Enrolled elsewhere and want to take a PCC course that transfers back? Apply as a visiting student — no transcripts required.</p>
            </section>
        </div>

        @include('demo.pcc.partials.cta-band', [
            'variant' => 'card',
            'heading' => 'Ready when you are.',
            'sub' => 'Questions at any step? Admissions & Records: 252-493-7245.',
            'ctas' => [
                ['label' => 'Apply Now', 'stub' => 'Apply to PCC', 'style' => 'gold'],
                ['label' => 'Visit Campus', 'stub' => 'Visit Campus', 'style' => 'outline'],
            ],
        ])
    </div>
@endsection
