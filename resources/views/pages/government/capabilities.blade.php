@extends('layouts.government')

@section('title', 'Capabilities Statement for Government Agencies')
@section('description', 'eRegister capabilities for government buyers: website redesign, Section 508 accessibility, CMS, hosting, maintenance, portals, integrations and implementation.')

@php
    // Published without UEI, CAGE, NAICS or any other identifier, and without
    // client names, insurance or certifications (the owner's call, 2026-10-03).
    // Say "U.S.-based" only; no city or state.
    $since = config('company.in_business_since');
    $capabilities = [
        ['route' => 'government.website-redesign', 'title' => 'Website redesign', 'body' => 'Mobile-first redesigns of legacy agency websites, built to WCAG 2.2 AA and Section 508.'],
        ['route' => 'government.accessibility', 'title' => 'Accessibility and Section 508', 'body' => 'Section 508, ADA and WCAG 2.2 AA audits, remediation and VPATs.'],
        ['route' => 'government.cms', 'title' => 'Content management (CMS)', 'body' => 'Editor-friendly content management with role-based publishing and multi-department workflows.'],
        ['route' => 'government.hosting', 'title' => 'Hosting and infrastructure', 'body' => 'Hardened, U.S.-based hosting with monitoring, automated backups and security patching.'],
        ['route' => 'government.maintenance', 'title' => 'Maintenance and support', 'body' => 'Security patching, content updates, accessibility monitoring and incident response on contract.'],
        ['route' => 'government.portals', 'title' => 'Citizen and staff portals', 'body' => 'Self-service portals for permits, licensing, payments and requests, and internal staff portals.'],
        ['route' => 'government.integrations', 'title' => 'System integrations', 'body' => 'Connect legacy systems, GIS, payment processors and permit systems through documented APIs.'],
        ['route' => 'government.implementation', 'title' => 'Implementation services', 'body' => 'Discovery, design, build, training and go-live support on a fixed-fee contract.'],
    ];

    // Wording from the implementation page.
    $phases = [
        ['title' => 'Discovery and scoping', 'body' => 'Stakeholder interviews, a current-state assessment, and a written scope with timeline, milestones and acceptance criteria.'],
        ['title' => 'Design and architecture', 'body' => 'Information architecture, visual design, technical architecture and an accessible component library reviewed by your team.'],
        ['title' => 'Build and iterate', 'body' => 'Iterative development in two-week sprints, with demos and stakeholder reviews at the end of each sprint.'],
        ['title' => 'Test, train and launch', 'body' => 'User acceptance testing with your team, content migration, editor training, and a coordinated go-live with a runbook.'],
        ['title' => 'Hypercare and transition', 'body' => 'Elevated support right after launch, then a handoff to your maintenance contract or in-house team.'],
    ];

    // Sales demos in this repo. Each is public by direct URL and noindexed.
    $concepts = [
        ['title' => 'County parks and historic sites website', 'body' => 'A concept for the Clay County, Missouri parks, recreation and historic sites website: destinations, trails, historic sites, events and visit planning.', 'url' => route('clay-demo.home')],
        ['title' => 'School district website and CMS', 'body' => 'A concept for an MDCPS school website, with a public calendar and a demo editor area for calendar entries, alerts and media.', 'url' => route('mdcps-demo.home')],
        ['title' => 'State public information website', 'body' => 'Two design options for a Florida EOG public information website.', 'url' => route('government.florida-eog-demo-1')],
    ];
@endphp

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'Capabilities statement'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">For contracting officers</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl">
                Capabilities statement
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300">
                What eRegister builds and runs for state and local government, and how we deliver it, on one page.
            </p>
        </div>
    </div>
</section>

{{-- Core capabilities --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Core capabilities</h2>
        <ul class="mt-8 divide-y divide-slate-200 border-y border-slate-200">
            @foreach ($capabilities as $capability)
                <li class="py-4 sm:flex sm:gap-6">
                    <a href="{{ route($capability['route']) }}" class="shrink-0 font-semibold text-blue-700 hover:text-blue-800 hover:underline sm:w-64">{{ $capability['title'] }}</a>
                    <p class="mt-1 text-slate-600 sm:mt-0">{{ $capability['body'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- How we work --}}
<section class="bg-slate-50 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">How we work</h2>
        <p class="mt-4 text-lg text-slate-600">
            Procurement-friendly implementation on a fixed-fee SOW. Pricing is tied to deliverables, not hours. We
            respond to your existing solicitation, or help you write one. Every project runs from discovery to
            go-live in the same phases.
        </p>
        <ol class="mt-8 space-y-4">
            @foreach ($phases as $phase)
                <li class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 class="font-semibold text-slate-900">{{ $phase['title'] }}</h3>
                    <p class="mt-1 text-slate-600">{{ $phase['body'] }}</p>
                </li>
            @endforeach
        </ol>
        <p class="mt-6 text-slate-600">
            The full phase plan is on our <a href="{{ route('government.implementation') }}" class="font-medium text-blue-700 underline hover:text-blue-800">implementation page</a>.
        </p>
    </div>
</section>

{{-- Concept builds --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Concept builds</h2>
        <p class="mt-4 text-lg text-slate-600">
            Working concepts we built to show an agency what its site could look like. None of them is a client
            engagement.
        </p>
        <div class="mt-8 grid gap-6 md:grid-cols-3">
            @foreach ($concepts as $concept)
                <div class="flex flex-col rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Concept build, not a client engagement</p>
                    <h3 class="mt-2 font-semibold text-slate-900">{{ $concept['title'] }}</h3>
                    <p class="mt-2 flex-1 text-sm text-slate-600">{{ $concept['body'] }}</p>
                    <a href="{{ $concept['url'] }}" class="mt-4 text-sm font-semibold text-blue-700 hover:text-blue-800 hover:underline">View the concept</a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Company --}}
<section class="bg-slate-50 py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Company</h2>
        <p class="mt-4 text-lg text-slate-600">
            eRegister is a private, U.S.-based company{{ $since ? ", in business since {$since}" : '' }}. We are a
            commercial vendor, not a government agency.
        </p>
    </div>
</section>

{{-- Contact --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Contact</h2>
        <p class="mt-4 text-lg text-slate-600">
            Tell us about your project through our <a href="{{ route('contact') }}" class="font-medium text-blue-700 underline hover:text-blue-800">contact page</a>.
            We reply by email.
        </p>
    </div>
</section>
@endsection
