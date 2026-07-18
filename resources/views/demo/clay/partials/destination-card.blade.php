@php
    /** @var array $dest destination record from resources/demo/clay-county/destinations.json */
    $tagStyles = [
        'lake' => 'text-[#0E5A73] bg-[#E3EEF2]',
        'park' => 'text-[#35663C] bg-[#E5F0E4]',
        'beach' => 'text-[#8A5A20] bg-[#FAF5EC]',
        'nature' => 'text-[#35663C] bg-[#E5F0E4]',
        'historic' => 'text-[#93402A] bg-[#F5E6DF]',
    ];
    $href = $dest['url'] ? url($dest['url']) : route('clay-demo.explore').'#dest-'.$dest['slug'];
    $hoverBorder = $dest['historic'] ? 'hover:border-[#93402A]' : 'hover:border-[#B98A54]';
    $cta = $dest['cta'] ?? ($dest['historic'] ? 'Step into history' : 'See more');
@endphp

<a href="{{ $href }}"
    class="cc-hoverable {{ $hoverBorder }} flex flex-col overflow-hidden rounded-xl border border-[#E0D9CB] bg-white no-underline shadow-[0_1px_3px_rgba(20,30,35,.06)] hover:shadow-[0_8px_24px_rgba(20,30,35,.12)]">
    <span class="relative block aspect-[3/2] overflow-hidden bg-[#E3EEF2]">
        @if ($dest['image'])
            <img src="{{ asset('img/demos/clay-county/'.$dest['image']) }}" alt="{{ $dest['imageAlt'] }}"
                class="absolute inset-0 h-full w-full object-cover" loading="lazy">
        @else
            <span class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-[#F2ECDF] text-[#8A9199]">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="m3 16 5-5 4 4 3-3 6 6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="9" cy="9.5" r="1.4" stroke="currentColor" stroke-width="1.4"/></svg>
                <span class="text-xs font-bold tracking-[.05em] uppercase">Photo pending</span>
            </span>
        @endif
    </span>
    <span class="flex flex-1 flex-col gap-2 p-5">
        <span>
            <span class="{{ $tagStyles[$dest['type']] ?? $tagStyles['park'] }} rounded px-2 py-0.5 text-[11px] font-bold tracking-[.06em] uppercase">{{ $dest['typeLabel'] }}</span>
        </span>
        <span class="{{ $dest['historic'] ? 'cc-serif' : '' }} text-xl font-extrabold text-[#0B3A4E]">{{ $dest['name'] }}</span>
        <span class="text-[14.5px] leading-normal text-[#5A646C]">{{ $dest['summary'] }}</span>
        <span class="{{ $dest['historic'] ? 'text-[#93402A]' : 'text-[#0E5A73]' }} mt-auto pt-1 text-sm font-bold">{{ $cta }} →</span>
    </span>
</a>
