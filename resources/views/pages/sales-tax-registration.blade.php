@extends('layouts.landing')

@section('title', 'Sales & Use Tax Registration | Register for Sales Tax in Any State')

@section('meta')
<meta name="description" content="Register for sales and use tax in any state. We prepare and file your registration with the state so you get your permit fast — no government runaround.">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=space-grotesk:500,600,700" rel="stylesheet" />

<style>
    .sales-tax-page {
        /* Brand accent → drives Flux variant="primary" buttons + accent text on this page only */
        --color-accent: #0E9F6E;
        --color-accent-content: #0B7A55;
        --color-accent-foreground: #ffffff;

        --ink: #0C1A2B;
        --paper: #FBFAF7;
        --stamp: #D6452B;

        background: var(--paper);
    }
    .sales-tax-page .font-display { font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; }

    /* Signature: official permit stamp */
    .sales-tax-page .stamp {
        color: var(--stamp);
        border: 3px solid var(--stamp);
        letter-spacing: .12em;
        border-radius: .5rem;
        box-shadow: 0 0 0 1px rgba(214,69,43,.15);
        mix-blend-mode: multiply;
    }

    @keyframes salesTaxRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    @keyframes salesTaxSettle { from { opacity: 0; transform: rotate(-2deg) scale(1.15); } to { opacity: 1; transform: rotate(-9deg) scale(1); } }
    .sales-tax-page .rise { animation: salesTaxRise .6s cubic-bezier(.2,.7,.2,1) both; }
    .sales-tax-page .rise-2 { animation: salesTaxRise .6s cubic-bezier(.2,.7,.2,1) .08s both; }
    .sales-tax-page .rise-3 { animation: salesTaxRise .6s cubic-bezier(.2,.7,.2,1) .16s both; }
    .sales-tax-page .stamp-in { animation: salesTaxSettle .7s cubic-bezier(.2,.8,.2,1) .35s both; }

    @media (prefers-reduced-motion: reduce) {
        .sales-tax-page .rise,
        .sales-tax-page .rise-2,
        .sales-tax-page .rise-3,
        .sales-tax-page .stamp-in { animation: none; }
    }
</style>
@endsection

@section('content')
@php
    // Google Ads ad-group keyword variants via ?intent= (whitelisted, default = registration).
    // Hero headline form (Title Case, used as the green accent in the H1).
    $heroKeyword = \App\Support\PageIntent::keyword([
        'sales-tax-registration' => 'Sales Tax',
        'sales-tax-permit' => 'Sales Tax Permit',
        'sales-tax-id' => 'Sales Tax ID',
    ], 'sales-tax-registration');

    // Mid-sentence noun form (lower case, used in body copy / step headings).
    $documentNoun = \App\Support\PageIntent::keyword([
        'sales-tax-registration' => 'sales tax registration',
        'sales-tax-permit' => 'sales tax permit',
        'sales-tax-id' => 'sales tax ID',
    ], 'sales-tax-registration');

    // Document suffix (Title Case) used on the permit card heading: "Sales & Use Tax {suffix}".
    $documentSuffix = \App\Support\PageIntent::keyword([
        'sales-tax-registration' => 'Registration',
        'sales-tax-permit' => 'Permit',
        'sales-tax-id' => 'ID',
    ], 'sales-tax-registration');

    // Single source of truth for the CTA target — signup flow drives the sales-tax onboarding redirect.
    $startUrl = route('register');
    $price = '$199'; // flat fee placeholder — confirm real price
@endphp

