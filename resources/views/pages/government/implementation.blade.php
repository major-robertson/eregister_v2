@extends('layouts.government')

@section('title', 'Implementation Services for Government Agencies')
@section('description', 'Procurement-friendly implementation for government digital projects: discovery, design, build, training, and go-live support on a fixed-fee contract.')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'Implementation'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Implementation services
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                A complete delivery team &mdash; project management, design, engineering, QA, and training &mdash; on
                a fixed-fee SOW that procurement can actually approve.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Request an SOW
                </a>
                <a href="#phases"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    See phases
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Phases --}}
<section id="phases" class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Engagement Phases</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Predictable phases. Clear deliverables.
            </h2>
        </div>

        <div class="mt-14 space-y-6">
            @foreach ([
                ['number' => '01', 'title' => 'Discovery & scoping', 'duration' => '2&ndash;4 weeks', 'body' => 'Stakeholder interviews, current-state assessment, and a written scope document with timeline, milestones, and acceptance criteria.', 'deliverables' => ['Stakeholder interview report', 'Current-state assessment', 'Written scope of work', 'Risk register']],
                ['number' => '02', 'title' => 'Design & architecture', 'duration' => '4&ndash;8 weeks', 'body' => 'Information architecture, visual design, technical architecture, and an accessible component library reviewed by your team.', 'deliverables' => ['Information architecture', 'High-fidelity designs', 'Component library', 'Technical architecture document']],
                ['number' => '03', 'title' => 'Build & iterate', 'duration' => '8&ndash;20 weeks', 'body' => 'Iterative development in two-week sprints, with demos and stakeholder reviews at the end of each sprint.', 'deliverables' => ['Working software in staging', 'Sprint demos', 'Accessibility test reports', 'Security review']],
                ['number' => '04', 'title' => 'Test, train & launch', 'duration' => '2&ndash;4 weeks', 'body' => 'User acceptance testing with your team, content migration, editor training, and a coordinated go-live with a runbook.', 'deliverables' => ['UAT sign-off', 'Migrated content', 'Editor training sessions', 'Launch runbook', 'Production go-live']],
                ['number' => '05', 'title' => 'Hypercare & transition', 'duration' => '4&ndash;8 weeks', 'body' => 'Elevated support immediately after launch, then a smooth handoff to your maintenance contract or in-house team.', 'deliverables' => ['Daily standups', 'Issue triage &amp; resolution', 'Final documentation', 'Maintenance handoff']],
            ] as $phase)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 sm:p-8">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <div>
                            <p class="text-3xl font-bold text-blue-700">{{ $phase['number'] }}</p>
                            <h3 class="mt-1 text-xl font-bold text-slate-900">{{ $phase['title'] }}</h3>
                            <p class="mt-2 text-sm text-slate-500">Typical: {!! $phase['duration'] !!}</p>
                        </div>
                        <div class="lg:col-span-2">
                            <p class="text-slate-700">{!! $phase['body'] !!}</p>
                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                @foreach ($phase['deliverables'] as $deliverable)
                                    <div class="flex items-start gap-2 text-sm text-slate-600">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>{!! $deliverable !!}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Procurement --}}
