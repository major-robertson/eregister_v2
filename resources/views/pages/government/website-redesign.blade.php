@extends('layouts.government')

@section('title', 'Government Website Redesign Services | Modern Agency Websites')

@section('meta')
<meta name="description"
    content="Full website redesigns for federal, state, county, and city government agencies. Modern, mobile-first, accessible (WCAG 2.2 AA / Section 508) websites delivered on a predictable timeline.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Government website redesign
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                Replace your aging agency website with a modern, mobile-first experience that residents can actually
                use &mdash; on a fixed timeline and a fixed budget.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Request a redesign proposal
                </a>
                <a href="#scope"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    What&rsquo;s included
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Problem --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">The Challenge</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Your residents deserve better than a 2008 website
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    Most agency sites were built years ago, on platforms that are no longer supported, with content
                    structures that no one on staff fully understands. They&rsquo;re slow on phones, fail accessibility
                    audits, and bury the answers residents are actually looking for.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    A modern redesign isn&rsquo;t just a new coat of paint. It&rsquo;s a rethink of the information
                    architecture, the content, the accessibility posture, and the publishing tools your team uses every
                    day.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">Common pain points we solve</h3>
                <ul class="mt-6 space-y-4">
                    @foreach (['Site fails WCAG 2.2 / Section 508 audits', 'Mobile experience is unusable', 'Editors avoid the CMS because it&rsquo;s painful', 'Search results return junk &mdash; or nothing', 'Page load times exceed five seconds', 'Inconsistent branding across departments'] as $pain)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-700" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-slate-700">{!! $pain !!}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Scope --}}
<section id="scope" class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">What&rsquo;s included</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                A complete redesign engagement
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $included = [
                    ['title' => 'Discovery & content audit', 'body' => 'Stakeholder interviews, content inventory, analytics review, and a residents-first information architecture.'],
                    ['title' => 'Brand-aligned visual design', 'body' => 'High-fidelity designs that match agency branding standards and pass an accessibility review before any code is written.'],
                    ['title' => 'Accessible component library', 'body' => 'Reusable, WCAG 2.2 AA components your editors can recombine without breaking the design system.'],
                    ['title' => 'Mobile-first build', 'body' => 'Built for phones first, then tablet and desktop. Works on every device residents actually own.'],
                    ['title' => 'Content migration', 'body' => 'We migrate existing content into the new structure, with redirects so old links keep working.'],
                    ['title' => 'Editor training & launch', 'body' => 'Live training sessions and written runbooks so your team can publish confidently from day one.'],
                ];
            @endphp

            @foreach ($included as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Outcomes --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Outcomes</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                What success looks like
            </h2>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['stat' => '< 2s', 'label' => 'Page load on 4G mobile'], ['stat' => '100%', 'label' => 'WCAG 2.2 AA conformance'], ['stat' => '50%+', 'label' => 'Reduction in support calls'], ['stat' => '99.9%', 'label' => 'Uptime under SLA']] as $stat)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
                    <p class="text-3xl font-bold text-blue-700">{{ $stat['stat'] }}</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'website-redesign'])
@include('pages.government.partials.cta')
@endsection
