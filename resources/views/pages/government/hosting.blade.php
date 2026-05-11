@extends('layouts.government')

@section('title', 'Government Web Hosting | Secure, U.S.-Based Hosting for Agency Sites')

@section('meta')
<meta name="description"
    content="Hardened, U.S.-based hosting and infrastructure for government websites. Uptime SLAs, automated backups, monitoring, security patching, and DDoS protection.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
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
            @foreach ([['stat' => '99.9%', 'label' => 'Uptime SLA'], ['stat' => '< 15min', 'label' => 'Incident response'], ['stat' => 'Daily', 'label' => 'Encrypted backups'], ['stat' => 'U.S.', 'label' => 'Data residency']] as $stat)
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
            @foreach ([['title' => 'Hardened cloud infrastructure', 'body' => 'Built on FedRAMP-aligned U.S. cloud regions with private networking and encryption at rest and in transit.'], ['title' => '24/7 monitoring & alerting', 'body' => 'Synthetic uptime checks, error-rate tracking, and on-call rotation. We see issues before residents do.'], ['title' => 'Automated patching', 'body' => 'OS, runtime, and dependency security patches applied on a defined cadence with rollback safety.'], ['title' => 'Daily encrypted backups', 'body' => 'Off-site, encrypted backups with point-in-time recovery and tested restore procedures.'], ['title' => 'DDoS &amp; WAF protection', 'body' => 'Web application firewall and DDoS mitigation in front of every public endpoint.'], ['title' => 'Audit-ready logging', 'body' => 'Centralized logs retained per your records-management policy, available for FOIA or audit requests.']] as $item)
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

@include('pages.government.partials.related', ['exclude' => 'hosting'])
@include('pages.government.partials.cta')
@endsection
