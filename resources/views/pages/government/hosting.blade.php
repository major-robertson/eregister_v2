@extends('layouts.government')

@section('title', 'Secure Web Hosting for Government Agencies')
@section('description', 'Hardened, U.S.-based hosting for government websites, with uptime SLAs, automated backups, monitoring, security patching, and DDoS protection.')

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <x-seo.breadcrumbs class="mb-6 text-slate-400" center :items="[
                ['name' => 'Government', 'url' => route('government.home')],
                ['name' => 'Hosting'],
            ]" />
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Hosting &amp; infrastructure for government
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                U.S.-based, hardened hosting with the monitoring, backup, and uptime guarantees public-sector workloads
                require.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Get a hosting quote
                </a>
                <a href="#stack"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    See the stack
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Promise --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">The Promise</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Stays online. Stays secure. Stays patched.
            </h2>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['stat' => 'SLA', 'label' => 'Uptime commitment in every contract'], ['stat' => 'On call', 'label' => 'Incident response'], ['stat' => 'Daily', 'label' => 'Encrypted backups'], ['stat' => 'U.S.', 'label' => 'Data residency']] as $stat)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
                    <p class="text-3xl font-bold text-blue-700">{{ $stat['stat'] }}</p>
                    <p class="mt-2 text-sm text-slate-600">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Stack --}}
<section id="stack" class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">What&rsquo;s included</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                A complete, managed environment
            </h2>
        </div>

        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([['title' => 'Hardened cloud infrastructure', 'body' => 'Built on U.S. cloud regions with private networking and encryption at rest and in transit.'], ['title' => 'Monitoring & alerting', 'body' => 'Synthetic uptime checks, error-rate tracking, and on-call rotation. We see issues before residents do.'], ['title' => 'Automated patching', 'body' => 'OS, runtime, and dependency security patches applied on a defined cadence with rollback safety.'], ['title' => 'Daily encrypted backups', 'body' => 'Off-site, encrypted backups with point-in-time recovery and tested restore procedures.'], ['title' => 'DDoS & WAF protection', 'body' => 'Web application firewall and DDoS mitigation in front of every public endpoint.'], ['title' => 'Audit-ready logging', 'body' => 'Centralized logs retained per your records-management policy, available for FOIA or audit requests.']] as $item)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ $item['title'] }}</h3>
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
                    Agencies that would rather not run servers
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    This fits an agency whose website runs on an aging server down the hall, on a shared host with no
                    real support, or on infrastructure the original vendor no longer maintains. It also fits IT teams
                    who would rather spend their time on internal systems. Towns, counties and state programs get the
                    same managed setup, sized to the traffic you expect.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    The problems we see are plain. No one knows when the server was last patched. Backups exist, but
                    no one has tried a restore. The site slows down during a storm or an election night, just when
                    residents need it most. An audit request turns into a hunt for logs.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">What your agency provides</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['Access to your domain registrar and DNS', 'Your security and records-retention policies', 'Any data-residency or logging rules your state sets', 'A contact for incident notices', 'An agreed change window for the cutover'] as $item)
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
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How a hosting move runs</h2>
        <ol class="mt-10 space-y-6">
            @foreach ([
                ['title' => 'Discovery and scoping', 'body' => 'We review your current hosting, traffic, domains and security rules. You get a written migration plan.'],
                ['title' => 'Design and architecture', 'body' => 'We design the environment, including networking, backups, logging and monitoring, to match your policies.'],
                ['title' => 'Build and iterate', 'body' => 'We build the environment and run your site on it in staging so your team can test.'],
                ['title' => 'Test, train and launch', 'body' => 'We test a restore from backup, agree a cutover window with you and switch DNS using a runbook.'],
                ['title' => 'Hypercare and transition', 'body' => 'We watch the new environment closely after cutover, then move it into routine monitoring and patching.'],
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
            Your current site stays live until cutover day. These are the same phases we use on every project.
            The full plan is on our
            <a href="{{ route('government.implementation') }}" class="font-medium text-blue-700 underline hover:text-blue-800">implementation page</a>.
        </p>
    </div>
</section>

{{-- How it fits, and FAQ --}}
@php
    $faqs = [
        ['q' => 'Can we buy hosting on its own?', 'a' => 'Yes. Hosting can be a stand-alone contract or part of a larger project. We respond to your solicitation or work under a cooperative purchasing agreement, on standard government payment terms.'],
        ['q' => 'How long does a hosting move take?', 'a' => 'It depends on the size of the site and how many systems connect to it. The written plan sets a date for each phase, from discovery to cutover, before work starts.'],
        ['q' => 'Who owns our data and code?', 'a' => 'Your agency owns its data. It stays in U.S. regions, backups are encrypted, and the statement of work sets how data and code come back to you if the contract ends.'],
        ['q' => 'Does hosting affect accessibility?', 'a' => 'Indirectly. Slow or unreliable pages are hard to use, especially with assistive technology or an older phone. The pages themselves are covered by our accessibility audit and remediation work.'],
        ['q' => 'What happens when something goes wrong?', 'a' => 'Monitoring alerts our on-call engineer, who works the incident and keeps your contact informed. Response terms are written into the SLA in your contract.'],
    ];
@endphp
<section class="bg-white py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Related work</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">How it fits with our other services</h2>
        <ul class="mt-8 space-y-4 text-slate-600">
            <li>A <a href="{{ route('government.website-redesign') }}" class="font-medium text-blue-700 underline hover:text-blue-800">website redesign</a> is a natural time to move hosting, since the site is changing anyway.</li>
            <li>A <a href="{{ route('government.maintenance') }}" class="font-medium text-blue-700 underline hover:text-blue-800">maintenance contract</a> covers the application: code updates, accessibility checks and editor help.</li>
            <li><a href="{{ route('government.portals') }}" class="font-medium text-blue-700 underline hover:text-blue-800">Portals</a> and <a href="{{ route('government.integrations') }}" class="font-medium text-blue-700 underline hover:text-blue-800">integrations</a> run in the same environment, with the same monitoring and backups.</li>
            <li>When hosting is one piece of a bigger change, our <a href="{{ route('government.implementation') }}" class="font-medium text-blue-700 underline hover:text-blue-800">implementation services</a> run the move inside one fixed-fee project.</li>
        </ul>
        <p class="mt-8 text-slate-600">
            Every service is listed on our
            <a href="{{ route('government.capabilities') }}" class="font-medium text-blue-700 underline hover:text-blue-800">capabilities statement</a>.
            For a hosting quote, tell us about your current setup on our
            <a href="{{ route('contact') }}" class="font-medium text-blue-700 underline hover:text-blue-800">contact page</a>.
        </p>

        <p class="mt-16 text-sm font-semibold uppercase tracking-wider text-blue-700">FAQ</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Questions about hosting</h2>
        <x-seo.faq class="mt-10" card="zinc" :items="$faqs" />
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'hosting'])
@include('pages.government.partials.cta')
@endsection