<div class="sales-tax-page text-slate-700">

    {{-- ───────────────────────── HERO ───────────────────────── --}}
    <section class="relative overflow-hidden" style="background: var(--ink);">
        {{-- subtle grid backdrop --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
             style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px);background-size:48px 48px;"></div>
        <div class="pointer-events-none absolute -right-40 -top-40 h-[36rem] w-[36rem] rounded-full"
             style="background: radial-gradient(closest-side, rgba(14,159,110,.22), transparent);"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-16 px-6 pb-24 pt-20 sm:pt-24 lg:grid-cols-[1.05fr_.95fr] lg:pb-28 lg:pt-28">

            {{-- copy --}}
            <div>
                <div class="rise mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/80 ring-1 ring-white/15">
                    <flux:icon name="shield-check" variant="micro" class="size-3.5" style="color: var(--color-accent)" />
                    Prepared &amp; filed by compliance specialists
                </div>

                <h1 class="rise font-display text-4xl font-bold leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Register for<br>
                    <span data-hero-keyword style="color: var(--color-accent)">{{ $heroKeyword }}</span>
                </h1>

                <p class="rise-2 mt-6 max-w-md text-lg leading-relaxed text-white/70">
                    Answer a few questions about your business. We prepare your state
                    registration, file it for you, and send back your permit and account
                    number.
                </p>

                <div class="rise-3 mt-9">
                    <flux:button href="{{ $startUrl }}" variant="primary"
                                 icon:trailing="arrow-right"
                                 class="w-full !h-14 !text-base font-semibold shadow-lg shadow-emerald-900/30">
                        Start my registration
                    </flux:button>
                </div>

                {{-- trust row --}}
                <div class="rise-3 mt-12 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-white/60">
                    <span class="flex items-center gap-2">
                        <flux:icon name="check-circle" variant="mini" class="size-4" style="color: var(--color-accent)" />
                        All applicable states
                    </span>
                    <span class="flex items-center gap-2">
                        <flux:icon name="check-circle" variant="mini" class="size-4" style="color: var(--color-accent)" />
                        Multi-state in one go
                    </span>
                    <span class="flex items-center gap-2">
                        <flux:icon name="check-circle" variant="mini" class="size-4" style="color: var(--color-accent)" />
                        Document review included
                    </span>
                </div>
            </div>

            {{-- signature: the outcome they're buying — an issued permit --}}
            <div class="rise-2 relative mx-auto w-full max-w-sm">
                <div class="relative rounded-2xl bg-[var(--paper)] p-7 shadow-2xl ring-1 ring-black/5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                        <div class="font-display text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            Department of Revenue
                        </div>
                        <flux:icon name="building-office-2" variant="mini" class="size-5 text-slate-300" />
                    </div>

                    <h3 class="font-display mt-5 text-lg font-bold leading-tight text-[var(--ink)]">
                        Sales &amp; Use Tax {{ $documentSuffix }}
                    </h3>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Business</dt>
                            <dd class="font-medium text-slate-700">Acme Contracting LLC</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Permit no.</dt>
                            <dd class="font-mono font-medium text-slate-700">ST-0042-117835</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Effective</dt>
                            <dd class="font-medium text-slate-700">Today</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Status</dt>
                            <dd class="font-semibold" style="color: var(--color-accent)">Active</dd>
                        </div>
                    </dl>

                    {{-- the stamp --}}
                    <div class="stamp stamp-in absolute -right-4 bottom-6 rotate-[-9deg] px-4 py-2 font-display text-lg font-bold uppercase">
                        Registered
                    </div>
                </div>

                {{-- floating reassurance chip --}}
                <div class="absolute -left-5 -top-5 hidden items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-lg ring-1 ring-black/5 sm:flex">
                    <flux:icon name="bolt" variant="mini" class="size-4" style="color: var(--stamp)" />
                    Filed for you
                </div>
            </div>
        </div>
    </section>

    {{-- ───────────────────────── TRUST BAR ───────────────────────── --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid max-w-6xl grid-cols-3 divide-x divide-slate-100 px-6 py-8 text-center">
            @foreach ([
                ['number' => 'All', 'label' => 'Applicable states'],
                ['number' => '10 min', 'label' => 'Average to apply'],
                ['number' => '100%', 'label' => 'Filings reviewed'],
            ] as $stat)
                <div class="px-3">
                    <div class="font-display text-2xl font-bold text-[var(--ink)] sm:text-3xl">{{ $stat['number'] }}</div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ───────────────────────── HOW IT WORKS ───────────────────────── --}}
    <section id="how" class="mx-auto max-w-6xl px-6 py-24">
        <div class="max-w-2xl">
            <div class="font-display text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--color-accent)">How it works</div>
            <h2 class="font-display mt-3 text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">
                Three steps to a valid {{ $documentNoun }}
            </h2>
            <p class="mt-4 text-lg text-slate-500">
                You do the easy part. We do the part that trips everyone up.
            </p>
        </div>

        <div class="mt-14 grid gap-8 md:grid-cols-3">
            @foreach ([
                ['n' => '01', 'icon' => 'pencil-square', 'title' => 'Tell us about your business', 'body' => 'A short guided form. Entity type, where you sell, what you sell. No tax jargon, no blank state PDFs.'],
                ['n' => '02', 'icon' => 'document-check', 'title' => 'We prepare &amp; file', 'body' => 'A specialist reviews your details, completes the state registration correctly, and submits it on your behalf.'],
                ['n' => '03', 'icon' => 'check-badge', 'title' => 'Get your ' . $documentNoun, 'body' => 'You receive your permit and account number. Everything you need to collect and remit sales tax legally.'],
            ] as $step)
                <div class="group relative rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl hover:shadow-slate-200/60">
                    <div class="font-display text-5xl font-bold text-slate-100 transition group-hover:text-emerald-100">{{ $step['n'] }}</div>
                    <div class="-mt-6 mb-5 inline-flex size-11 items-center justify-center rounded-xl bg-[var(--ink)]">
                        <flux:icon name="{{ $step['icon'] }}" variant="mini" class="size-5 text-white" />
                    </div>
                    <h3 class="font-display text-lg font-bold text-[var(--ink)]">{!! $step['title'] !!}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $step['body'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-12">
            <flux:button href="{{ $startUrl }}" variant="primary" icon:trailing="arrow-right"
                         class="!h-12 !px-7 font-semibold">
                Start my registration
            </flux:button>
        </div>
    </section>

    {{-- ───────────────────────── WHY eREGISTER ───────────────────────── --}}
    <section id="why" class="bg-white">
        <div class="mx-auto max-w-6xl px-6 py-24">
            <div class="grid gap-16 lg:grid-cols-[.9fr_1.1fr] lg:items-center">
                <div>
                    <div class="font-display text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--color-accent)">Why eRegister</div>
                    <h2 class="font-display mt-3 text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">
                        Skip the state portals.<br>Skip the guesswork.
                    </h2>
                    <p class="mt-5 text-lg leading-relaxed text-slate-500">
                        Every state does sales tax registration differently. Different forms,
                        different logins, different traps. Get one wrong and you're looking at
                        delays or penalties. We've done it thousands of times, so you don't have to learn it once.
                    </p>
                    <div class="mt-8 rounded-xl border-l-4 bg-slate-50 p-5" style="border-color: var(--stamp)">
                        <div class="flex items-start gap-3">
                            <flux:icon name="clock" variant="mini" class="mt-0.5 size-5 shrink-0" style="color: var(--stamp)" />
                            <p class="text-sm text-slate-600">
                                <span class="font-semibold text-[var(--ink)]">Already selling?</span>
                                Registering late can mean back taxes and penalties. The sooner you file, the less it costs.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ([
                        ['icon' => 'map', 'title' => 'Any state, one process', 'body' => 'Register in a single state or many at once. Same simple flow either way.'],
                        ['icon' => 'shield-check', 'title' => 'Reviewed before filing', 'body' => 'A specialist checks your details against state rules so it lands right the first time.'],
                        ['icon' => 'banknotes', 'title' => 'Flat, upfront fee', 'body' => 'Know the cost before you start. No metered charges, no surprise add-ons.'],
                        ['icon' => 'bolt', 'title' => 'Done for you', 'body' => 'No portals, no PINs to chase, no waiting on hold with a state agency.'],
                    ] as $f)
                        <div class="rounded-2xl border border-slate-200 p-6">
                            <flux:icon name="{{ $f['icon'] }}" variant="outline" class="size-6" style="color: var(--color-accent)" />
                            <h3 class="font-display mt-4 text-base font-bold text-[var(--ink)]">{{ $f['title'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $f['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ───────────────────────── PRICING ───────────────────────── --}}
    <section id="pricing" class="bg-white">
        <div class="mx-auto max-w-3xl px-6 py-24">
            <div class="text-center">
                <div class="font-display text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--color-accent)">Pricing</div>
                <h2 class="font-display mt-3 text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">
                    Simple, flat-rate pricing
                </h2>
                <p class="mt-3 text-slate-500">One price per state. No metered charges, no surprise add-ons.</p>
            </div>

            <div class="mx-auto mt-12 max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-[var(--paper)] shadow-xl shadow-slate-200/60">
                <div class="border-b border-slate-200 px-8 py-10 text-center">
                    <div class="font-display text-sm font-semibold uppercase tracking-wide text-slate-400">Per state</div>
                    <div class="mt-3 flex items-baseline justify-center gap-1">
                        <span class="font-display text-5xl font-bold text-[var(--ink)]">{{ $price }}</span>
                        <span class="text-base font-medium text-slate-500">/ state</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">Prepared &amp; filed by a compliance specialist.</p>
                </div>
                <div class="px-8 py-8">
                    <ul class="space-y-3 text-sm text-slate-600">
                        @foreach ([
                            'Guided application — no state portals',
                            'Document review before filing',
                            'Filed with the state on your behalf',
                            'Your permit and account number returned to you',
                        ] as $included)
                            <li class="flex items-start gap-3">
                                <flux:icon name="check-circle" variant="mini" class="mt-0.5 size-5 shrink-0" style="color: var(--color-accent)" />
                                <span>{{ $included }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-8">
                        <flux:button href="{{ $startUrl }}" variant="primary" icon:trailing="arrow-right"
                                     class="w-full !h-12 font-semibold">
                            Start my registration
                        </flux:button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ───────────────────────── FAQ ───────────────────────── --}}
    <section id="faq" class="mx-auto max-w-3xl px-6 py-24">
        <div class="text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">Common questions</h2>
            <p class="mt-3 text-slate-500">Everything you need to know before you start.</p>
        </div>

        <div class="mt-12">
            <flux:accordion>
                <flux:accordion.item>
                    <flux:accordion.heading>What is sales &amp; use tax registration?</flux:accordion.heading>
                    <flux:accordion.content>
                        It's how a state authorizes your business to collect and remit sales tax.
                        Once registered, you receive a permit and account number. Required before
                        you legally collect tax from customers in that state.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Do I actually need to register?</flux:accordion.heading>
                    <flux:accordion.content>
                        If you sell taxable goods or services, or you've crossed a state's economic
                        nexus threshold, you generally must register before collecting tax. Not sure?
                        Start the form and we'll help you figure out where you have an obligation.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>How long does it take?</flux:accordion.heading>
                    <flux:accordion.content>
                        The application takes about ten minutes. Filing and state processing times
                        vary. Many states issue an account number quickly, while others take a few
                        business days. We keep you posted at each step.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Can you register me in multiple states?</flux:accordion.heading>
                    <flux:accordion.content>
                        Yes. Select every state you need during the application and we'll prepare and
                        file each registration. The flat fee applies per state.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>What do I receive when it's done?</flux:accordion.heading>
                    <flux:accordion.content>
                        Your sales &amp; use tax permit and account number for each state, plus a copy
                        of your filing for your records — everything you need to start collecting and
                        remitting correctly.
                    </flux:accordion.content>
                </flux:accordion.item>
            </flux:accordion>
        </div>
    </section>

    {{-- ───────────────────────── FINAL CTA ───────────────────────── --}}
    <section class="relative overflow-hidden" style="background: var(--ink);">
        <div class="pointer-events-none absolute -left-32 bottom-0 h-96 w-96 rounded-full"
             style="background: radial-gradient(closest-side, rgba(14,159,110,.25), transparent);"></div>
        <div class="relative mx-auto max-w-3xl px-6 py-20 text-center">
            <h2 class="font-display text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Ready to get registered?
            </h2>
            <p class="mx-auto mt-4 max-w-md text-lg text-white/70">
                Answer a few questions and we'll take it from there. Your permit is closer than you think.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <flux:button href="{{ $startUrl }}" variant="primary" icon:trailing="arrow-right"
                             class="!h-14 !px-8 !text-base font-semibold shadow-lg shadow-emerald-900/30">
                    Start my registration
                </flux:button>
                <span class="text-sm text-white/55">Create your account · We'll take it from there</span>
            </div>
        </div>
    </section>

</div>
@endsection
