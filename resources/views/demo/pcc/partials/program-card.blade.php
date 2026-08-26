{{--
    Program card (Lenoir-style concise program summary from the RFP).

    $program — merged program record
    $eyebrow — small label above the name (division on the finder, length on workforce)
    $show    — optional Alpine expression; when set the finder shows/hides the card client-side
--}}
<a href="{{ route('pcc-demo.program', $program['slug']) }}"
    @isset ($show) x-show="{{ $show }}" @endisset
    class="pcc-hoverable flex flex-col gap-2.5 rounded-[10px] border-[1.5px] border-[#E3EAF2] bg-white p-[22px] no-underline hover:-translate-y-[3px] hover:border-[#0C2E52] hover:shadow-[0_10px_24px_rgba(12,46,82,.1)]">
    <span class="text-xs font-bold uppercase tracking-[.1em] text-[#5E7288]">{{ $eyebrow }}</span>
    <span class="pcc-display text-xl font-extrabold leading-tight text-[#0C2E52]">{{ $program['name'] }}</span>
    <span class="text-[14.5px] leading-normal text-[#4A5A6A]">{{ $program['blurb'] }}</span>
    <span class="mt-auto flex flex-wrap gap-2 pt-1.5">
        <span class="rounded bg-[#EDF3FA] px-2.5 py-1 text-[12.5px] font-bold text-[#0C2E52]">{{ $program['award'] }}</span>
        <span class="rounded bg-[#FFF4DC] px-2.5 py-1 text-[12.5px] font-bold text-[#7A5A00]">{{ $program['format'] }}</span>
    </span>
    <span class="text-[14.5px] font-bold text-[#0C5DA5]">View program &rarr;</span>
</a>
