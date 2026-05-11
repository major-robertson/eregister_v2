@extends('layouts.government')

@section('title', 'Government Website Maintenance & Support | Long-Term Care Contracts')

@section('meta')
<meta name="description"
    content="Long-term maintenance and support contracts for government websites. Security patching, content updates, accessibility monitoring, and on-call incident response.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-300">Service</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                Maintenance &amp; ongoing support
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-300 sm:text-xl">
                Launching a new website is the easy part. We&rsquo;re the team that keeps it healthy for the next five
                years &mdash; on a predictable, fixed-fee contract.
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('contact') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-blue-500">
                    Discuss a support contract
                </a>
                <a href="#tiers"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-900/50 px-8 py-4 text-base font-semibold text-white transition hover:bg-slate-800">
                    See coverage tiers
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Why --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Why agencies need this</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                    Websites rot. We prevent that.
                </h2>
                <p class="mt-6 text-lg text-slate-600">
                    Without a dedicated maintenance partner, even the best-built website starts breaking down after 18
                    months &mdash; security patches go un-applied, accessibility regressions creep in, and small bugs
                    pile up until the next costly redesign.
                </p>
                <p class="mt-4 text-lg text-slate-600">
                    Our maintenance contracts keep your site secure, accessible, and current &mdash; so you don&rsquo;t
                    have to start over every five years.
                </p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8">
                <h3 class="text-lg font-semibold text-slate-900">What&rsquo;s always covered</h3>
                <ul class="mt-6 space-y-3 text-slate-700">
                    @foreach (['Security patches and dependency updates', 'Accessibility regression monitoring', 'Uptime monitoring and incident response', 'Quarterly performance reports', 'Editor support tickets', 'Documented runbooks for your team'] as $item)
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

{{-- Tiers --}}
<section id="tiers" class="bg-slate-50 py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Coverage Tiers</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                Pick the level of support that matches your team
            </h2>
        </div>

        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @foreach ([
                ['name' => 'Essentials', 'tagline' => 'For low-traffic informational sites', 'features' => ['Security patches monthly', 'Uptime monitoring', 'Quarterly accessibility scan', 'Business-hours support']],
                ['name' => 'Standard', 'tagline' => 'Most common &mdash; full coverage', 'features' => ['Weekly security patches', '24/7 uptime monitoring', 'Monthly accessibility scan', 'Editor support tickets', 'Quarterly performance review'], 'highlight' => true],
                ['name' => 'Premier', 'tagline' => 'For high-traffic, mission-critical sites', 'features' => ['Continuous patching', '24/7 on-call response', 'Continuous accessibility monitoring', 'Dedicated account manager', 'Monthly executive reporting', 'Annual penetration testing']],
            ] as $tier)
                <div
                    class="relative flex flex-col rounded-2xl border bg-white p-8 shadow-sm {{ ($tier['highlight'] ?? false) ? 'border-blue-700 ring-2 ring-blue-700' : 'border-slate-200' }}">
                    @if ($tier['highlight'] ?? false)
                        <span
                            class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-blue-700 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white">Most
                            popular</span>
                    @endif
                    <h3 class="text-xl font-bold text-slate-900">{{ $tier['name'] }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{!! $tier['tagline'] !!}</p>
                    <ul class="mt-6 flex-1 space-y-3">
                        @foreach ($tier['features'] as $feature)
                            <li class="flex items-start gap-3 text-sm text-slate-700">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-700" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('contact') }}"
                        class="mt-8 inline-flex items-center justify-center rounded-lg {{ ($tier['highlight'] ?? false) ? 'bg-blue-700 text-white hover:bg-blue-800' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }} px-4 py-2.5 text-sm font-semibold transition">
                        Discuss this tier
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

@include('pages.government.partials.related', ['exclude' => 'maintenance'])
@include('pages.government.partials.cta')
@endsection
