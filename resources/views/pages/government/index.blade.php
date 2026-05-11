@extends('layouts.government')

@section('title', 'Government Digital Services | Website Redesign, CMS, Accessibility & Hosting')

@section('meta')
<meta name="description"
    content="eRegister Government builds modern, accessible, secure websites and digital services for federal, state, county, and city agencies. Website redesigns, CMS, citizen portals, hosting, and ongoing maintenance.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-28">
    <div class="absolute inset-0 opacity-[0.05]"
        style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%23ffffff&quot; fill-opacity=&quot;1&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')">
    </div>

    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div
            class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-500/10 px-4 py-1.5 text-sm text-blue-200">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            Digital services for public-sector teams
        </div>

        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Modern websites, portals, and infrastructure
            <span class="bg-gradient-to-r from-blue-300 to-cyan-300 bg-clip-text text-transparent">for government.</span>
        </h1>

        <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
            We help state, county, city, school, and public-sector teams redesign legacy websites, launch citizen
            portals, and keep digital services accessible, secure, and reliable &mdash; with clear scopes, responsive
            communication, and long-term support.
        </p>

        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('contact') }}"
                class="group inline-flex items-center gap-2 rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500 hover:shadow-xl">
                Request a consultation
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
            <a href="#services"
                class="inline-flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                Explore services
            </a>
        </div>

        <div class="mt-14 grid grid-cols-2 gap-6 text-left sm:grid-cols-4">
            <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-4">
                <p class="text-2xl font-bold text-white">Nationwide</p>
                <p class="mt-1 text-xs text-slate-400">Service coverage</p>
            </div>
            <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-4">
                <p class="text-2xl font-bold text-white">WCAG AA</p>
                <p class="mt-1 text-xs text-slate-400">Accessibility baseline</p>
            </div>
            <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-4">
                <p class="text-2xl font-bold text-white">99.9%</p>
                <p class="mt-1 text-xs text-slate-400">Hosting uptime target</p>
            </div>
            <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-4">
                <p class="text-2xl font-bold text-white">U.S.-based</p>
                <p class="mt-1 text-xs text-slate-400">Team &amp; hosting</p>
            </div>
        </div>
    </div>
</section>

{{-- Audience strip --}}
<section class="border-b border-slate-200 bg-white py-10">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
            Built for the agencies citizens rely on
        </p>
        <div class="mt-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-sm font-medium text-slate-700">
            <span>State Agencies</span>
            <span class="text-slate-300">&bull;</span>
            <span>County Governments</span>
            <span class="text-slate-300">&bull;</span>
            <span>City &amp; Municipal</span>
            <span class="text-slate-300">&bull;</span>
            <span>School Districts</span>
            <span class="text-slate-300">&bull;</span>
            <span>Special Districts</span>
            <span class="text-slate-300">&bull;</span>
            <span>Public Authorities</span>
        </div>
    </div>
</section>

