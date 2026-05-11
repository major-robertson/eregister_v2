@extends('layouts.government')

@section('title', 'Citizen & Staff Portals for Government | Self-Service Web Applications')

@section('meta')
<meta name="description"
    content="Custom citizen self-service portals and internal staff portals for government agencies. Permits, licensing, payments, requests, and intranet workflows.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-slate-950 via-blue-950 to-slate-900 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
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

@include('pages.government.partials.related', ['exclude' => 'portals'])
@include('pages.government.partials.cta')
@endsection
