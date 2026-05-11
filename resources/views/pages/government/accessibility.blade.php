@extends('layouts.government')

@section('title', 'Government Website Accessibility | WCAG 2.2 AA & Section 508 Compliance')

@section('meta')
<meta name="description"
    content="Section 508, ADA, and WCAG 2.2 AA accessibility audits, remediation, and VPATs for government websites and digital services. Make your agency&rsquo;s site accessible to every resident.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Accessibility &amp; Section 508 compliance
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                WCAG 2.2 AA, Section 508, and ADA Title II conformance &mdash; from the initial audit through
                remediation and ongoing monitoring.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Request an audit
                </a>
                <a href="#scope"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    What we deliver
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Why it matters --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Why it matters</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Accessibility isn&rsquo;t optional &mdash; and it isn&rsquo;t hard
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    Federal agencies are bound by Section 508. State and local governments are bound by ADA Title II
                    and increasingly by state-level statutes. Most failures aren&rsquo;t intentional &mdash; they come
                    from a CMS that lets editors paste inaccessible content, or a design system that wasn&rsquo;t
                    built with assistive technology in mind.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    We fix both. We remediate the existing site, and we put guardrails in place so it stays
                    conformant after we leave.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">Standards we work to</h3>
                <div class="mt-6 space-y-4">
                    @foreach ([['name' => 'WCAG 2.2 Level AA', 'body' => 'The international web accessibility baseline cited by most procurement language.'], ['name' => 'Section 508 (ICT Refresh)', 'body' => 'The federal procurement standard for information and communication technology.'], ['name' => 'ADA Title II', 'body' => 'The 2024 final rule applying WCAG 2.1 AA to state and local government web content.'], ['name' => 'EN 301 549', 'body' => 'For agencies with EU partners or vendors that need a unified standard.']] as $standard)
                        <div>
                            <p class="font-semibold text-slate-900">{{ $standard['name'] }}</p>
                            <p class="text-sm text-slate-600">{{ $standard['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

{{-- What we deliver --}}
<section id="scope" class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Deliverables</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Audit. Remediate. Monitor.
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'Comprehensive accessibility audit', 'body' => 'Automated scans plus manual testing with screen readers, keyboard-only navigation, and zoom/contrast tools.'], ['title' => 'Prioritized remediation plan', 'body' => 'Findings ranked by severity, user impact, and effort &mdash; not a 600-page PDF dump.'], ['title' => 'Code-level remediation', 'body' => 'Our developers fix the issues directly in your codebase, with PRs your team can review.'], ['title' => 'Editor training', 'body' => 'Live sessions teaching content authors how to write accessible headings, alt text, links, and tables.'], ['title' => 'VPAT / ACR documentation', 'body' => 'Voluntary Product Accessibility Templates and Accessibility Conformance Reports for procurement.'], ['title' => 'Ongoing monitoring', 'body' => 'Automated regression scans on every deploy and quarterly manual re-tests so conformance doesn&rsquo;t drift.']] as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{!! $item['body'] !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'accessibility'])
@include('pages.government.partials.cta')
@endsection
