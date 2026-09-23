@props([
    'items',                 // [['q' => 'Question?', 'a' => 'Answer text'], ...]
    'card' => 'white',       // 'white' (on a zinc-50 section) or 'zinc' (on a white section)
    'icon' => 'plus',        // 'plus' (rotates 45deg) or 'chevron' (rotates 180deg)
    'html' => false,         // true to render answers as trusted HTML instead of text
])
{{-- FAQ accordion and its FAQPage JSON-LD, both rendered from $items so the
     structured data can never drift from what the visitor reads. Text is
     entity-decoded then escaped once, so items may carry &quot; (needed inside
     the :items attribute) and data pulled from the rule tables stays inert. --}}
@php
    $text = fn (string $value) => $html ? $value : e(html_entity_decode($value, ENT_QUOTES));
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($item) => [
            '@type' => 'Question',
            'name' => trim(html_entity_decode(strip_tags($item['q']))),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($item['a'])))),
            ],
        ], array_values($items)),
    ];

    $cardClass = $card === 'zinc'
        ? 'group rounded-xl border border-zinc-200 bg-zinc-50'
        : 'group rounded-2xl border border-zinc-200 bg-white shadow-sm';
    $summaryClass = $card === 'zinc'
        ? 'flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden'
        : 'flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden';
    $answerClass = $card === 'zinc'
        ? 'border-t border-zinc-100 px-5 py-4 text-zinc-600'
        : 'px-6 pb-5 text-zinc-600';
@endphp
@push('schema')
<x-seo.json-ld :data="$faqSchema" />
@endpush
<div {{ $attributes->merge(['class' => $card === 'zinc' ? 'space-y-3' : 'space-y-4']) }}>
    @foreach ($items as $item)
    <details class="{{ $cardClass }}">
        <summary class="{{ $summaryClass }}">
            <span>{!! $text($item['q']) !!}</span>
            @if ($icon === 'chevron')
            <svg class="ml-4 h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            @else
            <svg class="ml-4 h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            @endif
        </summary>
        <div class="{{ $answerClass }}">{!! $text($item['a']) !!}</div>
    </details>
    @endforeach
</div>
