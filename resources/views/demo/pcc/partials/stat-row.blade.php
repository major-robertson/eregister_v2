{{--
    "At a Glance" stat strip (Carteret pattern from the RFP).

    $stats — array of ['value', 'label']
--}}
<dl class="m-0 grid grid-cols-1 gap-[18px] sm:grid-cols-2 lg:grid-cols-4">
    @foreach ($stats as $stat)
        <div class="flex flex-col gap-1 border-t-4 border-[#FFB71B] px-1 pt-[18px]">
            <dt class="sr-only">{{ $stat['label'] }}</dt>
            <dd class="pcc-display m-0 text-[42px] font-black leading-none text-[#0C2E52]">{{ $stat['value'] }}</dd>
            <dd class="m-0 text-[15px] leading-snug text-[#4A5A6A]">{{ $stat['label'] }}</dd>
        </div>
    @endforeach
</dl>
