@extends('layouts.government')

@section('title', 'System Integrations for Government Agencies')
@section('description', 'Connect government systems such as legacy mainframes, GIS, payment processors, and tax and permit systems through clean, well-documented APIs.')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'System Integrations'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                System integrations
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                Connect your modern website or portal to the systems that already run your agency &mdash; without
                ripping them out.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Discuss an integration
                </a>
                <a href="#systems"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    Systems we connect
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Intro --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">The reality</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Your data lives in 14 different places. We bridge them.
            </h2>
            <p class="mt-6 text-lg text-slate-600">
                Most agencies have a permitting system, a tax system, a GIS, a CRM, a financial system, and a
                document repository &mdash; each from a different vendor, each with its own API or lack thereof. We
                build the integration layer that lets your new portal talk to all of them.
            </p>
        </div>
    </div>
</section>

{{-- Systems --}}
<section id="systems" class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Common Systems</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Systems we integrate with
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'Permitting &amp; licensing', 'body' => 'Tyler EnerGov, Accela, OpenGov Permitting, and custom legacy systems.'], ['title' => 'Tax &amp; revenue', 'body' => 'Tyler Munis, CentralSquare, Springbrook, and state tax systems.'], ['title' => 'GIS', 'body' => 'Esri ArcGIS Online, ArcGIS Enterprise, and OpenStreetMap-based stacks.'], ['title' => 'Payment processors', 'body' => 'Stripe, Authorize.net, Heartland, NIC, GovOS, Point &amp; Pay, and PayGov.'], ['title' => 'Identity &amp; SSO', 'body' => 'Okta, Microsoft Entra ID, Google Workspace, Login.gov, and state identity providers.'], ['title' => 'Records &amp; documents', 'body' => 'OnBase, Laserfiche, SharePoint, and standalone document stores.'], ['title' => 'CRM &amp; case management', 'body' => 'Salesforce Public Sector, Microsoft Dynamics 365, and home-grown systems.'], ['title' => 'Financial &amp; ERP', 'body' => 'Oracle, SAP, Workday, and mid-market public-sector ERP suites.'], ['title' => 'Legacy &amp; mainframe', 'body' => 'Yes, even those. SOAP, fixed-width files, scheduled SFTP &mdash; we can work with them.']] as $system)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">{!! $system['title'] !!}</h3>
                    <p class="mt-2 text-sm text-slate-600">{!! $system['body'] !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Approach --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">How we build integrations</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Clean, observable, replaceable
            </h2>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'API gateway', 'body' => 'A single, documented API gateway in front of your systems &mdash; with rate limiting, auth, and logging.'], ['title' => 'Async & resilient', 'body' => 'Queued jobs and retry logic so a 30-second mainframe call doesn&rsquo;t freeze the resident&rsquo;s browser.'], ['title' => 'Observable', 'body' => 'Every integration call is logged and graphed. You see error rates and latency in real time.']] as $approach)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $approach['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{!! $approach['body'] !!}</p>
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
                    Agencies whose systems do not talk to each other
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    This fits an agency launching a new website or portal that needs live data from existing systems.
                    It also fits IT teams tired of nightly exports, manual re-keying and old scripts no one documented.
                    The IT director or a department&rsquo;s systems lead usually owns the work.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    The problems are familiar. Staff copy data between systems by hand. A resident pays online, but the
                    tax system does not know until the next batch runs. A vendor upgrade breaks a script, and no one
                    finds out until residents call.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-8">
                <h3 class="text-lg font-semibold text-slate-900">What your agency provides</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['A list of the systems involved and who owns each', 'API documentation, file layouts or vendor contacts', 'Test environments or sample data for each system', 'Your rules for service accounts and credentials', 'Staff who can confirm the data looks right'] as $item)
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
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How an integration project runs</h2>
        <ol class="mt-10 space-y-6">
            @foreach ([
                ['title' => 'Discovery and scoping', 'body' => 'We list every system, data flow and owner, and test what each system can do. You get a written scope.'],
                ['title' => 'Design and architecture', 'body' => 'We design the gateway, data mappings and error handling, and document each connection.'],
                ['title' => 'Build and iterate', 'body' => 'We build one connection at a time in two-week sprints, against test systems first.'],
                ['title' => 'Test, train and launch', 'body' => 'Your staff check real records end to end. We switch to production with a runbook and walk IT through the logs.'],
                ['title' => 'Hypercare and transition', 'body' => 'We watch error rates closely after launch, then hand off to maintenance or your IT team.'],
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

{{-- How it fits --}}
<section class="bg-slate-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Related work</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How it fits with our other services</h2>
        <ul class="mt-8 space-y-4 text-slate-600">
            <li><a href="{{ route('government.portals') }}" class="font-medium text-blue-700 underline hover:text-blue-800">Portals</a> rely on integrations, so that when residents apply and pay, your back-office systems update.</li>
            <li>Your <a href="{{ route('government.cms') }}" class="font-medium text-blue-700 underline hover:text-blue-800">content management system</a> can show live data such as permit status or GIS layers.</li>
            <li>The integration layer can run on our <a href="{{ route('government.hosting') }}" class="font-medium text-blue-700 underline hover:text-blue-800">managed hosting</a>, with the same monitoring and logs.</li>
            <li>A <a href="{{ route('government.maintenance') }}" class="font-medium text-blue-700 underline hover:text-blue-800">maintenance contract</a> keeps connections working when vendors upgrade their systems.</li>
        </ul>
        <p class="mt-8 text-slate-600">
            Every service is listed on our
            <a href="{{ route('government.capabilities') }}" class="font-medium text-blue-700 underline hover:text-blue-800">capabilities statement</a>.
            To map your systems with us, start on our
            <a href="{{ route('contact') }}" class="font-medium text-blue-700 underline hover:text-blue-800">contact page</a>.
        </p>
    </div>
</section>

{{-- FAQ --}}
@php
    $faqs = [
        ['q' => 'How do we buy integration work?', 'a' => 'As part of a larger project or on its own. We respond to RFPs and work under cooperative purchasing agreements, with fixed-fee pricing tied to deliverables. You keep the systems you have.'],
        ['q' => 'How long does an integration take?', 'a' => 'It depends on the systems and how well they are documented. A modern API connects faster than a mainframe file feed. The written scope sets a date for each phase.'],
        ['q' => 'Who owns the integration code?', 'a' => 'The statement of work sets ownership of the code before work starts. Every connection is documented, so your team or another vendor can maintain it.'],
        ['q' => 'Do integrations affect accessibility?', 'a' => 'The integration layer has no screens of its own. Pages and portals that show its data are built to WCAG 2.2 AA, including the messages shown when a system is down.'],
        ['q' => 'What happens when a vendor changes its system?', 'a' => 'Monitoring flags failed calls as they happen. Under a maintenance contract, we update the connection and test it again.'],
    ];
@endphp
<section class="bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">FAQ</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Questions about integrations</h2>
        <x-seo.faq class="mt-10" card="zinc" :items="$faqs" />
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'integrations'])
@include('pages.government.partials.cta')
@endsection
