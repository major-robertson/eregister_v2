{{--
    Numbered step rows (admissions pathways, financial aid steps).

    $steps — array of ['title', 'desc']
--}}
<ol class="m-0 flex list-none flex-col p-0">
    @foreach ($steps as $step)
        <li class="flex gap-[22px] border-b border-[#E3EAF2] px-1 py-[22px]">
            <span class="pcc-display inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-[#0C2E52] text-[17px] font-black text-[#FFB71B]" aria-hidden="true">{{ $loop->iteration }}</span>
            <div class="flex flex-col gap-[5px]">
                <span class="pcc-display text-[19px] font-extrabold text-[#0C2E52]">{{ $step['title'] }}</span>
                <span class="max-w-[720px] text-[15.5px] leading-normal text-[#33424F]">{{ $step['desc'] }}</span>
            </div>
        </li>
    @endforeach
</ol>
