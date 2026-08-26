{{--
    Navy page hero shared by the interior pages.

    $eyebrow  — small gold uppercase label
    $heading  — h1 text
    $intro    — optional lead paragraph
    $ctas     — optional array of ['label', 'style' => 'gold'|'outline', and 'href' OR 'stub']
--}}
<div class="pcc-on-dark bg-[#0C2E52] px-4 py-[52px] md:px-8">
    <div class="mx-auto flex max-w-[1100px] flex-col gap-3.5">
        <span class="text-[13px] font-bold uppercase tracking-[.12em] text-[#FFB71B]">{{ $eyebrow }}</span>
        <h1 class="pcc-display m-0 text-[clamp(30px,3.8vw,46px)] font-black leading-[1.08] text-white">{{ $heading }}</h1>
        @isset ($intro)
            <p class="m-0 max-w-[640px] text-[17px] leading-normal text-[#C9DAEC]">{{ $intro }}</p>
        @endisset
        @if (! empty($ctas))
            <div class="flex flex-wrap gap-3">
                @foreach ($ctas as $cta)
                    @php
                        $classes = ($cta['style'] ?? 'gold') === 'gold'
                            ? 'pcc-hoverable cursor-pointer rounded bg-[#FFB71B] px-6 py-[13px] font-extrabold text-[#0C2E52] no-underline hover:bg-[#E8A404]'
                            : 'pcc-hoverable cursor-pointer rounded border-2 border-white px-[22px] py-3 font-bold text-white no-underline hover:bg-white/10';
                    @endphp
                    @isset ($cta['href'])
                        <a href="{{ $cta['href'] }}" class="{{ $classes }}">{{ $cta['label'] }}</a>
                    @else
                        <button type="button" @click="openStub('{{ $cta['stub'] }}')" class="{{ $classes }}">{{ $cta['label'] }}</button>
                    @endisset
                @endforeach
            </div>
        @endif
    </div>
</div>
