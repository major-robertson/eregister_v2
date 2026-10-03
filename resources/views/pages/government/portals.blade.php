@extends('layouts.government')

@section('title', 'Citizen & Staff Portals for Government Agencies')
@section('description', 'Custom citizen self-service portals and internal staff portals for government agencies: permits, licensing, payments, requests, and intranet workflows.')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'Portals'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Citizen &amp; staff portals
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                Self-service portals that let residents apply, pay, and check status &mdash; and internal staff
                portals that replace spreadsheets, email chains, and aging Access databases.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Scope a portal
                </a>
                <a href="#examples"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    See examples
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Two columns --}}
<section id="examples" class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Resident-facing</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Citizen self-service portals</h2>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['Permit applications and renewals', 'Business licensing &amp; registration', 'Tax and utility payments', '311-style service requests', 'FOIA / public records requests', 'Inspection scheduling', 'Online forms with conditional logic'] as $item)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-700" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4" />
                            </svg>
                            <span>{!! $item !!}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Staff-facing</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-900">Internal portals &amp; intranets</h2>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['Department dashboards and case queues', 'Staff directories with org charts', 'Document libraries with version control', 'Internal forms (HR, IT, facilities)', 'Approval workflows with audit trails', 'Reporting dashboards for leadership', 'SSO with your existing identity provider'] as $item)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-700" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4" />
                            </svg>
                            <span>{!! $item !!}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Foundations --}}
<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Built-in Foundations</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Every portal ships with these
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'Identity &amp; SSO', 'body' => 'SAML, OIDC, and integration with state/county identity systems. MFA optional or required by role.'], ['title' => 'Payments', 'body' => 'PCI-compliant integrations with Stripe, Authorize.net, and major government payment processors.'], ['title' => 'Document upload', 'body' => 'Virus-scanned uploads with automatic redaction support and audit-ready storage.'], ['title' => 'Notifications', 'body' => 'Email and SMS notifications driven by workflow events. Configurable per applicant preference.'], ['title' => 'Audit logging', 'body' => 'Every action is logged. Full audit trail available for FOIA, OIG, and internal review.'], ['title' => 'Accessibility', 'body' => 'Every component is WCAG 2.2 AA conformant out of the box. Tested with assistive tech.']] as $feature)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">{!! $feature['title'] !!}</h3>
                    <p class="mt-2 text-sm text-slate-600">{!! $feature['body'] !!}</p>
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
                    Departments that still run on paper and email
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    This fits a permits office that takes applications by PDF and email, a clerk&rsquo;s office that
                    tracks records requests in a spreadsheet, or a department that runs on an aging Access database. A
                    portal can cover one process or grow one service at a time. The department head usually leads, with
                    IT as a partner.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    The problems are the same everywhere. Residents cannot see where their request stands, so they
                    call. Staff retype form data into other systems. Approvals live in inboxes, with no record of who
                    signed off.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">What your agency provides</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['A process owner for each service in the portal', 'Current forms, fee schedules and approval steps', 'Access to the systems the portal must read or update', 'Identity provider details for staff sign-in', 'Staff and residents willing to test early versions'] as $item)
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
<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">The process</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How a portal project runs</h2>
        <ol class="mt-10 space-y-6">
            @foreach ([
                ['title' => 'Discovery and scoping', 'body' => 'We walk through each process with the staff who run it and map every form, fee and approval. You get a written scope.'],
                ['title' => 'Design and architecture', 'body' => 'We design the resident and staff screens, the data model and the workflow, and review the screens for accessibility.'],
                ['title' => 'Build and iterate', 'body' => 'We build one service at a time in two-week sprints, with demos to the staff who will use it.'],
                ['title' => 'Test, train and launch', 'body' => 'Staff and a group of residents test it. We train staff, move open cases over and launch with a runbook.'],
                ['title' => 'Hypercare and transition', 'body' => 'We stay close as the first real applications arrive, then hand off to maintenance or your team.'],
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
            These are the same phases we use on every project. The full plan is on our
            <a href="{{ route('government.implementation') }}" class="font-medium text-blue-700 underline hover:text-blue-800">implementation page</a>.
        </p>
    </div>
</section>

{{-- How it fits, and FAQ --}}
@php
    $faqs = [
        ['q' => 'How do we buy a portal project?', 'a' => 'Through your usual process. We respond to RFPs, work under cooperative purchasing agreements, and price each phase as a fixed-fee deliverable. You can start with one service and add more later.'],
        ['q' => 'How long does a portal take to build?', 'a' => 'It depends on how many services and integrations it needs. The written scope sets a date for each phase, and a first service can launch before the rest are built.'],
        ['q' => 'Who owns the portal and the data residents submit?', 'a' => 'Your agency owns the data residents submit. The statement of work sets ownership of the code before work starts.'],
        ['q' => 'Can residents with disabilities use it?', 'a' => 'Every screen is built to WCAG 2.2 AA and tested with assistive technology, including forms, uploads and payment steps.'],
        ['q' => 'What support is there after launch?', 'a' => 'We stay close during hypercare as the first applications arrive. After that, a maintenance contract covers fixes, updates and changes to forms or workflows.'],
    ];
@endphp
<section class="bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Related work</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How it fits with our other services</h2>
        <ul class="mt-8 space-y-4 text-slate-600">
            <li><a href="{{ route('government.integrations') }}" class="font-medium text-blue-700 underline hover:text-blue-800">System integrations</a> connect the portal to your permitting, tax, payment and GIS systems.</li>
            <li>The portal can run on our <a href="{{ route('government.hosting') }}" class="font-medium text-blue-700 underline hover:text-blue-800">managed hosting</a>, with backups, monitoring and audit logs.</li>
            <li>A <a href="{{ route('government.website-redesign') }}" class="font-medium text-blue-700 underline hover:text-blue-800">website redesign</a> puts the portal&rsquo;s entry points where residents look first.</li>
            <li>An <a href="{{ route('government.accessibility') }}" class="font-medium text-blue-700 underline hover:text-blue-800">accessibility audit</a> can check the full resident journey, from search to payment.</li>
        </ul>
        <p class="mt-8 text-slate-600">
            Every service is listed on our
            <a href="{{ route('government.capabilities') }}" class="font-medium text-blue-700 underline hover:text-blue-800">capabilities statement</a>.
            To scope your first service, tell us about the process on our
            <a href="{{ route('contact') }}" class="font-medium text-blue-700 underline hover:text-blue-800">contact page</a>.
        </p>

        <p class="mt-16 text-sm font-semibold uppercase tracking-wider text-blue-700">FAQ</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Questions about portals</h2>
        <x-seo.faq class="mt-10" card="zinc" :items="$faqs" />
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'portals'])
@include('pages.government.partials.cta')
@endsection
