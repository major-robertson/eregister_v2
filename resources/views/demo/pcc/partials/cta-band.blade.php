{{--
    "Take the Next Step" CTA group (Centre College pattern from the RFP).

    $heading  — headline
    $sub      — supporting sentence
    $ctas     — array of ['label', 'style' => 'gold'|'outline'|'outline-dim', and 'href' OR 'stub']
    $variant  — 'band' (full-bleed, centered — homepage) or 'card' (rounded, split — interior pages)
--}}
@php
    $ctaClasses = fn (string $style): string => match ($style) {
        'outline' => 'pcc-hoverable cursor-pointer rounded border-2 border-white px-7 py-3.5 text-[17px] font-bold text-white no-underline hover:bg-white/10',
        'outline-dim' => 'pcc-hoverable cursor-pointer rounded border-2 border-white/45 px-7 py-3.5 text-[17px] font-bold text-white no-underline hover:border-white',
        default => 'pcc-hoverable cursor-pointer rounded bg-[#FFB71B] px-[30px] py-[15px] text-[17px] font-extrabold text-[#0C2E52] no-underline hover:bg-[#E8A404]',
    };
@endphp

@if (($variant ?? 'band') === 'card')
    <div class="pcc-on-dark flex flex-wrap items-center justify-between gap-5 rounded-xl bg-[linear-gradient(120deg,#123E6B,#0C2E52)] p-9">
        <div>
            <div class="pcc-display text-[26px] font-black text-white">{{ $heading }}</div>
            <div class="mt-1 text-[15.5px] text-[#C9DAEC]">{{ $sub }}</div>
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach ($ctas as $cta)
                @isset ($cta['href'])
                    <a href="{{ $cta['href'] }}" class="{{ $ctaClasses($cta['style'] ?? 'gold') }}">{{ $cta['label'] }}</a>
                @else
                    <button type="button" @click="openStub('{{ $cta['stub'] }}')" class="{{ $ctaClasses($cta['style'] ?? 'gold') }}">{{ $cta['label'] }}</button>
                @endisset
            @endforeach
        </div>
    </div>
@else
    <div class="pcc-on-dark bg-[linear-gradient(120deg,#123E6B,#0C2E52)] px-4 py-16 md:px-8">
        <div class="mx-auto flex max-w-[1100px] flex-col items-center gap-5 text-center">
            <h2 class="pcc-display m-0 text-[clamp(28px,3.4vw,42px)] font-black text-white">{{ $heading }}</h2>
            <p class="m-0 max-w-[560px] text-[17px] text-[#C9DAEC]">{{ $sub }}</p>
            <div class="flex flex-wrap justify-center gap-3.5">
                @foreach ($ctas as $cta)
                    @isset ($cta['href'])
                        <a href="{{ $cta['href'] }}" class="{{ $ctaClasses($cta['style'] ?? 'gold') }}">{{ $cta['label'] }}</a>
                    @else
                        <button type="button" @click="openStub('{{ $cta['stub'] }}')" class="{{ $ctaClasses($cta['style'] ?? 'gold') }}">{{ $cta['label'] }}</button>
                    @endisset
                @endforeach
            </div>
        </div>
    </div>
@endif
