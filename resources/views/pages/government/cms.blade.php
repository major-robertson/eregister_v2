@extends('layouts.government')

@section('title', 'Government CMS | Content Management Systems for Public Agencies')

@section('meta')
<meta name="description"
    content="Editor-friendly content management systems built for government. Role-based publishing, multi-department workflows, accessible content blocks, and modern editorial tooling.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
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

@include('pages.government.partials.related', ['exclude' => 'cms'])
@include('pages.government.partials.cta')
@endsection
