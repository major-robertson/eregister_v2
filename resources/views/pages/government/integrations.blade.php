@extends('layouts.government')

@section('title', 'Government System Integrations | Connect Legacy Systems & APIs')

@section('meta')
<meta name="description"
    content="Integrate government systems &mdash; legacy mainframes, GIS, payment processors, tax and permit systems &mdash; through clean, well-documented APIs.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
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
                Systems we&rsquo;ve integrated
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'Permitting &amp; licensing', 'body' => 'Tyler EnerGov, Accela, OpenGov Permitting, and custom legacy systems.'], ['title' => 'Tax &amp; revenue', 'body' => 'Tyler Munis, CentralSquare, Springbrook, and state tax systems.'], ['title' => 'GIS', 'body' => 'Esri ArcGIS Online, ArcGIS Enterprise, and OpenStreetMap-based stacks.'], ['title' => 'Payment processors', 'body' => 'Stripe, Authorize.net, Heartland, NIC, GovOS, Point &amp; Pay, and PayGov.'], ['title' => 'Identity &amp; SSO', 'body' => 'Okta, Microsoft Entra ID, Google Workspace, Login.gov, and state identity providers.'], ['title' => 'Records &amp; documents', 'body' => 'OnBase, Laserfiche, SharePoint, and standalone document stores.'], ['title' => 'CRM &amp; case management', 'body' => 'Salesforce Public Sector, Microsoft Dynamics 365, and home-grown systems.'], ['title' => 'Financial &amp; ERP', 'body' => 'Oracle, SAP, Workday, and mid-market public-sector ERP suites.'], ['title' => 'Legacy &amp; mainframe', 'body' => 'Yes, even those. SOAP, fixed-width files, scheduled SFTP &mdash; we&rsquo;ve seen it all.']] as $system)
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
            @foreach ([['title' => 'API gateway', 'body' => 'A single, documented API gateway in front of your systems &mdash; with rate limiting, auth, and logging.'], ['title' => 'Async &amp; resilient', 'body' => 'Queued jobs and retry logic so a 30-second mainframe call doesn&rsquo;t freeze the resident&rsquo;s browser.'], ['title' => 'Observable', 'body' => 'Every integration call is logged and graphed. You see error rates and latency in real time.']] as $approach)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $approach['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{!! $approach['body'] !!}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'integrations'])
@include('pages.government.partials.cta')
@endsection
