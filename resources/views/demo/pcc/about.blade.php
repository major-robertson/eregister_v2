@extends('demo.pcc.layout', [
    'title' => 'About PCC — Educating & Empowering Since 1961 | Pitt Community College (Concept Demo)',
    'metaDescription' => 'PCC\'s mission, leadership and impact across Pitt County and eastern North Carolina since 1961. Concept demo for RFP 115-6181.',
])

@section('content')
    @include('demo.pcc.partials.page-hero', [
        'eyebrow' => 'About PCC',
        'heading' => 'Educating and empowering people for success — since 1961.',
    ])

    <div class="mx-auto flex max-w-[1100px] flex-col gap-11 px-4 pb-[72px] pt-11 md:px-8">
        <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-2">
            <section class="flex flex-col gap-3">
                <h2 class="pcc-display m-0 text-[26px] font-extrabold text-[#0C2E52]">Our mission</h2>
                <p class="m-0 text-[16.5px] leading-relaxed text-[#33424F]">Pitt Community College educates and empowers people for success. With a culture of excellence and innovation, the college is a vital partner in the economic and workforce development of our community. PCC provides access to dynamic learning opportunities designed to foster personal enrichment, successful career preparation, and higher education transfer.</p>
                <p class="m-0 text-[16.5px] leading-relaxed text-[#33424F]">PCC is accredited by the Southern Association of Colleges and Schools Commission on Colleges (SACSCOC) to award associate degrees, and serves more than 7,400 students each year across Pitt County and eastern North Carolina.</p>
            </section>

            <figure class="m-0 flex flex-col gap-3.5 rounded-xl bg-[#EDF3FA] p-7">
                <img src="{{ asset('img/demos/pcc/president-maria-pharr.jpg') }}"
                    alt="Dr. Maria A. Pharr, PCC President"
                    class="h-[150px] w-[150px] rounded-full border-4 border-white object-cover object-top">
                <blockquote class="m-0 text-[15.5px] leading-relaxed text-[#33424F]">&ldquo;For more than 60 years, PCC has been doing its part to establish a skilled workforce that meets the needs of our community. Throughout that time, students have been our primary focus — and they always will be.&rdquo;</blockquote>
                <figcaption>
                    <div class="text-[15.5px] font-extrabold text-[#0C2E52]">Dr. Maria A. Pharr</div>
                    <div class="text-sm text-[#4A5A6A]">PCC President</div>
                </figcaption>
            </figure>
        </div>

        @include('demo.pcc.partials.stat-row', ['stats' => [
            ['value' => '7,400+', 'label' => 'students choosing PCC every year'],
            ['value' => '100+', 'label' => 'degree, diploma and certificate programs'],
            ['value' => '$76', 'label' => 'per credit hour for NC residents — a fraction of university cost'],
            ['value' => '1961', 'label' => 'educating and empowering people for success ever since'],
        ]])

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['title' => 'Visit our campus', 'desc' => 'Tours every Tuesday at 10 a.m. — sign up', 'stub' => 'Visit Campus'],
                ['title' => 'Campus map & directions', 'desc' => '2064 Warren Drive, Winterville, NC', 'stub' => 'Campus Map'],
                ['title' => 'History of PCC', 'desc' => 'From industrial education center to 7,400 students', 'stub' => 'History of PCC'],
                ['title' => 'Leadership & trustees', 'desc' => 'Board of Trustees, reports and strategic plan', 'stub' => 'Leadership & Reports'],
            ] as $card)
                <button type="button" @click="openStub('{{ $card['stub'] }}')"
                    class="pcc-hoverable flex cursor-pointer flex-col gap-1.5 rounded-[10px] border-[1.5px] border-[#E3EAF2] bg-white p-[22px] text-left hover:border-[#0C2E52] hover:shadow-[0_10px_24px_rgba(12,46,82,.1)]">
                    <span class="pcc-display text-lg font-extrabold text-[#0C2E52]">{{ $card['title'] }}</span>
                    <span class="text-[14.5px] leading-normal text-[#4A5A6A]">{{ $card['desc'] }} &rarr;</span>
                </button>
            @endforeach
        </div>
    </div>
@endsection
