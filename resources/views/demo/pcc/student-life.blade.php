@extends('demo.pcc.layout', [
    'title' => 'Student Life — Clubs, Athletics & Support | Pitt Community College (Concept Demo)',
    'metaDescription' => 'Clubs, Bulldog Athletics, free tutoring, career services and whole-student support at PCC. Concept demo for RFP 115-6181.',
])

@section('content')
    @include('demo.pcc.partials.page-hero', [
        'eyebrow' => 'Student Life',
        'heading' => 'More than classes.',
        'intro' => 'Lead a club, cheer the Bulldogs, get free tutoring, find your people. Everything here exists to help you finish — and enjoy the ride.',
    ])

    <div class="mx-auto flex max-w-[1100px] flex-col gap-11 px-4 pb-[72px] pt-11 md:px-8">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['title' => 'Student Government & Leadership', 'desc' => 'Run for SGA, become a student ambassador, or lead one of dozens of clubs and organizations.', 'cta' => 'Get involved', 'stub' => 'Student Engagement and Leadership'],
                ['title' => 'Bulldog Athletics', 'desc' => 'Cheer on the PCC Bulldogs — or suit up. Baseball, basketball, softball, volleyball and more.', 'cta' => 'Go Bulldogs', 'stub' => 'Bulldog Athletics'],
                ['title' => 'Free tutoring (TASC)', 'desc' => 'The Tutorial and Academic Success Center offers free tutoring, study space and academic coaching.', 'cta' => 'Book a session', 'stub' => 'TASC Tutoring'],
                ['title' => 'Career Services', 'desc' => 'Resumes, mock interviews, job fairs and employer connections — from your first semester on.', 'cta' => 'Plan your career', 'stub' => 'Career Services'],
                ['title' => 'Library & study spaces', 'desc' => 'Research help, quiet floors, group rooms and tech you can borrow.', 'cta' => 'Visit the library', 'stub' => 'Library'],
                ['title' => 'Support & accessibility', 'desc' => 'Counseling, accessibility services and food resources — support for the whole student.', 'cta' => 'Find support', 'stub' => 'Student Support Services'],
            ] as $card)
                <button type="button" @click="openStub('{{ $card['stub'] }}')"
                    class="pcc-hoverable flex cursor-pointer flex-col gap-1.5 rounded-[10px] border-[1.5px] border-[#E3EAF2] bg-white p-[22px] text-left hover:border-[#0C2E52] hover:shadow-[0_10px_24px_rgba(12,46,82,.1)]">
                    <span class="pcc-display text-[19px] font-extrabold text-[#0C2E52]">{{ $card['title'] }}</span>
                    <span class="text-[14.5px] leading-normal text-[#4A5A6A]">{{ $card['desc'] }}</span>
                    <span class="mt-auto pt-1 text-sm font-bold text-[#0C5DA5]">{{ $card['cta'] }} &rarr;</span>
                </button>
            @endforeach
        </div>

        {{-- Coming up on campus --}}
        <div class="grid grid-cols-1 items-center gap-8 rounded-xl bg-[#EDF3FA] p-8 lg:grid-cols-2">
            <img src="{{ asset('img/demos/pcc/life-student-ambassadors.jpg') }}"
                alt="PCC Student Ambassadors gathered on campus"
                class="h-[300px] w-full rounded-[10px] object-cover">
            <div class="flex flex-col gap-3">
                <h2 class="pcc-display m-0 text-[26px] font-extrabold text-[#0C2E52]">Coming up on campus</h2>
                @foreach ($events as $event)
                    <button type="button" @click="openStub('Events Calendar')"
                        class="pcc-hoverable flex cursor-pointer items-center gap-3.5 rounded-lg bg-white px-4 py-3 text-left hover:shadow-[0_6px_16px_rgba(12,46,82,.12)]">
                        <span class="pcc-display rounded-md bg-[#0C2E52] px-2.5 py-2 text-center text-[13px] font-extrabold leading-tight text-[#FFB71B]">{{ $event['month'] }}<br>{{ $event['day'] }}</span>
                        <span class="text-[15px] font-bold leading-snug text-[#0C2E52]">{{ $event['title'] }}</span>
                    </button>
                @endforeach
                <button type="button" @click="openStub('Events Calendar')" class="cursor-pointer self-start text-[14.5px] font-bold text-[#0C5DA5] hover:text-[#083E70]">Full events calendar &rarr;</button>
            </div>
        </div>
    </div>
@endsection
