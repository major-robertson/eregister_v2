@extends('layouts.government')

@section('title', 'Content Management Systems for Government Agencies')
@section('description', 'Editor-friendly content management for government: role-based publishing, multi-department workflows, and accessible content blocks.')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'Content Management'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Content management for government
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                A CMS your editors actually want to use &mdash; with the workflows, permissions, and accessibility
                guardrails that public-sector publishing demands.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Talk to our team
                </a>
                <a href="#features"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    See features
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Intro --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">The problem with most agency CMS
                </p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Built for developers, not for editors
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    Most government CMS platforms are either ancient SharePoint installs that nobody understands, or
                    enterprise products with licensing bills that keep growing while the editor experience stays
                    stuck in 2012.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    We build &mdash; or migrate to &mdash; modern, editor-friendly CMS platforms that match how your
                    departments actually publish.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">Platforms we work with</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['Custom Laravel-based CMS (recommended for tight integration)', 'Headless CMS (Statamic, Strapi, Sanity)', 'WordPress VIP / Multisite (when content team is large)', 'Drupal (when migrating an existing Drupal site)', 'Migration from SharePoint, Sitecore, OpenText, or static HTML'] as $option)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-blue-700" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4" />
                            </svg>
                            <span>{{ $option }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Features --}}
<section id="features" class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Capabilities</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                What your editors get on day one
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'Block-based editing', 'body' => 'Pre-built, accessible content blocks. Editors compose pages without touching code or breaking the design.'], ['title' => 'Role-based permissions', 'body' => 'Department-scoped access so the Parks team can&rsquo;t accidentally edit the Tax Assessor&rsquo;s pages.'], ['title' => 'Editorial workflows', 'body' => 'Draft, review, approve, schedule. Configurable per content type with email notifications.'], ['title' => 'Versioning & rollback', 'body' => 'Every change tracked with one-click rollback. Audit log for every publish event.'], ['title' => 'Accessible by default', 'body' => 'Built-in alt-text reminders, heading-order checks, and contrast validation as content is authored.'], ['title' => 'Search & taxonomy', 'body' => 'Configurable site search with synonyms, plus taxonomies for cross-department content reuse.']] as $feature)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $feature['title'] }}</h3>
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
                    Agencies where many people publish
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    This fits a city with many departments, a county where each elected office owns its pages, or a
                    state agency whose program staff post updates. It also fits a small team stuck on a platform no
                    one can support. IT or communications usually leads, with input from the clerk or records office.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    The common problems are familiar. Only one person knows how to publish. Pages go out without
                    review. Old content never comes down. A new CMS fixes the process, not just the software.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">What your agency provides</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['A list of departments and who publishes for each', 'Your approval rules: who drafts, reviews and publishes', 'Access to the current CMS or a full content export', 'Records-retention rules that apply to web content', 'Editors who can attend training sessions'] as $item)
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
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How a CMS project runs</h2>
        <ol class="mt-10 space-y-6">
            @foreach ([
                ['title' => 'Discovery and scoping', 'body' => 'We map your departments, content types and approval rules, and choose the platform with you. You get a written scope.'],
                ['title' => 'Design and architecture', 'body' => 'We define roles, workflows and content types, and design the accessible blocks your editors will use.'],
                ['title' => 'Build and iterate', 'body' => 'We configure the CMS in two-week sprints and demo it to your editors as it takes shape.'],
                ['title' => 'Test, train and launch', 'body' => 'We migrate content, run acceptance testing with your editors and train each department before go-live.'],
                ['title' => 'Hypercare and transition', 'body' => 'We answer editor questions closely after launch, then hand off to maintenance or your own team.'],
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
        ['q' => 'How do we buy a CMS project?', 'a' => 'Through your normal process. We respond to RFPs and RFQs, or work under a cooperative purchasing agreement. The fixed-fee statement of work lists any third-party license costs separately.'],
        ['q' => 'How long does a CMS migration take?', 'a' => 'It depends on how many pages and content types move. The written scope sets a date for each phase before the build starts.'],
        ['q' => 'Who owns the content and the CMS setup?', 'a' => 'Your agency owns its content. The statement of work sets ownership of the code and configuration before work starts, and handoff documentation explains how everything is stored.'],
        ['q' => 'Does the CMS help editors publish accessible pages?', 'a' => 'Yes. Content blocks are built to WCAG 2.2 AA, and the editor flags missing alt text, skipped headings and low contrast as authors write.'],
        ['q' => 'Who helps our editors after launch?', 'a' => 'We train each department before go-live and stay close during hypercare. After that, editor support tickets are part of our maintenance tiers.'],
    ];
@endphp
<section class="bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Related work</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How it fits with our other services</h2>
        <ul class="mt-8 space-y-4 text-slate-600">
            <li>A CMS move often happens inside a <a href="{{ route('government.website-redesign') }}" class="font-medium text-blue-700 underline hover:text-blue-800">website redesign</a>, so content and design change together.</li>
            <li>The editor catches common issues; an <a href="{{ route('government.accessibility') }}" class="font-medium text-blue-700 underline hover:text-blue-800">accessibility audit</a> covers the rest.</li>
            <li><a href="{{ route('government.integrations') }}" class="font-medium text-blue-700 underline hover:text-blue-800">System integrations</a> can pull data such as GIS layers or permit status into CMS pages.</li>
            <li>A <a href="{{ route('government.maintenance') }}" class="font-medium text-blue-700 underline hover:text-blue-800">maintenance contract</a> keeps the CMS and its add-ons patched.</li>
        </ul>
        <p class="mt-8 text-slate-600">
            Every service is listed on our
            <a href="{{ route('government.capabilities') }}" class="font-medium text-blue-700 underline hover:text-blue-800">capabilities statement</a>.
            To talk through your publishing setup, use our
            <a href="{{ route('contact') }}" class="font-medium text-blue-700 underline hover:text-blue-800">contact page</a>.
        </p>

        <p class="mt-16 text-sm font-semibold uppercase tracking-wider text-blue-700">FAQ</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Questions about a new CMS</h2>
        <x-seo.faq class="mt-10" card="zinc" :items="$faqs" />
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'cms'])
@include('pages.government.partials.cta')
@endsection