{{-- Services --}}
<section id="services" class="bg-slate-50 py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Services</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Everything your digital presence needs
            </h2>
            <p class="mt-4 text-lg text-slate-600">
                From a full website redesign to long-term maintenance, we handle the engineering, design,
                accessibility, hosting, and support work so your team can focus on serving the public.
            </p>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $services = [
                    [
                        'route' => 'government.website-redesign',
                        'title' => 'Website Redesign',
                        'description' => 'Modern, mobile-first redesigns of legacy agency websites with measurable performance and usability gains.',
                        'icon' => 'M4 6a2 2 0 012-2h12a2 2 0 012 2v2H4V6zM4 10h16v8a2 2 0 01-2 2H6a2 2 0 01-2-2v-8z',
                    ],
                    [
                        'route' => 'government.accessibility',
                        'title' => 'Accessibility',
                        'description' => 'Accessibility audits, remediation, and documentation aligned with ADA, Section 508, and WCAG AA requirements.',
                        'icon' => 'M12 4v16m8-8H4',
                    ],
                    [
                        'route' => 'government.cms',
                        'title' => 'Content Management',
                        'description' => 'Editor-friendly CMS with workflows, role-based publishing, and accessible templates.',
                        'icon' => 'M4 6h16M4 12h16M4 18h7',
                    ],
                    [
                        'route' => 'government.hosting',
                        'title' => 'Hosting & Infrastructure',
                        'description' => 'U.S.-based hosting options with monitoring, backups, SSL, patching, and uptime targets.',
                        'icon' => 'M5 12H3l9-9 9 9h-2M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7',
                    ],
                    [
                        'route' => 'government.maintenance',
                        'title' => 'Maintenance & Support',
                        'description' => 'Patches, content updates, accessibility monitoring, and incident response on contract.',
                        'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                    ],
                    [
                        'route' => 'government.portals',
                        'title' => 'Citizen & Staff Portals',
                        'description' => 'Self-service portals for permits, licensing, payments, requests, and internal staff workflows.',
                        'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-2a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z',
                    ],
                    [
                        'route' => 'government.integrations',
                        'title' => 'System Integrations',
                        'description' => 'Connect legacy systems, GIS platforms, payment processors, permitting tools, and internal databases through clean APIs.',
                        'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                    ],
                    [
                        'route' => 'government.implementation',
                        'title' => 'Implementation Services',
                        'description' => 'Procurement-friendly project delivery: discovery, build, training, and go-live support.',
                        'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                    ],
                ];
            @endphp

            @foreach ($services as $service)
                <a href="{{ route($service['route']) }}"
                    class="group relative flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="{{ $service['icon'] }}" />
                        </svg>
                    </div>
                    <h3 class="mt-5 text-lg font-semibold text-slate-900">{{ $service['title'] }}</h3>
                    <p class="mt-2 flex-1 text-sm text-slate-600">{{ $service['description'] }}</p>
                    <span
                        class="mt-4 inline-flex items-center text-sm font-semibold text-blue-700 transition group-hover:text-blue-800">
                        Learn more
                        <svg class="ml-1 h-4 w-4 transition group-hover:translate-x-0.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- Why us --}}
<section class="bg-white py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Why agencies pick eRegister</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Public-sector delivery with modern product-team execution
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    We combine structured project delivery with modern design and engineering practices. That means
                    clear timelines, practical documentation, lower long-term maintenance burden, and digital tools
                    your staff can confidently manage after launch.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('contact') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800">
                        Talk to our team
                    </a>
                    <a href="#process"
                        class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        See how we work
                    </a>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @php
                    $reasons = [
                        ['title' => 'U.S.-based engineering', 'body' => 'Designers, developers, and project managers based in the U.S., with direct communication throughout the project.'],
                        ['title' => 'Accessibility-first', 'body' => 'Accessibility planning, testing, and remediation are included throughout the project lifecycle.'],
                        ['title' => 'Procurement-friendly', 'body' => 'We work cleanly with RFPs, RFQs, fixed-fee SOWs, public-sector review cycles, and structured procurement requirements.'],
                        ['title' => 'Plain-English reporting', 'body' => 'You get clear progress updates, decision points, and status reporting for project teams, executives, and procurement stakeholders.'],
                        ['title' => 'Modern stack', 'body' => 'Laravel, secure cloud hosting, version-controlled deployments, and CI/CD pipelines that keep your site maintainable, patched, and fast.'],
                        ['title' => 'Long-term partner', 'body' => 'We stay on after launch. Maintenance, updates, and accessibility monitoring on contract.'],
                    ];
                @endphp

                @foreach ($reasons as $reason)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <h3 class="text-sm font-semibold text-slate-900">{{ $reason['title'] }}</h3>
                        </div>
                        <p class="mt-2 text-sm text-slate-600">{{ $reason['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Process --}}
<section id="process" class="bg-slate-50 py-20 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Our Process</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                A predictable path from kickoff to go-live
            </h2>
            <p class="mt-4 text-lg text-slate-600">
                Every project follows the same proven phases, with clear deliverables and approvals at each step.
            </p>
        </div>

        <div class="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @php
                $steps = [
                    ['number' => '01', 'title' => 'Discovery', 'body' => 'Stakeholder interviews, content audit, accessibility baseline, technical review, and a written scope your team can use for approvals and procurement.'],
                    ['number' => '02', 'title' => 'Design', 'body' => 'Information architecture, brand-aligned visual design, and accessible component library reviewed by your team.'],
                    ['number' => '03', 'title' => 'Build & Test', 'body' => 'Iterative development, automated and manual accessibility testing, security review, and content migration.'],
                    ['number' => '04', 'title' => 'Launch & Support', 'body' => 'Go-live with a runbook, training for your editors, and ongoing maintenance under SLA.'],
                ];
            @endphp

            @foreach ($steps as $step)
                <div class="relative rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="text-3xl font-bold text-blue-700">{{ $step['number'] }}</div>
                    <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ $step['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-slate-950 py-20 lg:py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-800 to-slate-900 p-10 sm:p-16">
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(circle at 30% 20%, white 0px, transparent 60%), radial-gradient(circle at 70% 80%, white 0px, transparent 50%);">
            </div>
            <div class="relative mx-auto max-w-3xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Ready to modernize your agency&rsquo;s digital presence?
                </h2>
                <p class="mt-4 text-lg text-blue-100">
                    Tell us about your project &mdash; website redesign, portal, accessibility audit, hosting,
                    maintenance, or full implementation &mdash; and our team will follow up promptly.
                </p>
                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('contact') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-white px-8 py-4 text-base font-semibold text-blue-900 shadow-lg transition hover:bg-blue-50">
                        Start a conversation
                    </a>
                    <a href="#services"
                        class="inline-flex items-center justify-center rounded-lg border border-white/30 px-8 py-4 text-base font-semibold text-white transition hover:bg-white/10">
                        Browse all services
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
