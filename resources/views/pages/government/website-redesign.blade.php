@extends('layouts.government')

@section('title', 'Website Redesign for Government Agencies')
@section('description', 'Full website redesigns for state, county, and city agencies. Mobile-first, accessible to WCAG 2.2 AA and Section 508, and delivered on a set timeline.')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'Website Redesign'],
            ]" />
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
            @foreach ([['stat' => 'Mobile-first', 'label' => 'Pages built to load fast on phones'], ['stat' => 'WCAG 2.2 AA', 'label' => 'Accessibility target'], ['stat' => 'Self-service', 'label' => 'Residents find answers without calling'], ['stat' => 'SLA', 'label' => 'Uptime commitment in writing']] as $stat)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
                    <p class="text-3xl font-bold text-blue-700">{{ $stat['stat'] }}</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Who it's for --}}
<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Who it&rsquo;s for</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Cities, counties, state departments and districts
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    A redesign fits a single department site or a central site that serves every department. The
                    buyer is usually the IT director, the communications lead or the clerk&rsquo;s office that owns
                    the website. If your site is hard to update, hard to read on a phone or failing accessibility
                    checks, this is where to start.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-8">
                <h3 class="text-lg font-semibold text-slate-900">What your agency provides</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['A project lead who can gather feedback and make decisions', 'Access to your current site, CMS and analytics', 'Brand standards, logos and any style guide', 'Department reviewers who know their content', 'Time for user acceptance testing and sign-off'] as $item)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-700" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4" />
                            </svg>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- How the work runs --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">The process</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How a redesign runs</h2>
        <ol class="mt-10 space-y-6">
            @foreach ([
                ['title' => 'Discovery and scoping', 'body' => 'We interview stakeholders, review analytics and inventory every page. You get a written scope with milestones and acceptance criteria.'],
                ['title' => 'Design and architecture', 'body' => 'We draft the new site map and page designs. Your team reviews them, and they pass an accessibility review before code is written.'],
                ['title' => 'Build and iterate', 'body' => 'We build in two-week sprints and demo working pages at the end of each one.'],
                ['title' => 'Test, train and launch', 'body' => 'Your team tests the site. We move the content, set redirects, train your editors and launch with a runbook.'],
                ['title' => 'Hypercare and transition', 'body' => 'We stay close after launch, then hand off to your maintenance contract or in-house team.'],
            ] as $i => $step)
                <li class="flex gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700 ring-1 ring-inset ring-blue-100">{{ $i + 1 }}</span>
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-1 text-slate-600">{{ $step['body'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
        <p class="mt-8 text-slate-600">
            Each phase ends with a deliverable your team signs off on. The full plan is on our
            <a href="{{ route('government.implementation') }}" class="font-medium text-blue-700 underline hover:text-blue-800">implementation page</a>.
        </p>
    </div>
</section>

{{-- How it fits --}}
<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Related work</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How it fits with our other services</h2>
        <ul class="mt-8 space-y-4 text-slate-600">
            <li>An <a href="{{ route('government.accessibility') }}" class="font-medium text-blue-700 underline hover:text-blue-800">accessibility audit</a> of your current site tells the redesign what to fix.</li>
            <li>Most redesigns include a new <a href="{{ route('government.cms') }}" class="font-medium text-blue-700 underline hover:text-blue-800">content management system</a> your editors can use without help.</li>
            <li>Our <a href="{{ route('government.hosting') }}" class="font-medium text-blue-700 underline hover:text-blue-800">managed hosting</a> can run the new site, with monitoring and backups.</li>
            <li>A <a href="{{ route('government.maintenance') }}" class="font-medium text-blue-700 underline hover:text-blue-800">maintenance contract</a> keeps it patched and accessible after launch.</li>
        </ul>
        <p class="mt-8 text-slate-600">
            Contracting officers can see every service on our
            <a href="{{ route('government.capabilities') }}" class="font-medium text-blue-700 underline hover:text-blue-800">capabilities statement</a>.
            To get a proposal, tell us about your site on our
            <a href="{{ route('contact') }}" class="font-medium text-blue-700 underline hover:text-blue-800">contact page</a>.
        </p>
    </div>
</section>

{{-- FAQ --}}
@php
    $faqs = [
        ['q' => 'Can you respond to our RFP or work through a cooperative contract?', 'a' => 'Yes. We respond to your solicitation, or help you write one. Our fixed-fee statements of work tie pricing to deliverables, not hours.'],
        ['q' => 'How long does a redesign take?', 'a' => 'It depends on the size of the site and how much content moves. Your written scope sets a date for each phase before the build starts.'],
        ['q' => 'Who owns the code and content when the project ends?', 'a' => 'Your agency owns its content. The statement of work sets ownership of the code, designs and documentation before work starts, so handoff holds no surprises.'],
        ['q' => 'Will the new site meet accessibility requirements?', 'a' => 'We design and build to WCAG 2.2 AA and Section 508. Designs are reviewed for accessibility before code is written, and editor training covers keeping new content accessible.'],
        ['q' => 'What support do we get after launch?', 'a' => 'A hypercare period follows go-live. After that, you can move to a maintenance contract with us or take it in-house with our runbooks.'],
    ];
@endphp
<section class="bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">FAQ</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Questions about a redesign</h2>
        <x-seo.faq class="mt-10" card="zinc" :items="$faqs" />
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'website-redesign'])
@include('pages.government.partials.cta')
@endsection
