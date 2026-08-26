@extends('demo.pcc.layout', [
    'title' => $program['name'].' — Pitt Community College (Concept Demo)',
    'metaDescription' => $program['blurb'].' Program details, cost, deadlines, support and next steps. Concept demo for RFP 115-6181.',
])

@section('content')
    {{-- Program hero --}}
    <div class="pcc-on-dark bg-[#0C2E52] px-4 py-12 md:px-8">
        <div class="mx-auto flex max-w-[1100px] flex-col gap-3.5">
            <a href="{{ route('pcc-demo.programs') }}" class="text-sm text-[#C9DAEC] no-underline hover:text-white">&larr; All programs</a>
            <span class="text-[13px] font-bold uppercase tracking-[.12em] text-[#FFB71B]">{{ $program['division'] }}</span>
            <h1 class="pcc-display m-0 text-[clamp(30px,3.8vw,48px)] font-black leading-[1.08] text-white">{{ $program['name'] }}</h1>
            <div class="flex flex-wrap gap-2.5">
                <span class="rounded bg-white/[.12] px-3 py-[5px] text-[13.5px] font-bold text-white">{{ $program['award'] }}</span>
                <span class="rounded bg-white/[.12] px-3 py-[5px] text-[13.5px] font-bold text-white">{{ $program['format'] }}</span>
                <span class="rounded bg-white/[.12] px-3 py-[5px] text-[13.5px] font-bold text-white">{{ $program['length'] }}</span>
            </div>
        </div>
    </div>

    {{-- The RFP's program-page question sequence: what is it → am I eligible →
         cost → deadlines → support → what next → who can I contact --}}
    <div class="mx-auto grid max-w-[1100px] grid-cols-1 items-start gap-11 px-4 pb-[72px] pt-11 md:px-8 lg:grid-cols-[1fr_minmax(260px,320px)]">
        <div class="flex min-w-0 flex-col gap-9">
            <section>
                <h2 class="pcc-display m-0 mb-2.5 text-2xl font-extrabold text-[#0C2E52]">What is it?</h2>
                <p class="m-0 text-[16.5px] leading-relaxed text-[#33424F]">{{ $program['what'] }}</p>
            </section>

            <section>
                <h2 class="pcc-display m-0 mb-2.5 text-2xl font-extrabold text-[#0C2E52]">Am I eligible?</h2>
                <ul class="m-0 flex list-none flex-col gap-2 p-0">
                    @foreach ($program['eligible'] as $item)
                        <li class="flex items-baseline gap-2.5">
                            <span class="font-black text-[#E8A404]" aria-hidden="true">&check;</span>
                            <span class="text-base leading-normal text-[#33424F]">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <section class="rounded-[10px] bg-[#EDF3FA] p-[22px]">
                    <h2 class="pcc-display m-0 mb-2 text-lg font-extrabold text-[#0C2E52]">What does it cost?</h2>
                    <div class="pcc-display text-[30px] font-black text-[#0C2E52]">{{ $program['cost'] }}</div>
                    <p class="m-0 mt-2 text-sm leading-normal text-[#4A5A6A]">{{ $program['costNote'] }} <a href="{{ route('pcc-demo.paying') }}" class="font-semibold text-[#0C5DA5] hover:text-[#083E70]">Financial aid options &rarr;</a></p>
                </section>
                <section class="rounded-[10px] bg-[#EDF3FA] p-[22px]">
                    <h2 class="pcc-display m-0 mb-2 text-lg font-extrabold text-[#0C2E52]">Key deadlines</h2>
                    <dl class="m-0 flex flex-col gap-1.5">
                        @foreach ($program['deadlines'] as $deadline)
                            <div class="flex justify-between gap-3 text-[14.5px]">
                                <dt class="text-[#33424F]">{{ $deadline['label'] }}</dt>
                                <dd class="m-0 whitespace-nowrap font-extrabold text-[#0C2E52]">{{ $deadline['date'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            </div>

            <section>
                <h2 class="pcc-display m-0 mb-2.5 text-2xl font-extrabold text-[#0C2E52]">What support is available?</h2>
                <ul class="m-0 flex list-none flex-col gap-2 p-0">
                    @foreach ($program['support'] as $item)
                        <li class="flex items-baseline gap-2.5">
                            <span class="font-black text-[#E8A404]" aria-hidden="true">&check;</span>
                            <span class="text-base leading-normal text-[#33424F]">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- Sticky next-steps rail --}}
        <aside class="top-[100px] flex min-w-0 flex-col gap-3.5 rounded-xl border-[1.5px] border-[#E3EAF2] bg-white p-[26px] lg:sticky">
            <h2 class="pcc-display m-0 text-[19px] font-extrabold text-[#0C2E52]">What do I do next?</h2>
            <ol class="m-0 flex list-none flex-col gap-3 p-0">
                @foreach ($program['next'] as $step)
                    <li class="flex items-baseline gap-3">
                        <span class="pcc-display inline-flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-[#0C2E52] text-[13px] font-extrabold text-[#FFB71B]" aria-hidden="true">{{ $loop->iteration }}</span>
                        <span class="text-[15px] leading-normal text-[#33424F]">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
            <button type="button" @click="openStub('Apply to PCC')" class="pcc-hoverable cursor-pointer rounded bg-[#FFB71B] p-[13px] text-center text-base font-extrabold text-[#0C2E52] hover:bg-[#E8A404]">Apply Now — it's free</button>
            <button type="button" @click="openStub('Request Information')" class="pcc-hoverable cursor-pointer rounded border-[1.5px] border-[#0C2E52] p-3 text-center text-[15px] font-bold text-[#0C2E52] hover:bg-[#EDF3FA]">Request Information</button>
            <div class="flex flex-col gap-0.5 border-t border-[#E3EAF2] pt-3.5">
                <span class="text-[14.5px] font-extrabold text-[#0C2E52]">Who can I contact?</span>
                <span class="text-[14.5px] text-[#4A5A6A]">{{ $program['contact'] }}</span>
                <a href="tel:2524937200" class="text-[14.5px] text-[#0C5DA5] hover:text-[#083E70]">252-493-7200</a>
                <button type="button" @click="openStub('Contact PCC')" class="cursor-pointer self-start text-[14.5px] text-[#0C5DA5] hover:text-[#083E70] hover:underline">Send a message &rarr;</button>
            </div>
        </aside>
    </div>
@endsection
