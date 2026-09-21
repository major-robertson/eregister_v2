{{--
    One line of proof for a hero: the Google rating and how long the company
    has been in business. Numbers come from config/company.php. Renders
    nothing if the rating is missing.
--}}
@props(['dark' => false])

@php
    $google = config('company.google_reviews');
    $since = config('company.in_business_since');
@endphp

@if (! empty($google['rating']) && ! empty($google['count']))
<p {{ $attributes->class(['flex flex-wrap items-center gap-x-2 gap-y-1 text-sm', $dark ? 'text-zinc-300' : 'text-zinc-600']) }}>
    <span class="flex items-center gap-0.5 text-amber-400" aria-hidden="true">
        @for ($i = 0; $i < (int) round($google['rating']); $i++)
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.29 3.97a1 1 0 00.95.69h4.17c.97 0 1.37 1.24.59 1.81l-3.38 2.45a1 1 0 00-.36 1.12l1.29 3.97c.3.92-.76 1.69-1.54 1.12l-3.37-2.45a1 1 0 00-1.18 0l-3.37 2.45c-.78.57-1.84-.2-1.54-1.12l1.29-3.97a1 1 0 00-.36-1.12L2.05 9.4c-.78-.57-.38-1.81.59-1.81h4.17a1 1 0 00.95-.69l1.29-3.97z"/></svg>
        @endfor
    </span>
    <span><span class="font-semibold {{ $dark ? 'text-white' : 'text-zinc-900' }}">{{ number_format($google['rating'], 1) }}</span> on Google ({{ $google['count'] }} reviews)</span>
    @if ($since)
        {{-- A divider only where both fit on one line; on phones it wraps clean. --}}
        <span class="sm:border-l sm:pl-2 {{ $dark ? 'sm:border-zinc-600' : 'sm:border-zinc-300' }}">In business since {{ $since }}</span>
    @endif
</p>
@endif