<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Procurement</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                We make it easy for your contracting officer
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'RFP &amp; RFQ response', 'body' => 'We respond to your existing solicitation, or help you write one that gets the right vendors.'], ['title' => 'Fixed-fee SOWs', 'body' => 'Predictable pricing tied to deliverables, not hours. No mid-project surprise invoices.'], ['title' => 'Standard contract vehicles', 'body' => 'Compatible with cooperative purchasing agreements and standard professional-services contracts.'], ['title' => 'Insurance &amp; bonding', 'body' => 'General liability, professional liability, and cyber liability coverage at standard agency thresholds.'], ['title' => 'Net-30 invoicing', 'body' => 'Standard government payment terms. We don&rsquo;t penalize for normal accounts-payable cycles.'], ['title' => 'Transparent reporting', 'body' => 'Monthly status reports, financial summaries, and risk updates that your director can hand to council.']] as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">{!! $item['title'] !!}</h3>
                    <p class="mt-2 text-sm text-slate-600">{!! $item['body'] !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Who it's for --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Who it&rsquo;s for</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    One team from kickoff to go-live
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    This fits any agency buying a defined project: a website redesign, a CMS move, a new portal or a
                    set of integrations. It works best when your team is small and cannot spare people to manage
                    several vendors. You get one point of contact and one contract.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    The problems it prevents are common. A vendor disappears after launch. Change orders keep growing
                    the budget. A project stalls because no one owns the schedule.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">What your agency provides</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['An executive sponsor and a day-to-day project lead', 'Decisions and sign-off at the end of each phase', 'Subject experts for interviews and reviews', 'Staff time for acceptance testing and training', 'Access to systems, content and existing documentation'] as $item)
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

{{-- Delivery team --}}
<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">The team</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Who is on the delivery team
            </h2>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['title' => 'Project management', 'body' => 'Runs the schedule, the risk register and the monthly status report. Your single point of contact.'],
                ['title' => 'Design', 'body' => 'Owns the information architecture, the visual design and the accessible component library.'],
                ['title' => 'Engineering', 'body' => 'Builds the site, portal or integrations, and handles security and performance.'],
                ['title' => 'Quality assurance', 'body' => 'Tests each sprint for function and accessibility, and runs acceptance testing with your team.'],
                ['title' => 'Training', 'body' => 'Teaches your editors and staff, and writes the runbooks they keep after launch.'],
            ] as $role)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $role['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $role['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How it fits, and FAQ --}}
@php
    $faqs = [
        ['q' => 'Can you work under our existing contract vehicle?', 'a' => 'Usually. We work under cooperative purchasing agreements and standard professional-services contracts, and we respond to RFPs and RFQs. If you have not issued a solicitation yet, we can help you write one.'],
        ['q' => 'How long does a typical project take?', 'a' => 'Each phase above shows its typical range. Your written scope sets firm dates for every phase once discovery is done.'],
        ['q' => 'Who owns the work product?', 'a' => 'Your agency owns its content and data. The statement of work sets ownership of the code, designs and documentation before work starts.'],
        ['q' => 'How is accessibility handled during the project?', 'a' => 'It is part of every phase. Designs are reviewed before the build, each sprint produces an accessibility test report, and editor training covers accessible content.'],
        ['q' => 'What happens after hypercare ends?', 'a' => 'We hand off with final documentation, either to a maintenance contract with us or to your in-house team. You can decide which during the project.'],
    ];
@endphp
<section class="bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Related work</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How it fits with our other services</h2>
        <ul class="mt-8 space-y-4 text-slate-600">
            <li>A <a href="{{ route('government.website-redesign') }}" class="font-medium text-blue-700 underline hover:text-blue-800">website redesign</a> runs through all five phases, ending with migrated content and trained editors.</li>
            <li>A <a href="{{ route('government.portals') }}" class="font-medium text-blue-700 underline hover:text-blue-800">portal</a> can launch one service at a time within the build phase.</li>
            <li><a href="{{ route('government.integrations') }}" class="font-medium text-blue-700 underline hover:text-blue-800">Integrations</a> are scoped and tested in the same phases, so data and screens launch together.</li>
            <li>Hypercare ends with a handoff to a <a href="{{ route('government.maintenance') }}" class="font-medium text-blue-700 underline hover:text-blue-800">maintenance contract</a> or your in-house team.</li>
        </ul>
        <p class="mt-8 text-slate-600">
            Contracting officers can find the full summary on our
            <a href="{{ route('government.capabilities') }}" class="font-medium text-blue-700 underline hover:text-blue-800">capabilities statement</a>.
            To request a statement of work, use our
            <a href="{{ route('contact') }}" class="font-medium text-blue-700 underline hover:text-blue-800">contact page</a>.
        </p>

        <p class="mt-16 text-sm font-semibold uppercase tracking-wider text-blue-700">FAQ</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Questions about implementation</h2>
        <x-seo.faq class="mt-10" card="zinc" :items="$faqs" />
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'implementation'])
@include('pages.government.partials.cta')
@endsection
