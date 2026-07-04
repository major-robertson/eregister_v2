@extends('layouts.landing')

@section('title', 'Resale Certificates | Unlimited Signed Certificates for Every State')

@section('meta')
<meta name="description" content="Generate signed resale certificates on official state forms in minutes. Unlimited certificates for every applicable state at one flat yearly price.">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=caveat:600|space-grotesk:500,600,700" rel="stylesheet" />

<style>
    .resale-page {
        /* Brand accent → drives Flux variant="primary" buttons + accent text on this page only */
        --color-accent: #0E9F6E;
        --color-accent-content: #0B7A55;
        --color-accent-foreground: #ffffff;

        --ink: #0B2420;
        --paper: #F8FAF8;
        --pen: #1D4ED8;
        --cta: #DC2626;
        --cta-hover: #B91C1C;

        background: var(--paper);
    }
    .resale-page .btn-cta { background-color: var(--cta) !important; }
    .resale-page .btn-cta:hover { background-color: var(--cta-hover) !important; }
    .resale-page .font-display { font-family: 'Space Grotesk', ui-sans-serif, system-ui, sans-serif; }
    .resale-page .font-signature { font-family: 'Caveat', cursive; }

    @keyframes resaleRise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    @keyframes resaleSign { from { opacity: 0; transform: translateX(-10px) rotate(-2deg); } to { opacity: 1; transform: rotate(-2deg); } }
    .resale-page .rise { animation: resaleRise .6s cubic-bezier(.2,.7,.2,1) both; }
    .resale-page .rise-2 { animation: resaleRise .6s cubic-bezier(.2,.7,.2,1) .08s both; }
    .resale-page .rise-3 { animation: resaleRise .6s cubic-bezier(.2,.7,.2,1) .16s both; }
    .resale-page .sign-in { animation: resaleSign .8s cubic-bezier(.2,.8,.2,1) .45s both; }

    @media (prefers-reduced-motion: reduce) {
        .resale-page .rise,
        .resale-page .rise-2,
        .resale-page .rise-3,
        .resale-page .sign-in { animation: none; }
    }
</style>
@endsection

@section('content')
@php
    // Google Ads ad-group keyword variants via ?intent= (whitelisted, default = resale-certificates).
    // Hero headline form (Title Case, used as the green accent in the H1).
    $heroKeyword = \App\Support\PageIntent::keyword([
        'resale-certificates' => 'Resale Certificate',
        'resale-license' => 'Resale License',
        'reseller-permit' => 'Reseller Permit',
        'wholesale-license' => 'Wholesale License',
        'tax-exempt-certificate' => 'Tax Exempt Certificate',
    ], 'resale-certificates');

    // Single source of truth for the CTA target — the dashboard doubles as
    // the pricing/subscribe page for signed-in users.
    $startUrl = auth()->check() ? route('resale-cert.dashboard') : route('register');

    // Live price from the catalog (ResaleCertPriceSeeder), fallback if unseeded.
    try {
        $price = \App\Models\Price::resolve(
            config('resale_cert.price_family'),
            config('resale_cert.price_key'),
            'default',
            'subscription',
        );
        $priceAmount = '$'.number_format($price->amount_cents / 100);
    } catch (\Throwable) {
        $priceAmount = '$297';
    }
@endphp

<div class="resale-page text-slate-700">

    {{-- ───────────────────────── HERO ───────────────────────── --}}
    <section class="relative overflow-hidden" style="background: var(--ink);">
        {{-- subtle grid backdrop --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.06]"
             style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px);background-size:48px 48px;"></div>
        <div class="pointer-events-none absolute -right-40 -top-40 h-[36rem] w-[36rem] rounded-full"
             style="background: radial-gradient(closest-side, rgba(14,159,110,.25), transparent);"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-16 px-6 pb-24 pt-20 sm:pt-24 lg:grid-cols-[1.05fr_.95fr] lg:pb-28 lg:pt-28">

            {{-- copy --}}
            <div>
                <div class="rise mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/80 ring-1 ring-white/15">
                    <flux:icon name="document-check" variant="micro" class="size-3.5" style="color: var(--color-accent)" />
                    Unlimited certificates &middot; All applicable states
                </div>

                <h1 class="rise font-display text-4xl font-bold leading-[1.05] tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Generate your<br>
                    <span data-hero-keyword style="color: var(--color-accent)">{{ $heroKeyword }}</span>
                </h1>

                <p class="rise-2 mt-6 max-w-md text-lg leading-relaxed text-white/70">
                    Enter your business details once. Get signed, vendor-ready
                    resale certificates on official state forms. Any state, any
                    vendor, as many as you need.
                </p>

                <div class="rise-3 mt-9">
                    <flux:button href="{{ $startUrl }}" variant="primary"
                                 icon:trailing="arrow-right"
                                 class="btn-cta w-full max-w-md !h-16 !text-lg font-semibold shadow-lg shadow-red-900/30">
                        Get started
                    </flux:button>
                </div>
                <p class="rise-3 mt-4 text-sm text-white/55">
                    {{ $priceAmount }}/year flat &middot; No per-certificate fees &middot; Cancel anytime
                </p>

                {{-- trust row --}}
                <div class="rise-3 mt-10 flex flex-wrap items-center gap-x-7 gap-y-3 text-sm text-white/60">
                    <span class="flex items-center gap-2">
                        <flux:icon name="check-circle" variant="mini" class="size-4" style="color: var(--color-accent)" />
                        Official state forms
                    </span>
                    <span class="flex items-center gap-2">
                        <flux:icon name="check-circle" variant="mini" class="size-4" style="color: var(--color-accent)" />
                        E-signature included
                    </span>
                    <span class="flex items-center gap-2">
                        <flux:icon name="check-circle" variant="mini" class="size-4" style="color: var(--color-accent)" />
                        Instant PDF download
                    </span>
                </div>
            </div>

            {{-- signature: the outcome they're buying — a signed, vendor-ready certificate --}}
            <div class="rise-2 relative mx-auto w-full max-w-sm">
                {{-- stacked forms behind, suggesting one certificate per state/vendor --}}
                <div class="absolute inset-0 -rotate-3 rounded-2xl bg-white/10 ring-1 ring-white/10"></div>
                <div class="absolute inset-0 rotate-2 rounded-2xl bg-white/5 ring-1 ring-white/10"></div>

                <div class="relative rounded-2xl bg-[var(--paper)] p-7 shadow-2xl ring-1 ring-black/5">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                        <div class="font-display text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                            Sales &amp; Use Tax
                        </div>
                        <flux:icon name="document-check" variant="mini" class="size-5 text-slate-300" />
                    </div>

                    <h3 class="font-display mt-5 text-lg font-bold leading-tight text-[var(--ink)]">
                        Resale Certificate
                    </h3>

                    <dl class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Purchaser</dt>
                            <dd class="font-medium text-slate-700">Acme Trading LLC</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Permit no.</dt>
                            <dd class="font-mono font-medium text-slate-700">32-0451-1178</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Vendor</dt>
                            <dd class="font-medium text-slate-700">Summit Wholesale Co.</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-400">Status</dt>
                            <dd class="font-semibold" style="color: var(--color-accent)">Tax exempt</dd>
                        </div>
                    </dl>

                    {{-- the signature --}}
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        <div class="text-[10px] font-medium uppercase tracking-wider text-slate-400">Authorized signature</div>
                        <div class="sign-in font-signature mt-1 -rotate-2 text-3xl" style="color: var(--pen)">
                            Jordan Avery
                        </div>
                    </div>

                    <div class="absolute -right-4 bottom-8 flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold text-white shadow-lg" style="background: var(--color-accent)">
                        <flux:icon name="check-badge" variant="micro" class="size-4" />
                        E-signed
                    </div>
                </div>

                {{-- floating reassurance chip --}}
                <div class="absolute -left-5 -top-5 hidden items-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-medium text-slate-600 shadow-lg ring-1 ring-black/5 sm:flex">
                    <flux:icon name="bolt" variant="mini" class="size-4" style="color: var(--color-accent)" />
                    PDF ready in minutes
                </div>
            </div>
        </div>
    </section>

    {{-- ───────────────────────── TRUST BAR ───────────────────────── --}}
    <section class="border-b border-slate-200 bg-white">
        <div class="mx-auto grid max-w-6xl grid-cols-3 divide-x divide-slate-100 px-6 py-8 text-center">
            @foreach ([
                ['number' => 'All', 'label' => 'Applicable states covered'],
                ['number' => 'Unlimited', 'label' => 'Certificates included'],
                ['number' => '$0', 'label' => 'Per-certificate fees'],
            ] as $stat)
                <div class="px-3">
                    <div class="font-display text-2xl font-bold text-[var(--ink)] sm:text-3xl">{{ $stat['number'] }}</div>
                    <div class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ───────────────────────── WHAT IT IS / THE MATH ───────────────────────── --}}
    <section id="what" class="mx-auto max-w-6xl px-6 py-24">
        <div class="grid gap-16 lg:grid-cols-[1.1fr_.9fr] lg:items-center">
            <div>
                <div class="font-display text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--color-accent)">Why it matters</div>
                <h2 class="font-display mt-3 text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">
                    Stop paying sales tax<br>on inventory
                </h2>
                <p class="mt-5 text-lg leading-relaxed text-slate-500">
                    A resale certificate tells your supplier you're buying goods to resell,
                    so they sell to you tax-free and you collect the tax from your customers
                    instead. Every state has its own form and rules. Vendors keep your
                    certificate on file, and most ask for a fresh one per vendor, per state.
                </p>
                <div class="mt-8 space-y-4">
                    @foreach ([
                        ['icon' => 'shopping-cart', 'title' => 'Buying wholesale', 'body' => 'Without a certificate on file, distributors charge you sales tax upfront. That\'s cash you only recover when the goods finally sell.'],
                        ['icon' => 'building-storefront', 'title' => 'Reselling goods', 'body' => 'Retailers, e-commerce sellers, and dropshippers registered for sales tax can buy inventory exempt, with the right certificate for each vendor.'],
                    ] as $need)
                        <div class="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-[var(--ink)]">
                                <flux:icon name="{{ $need['icon'] }}" variant="mini" class="size-5 text-white" />
                            </div>
                            <div>
                                <h3 class="font-display text-base font-bold text-[var(--ink)]">{{ $need['title'] }}</h3>
                                <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $need['body'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- the math card --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/60">
                <div class="flex items-center gap-3">
                    <flux:icon name="calculator" variant="outline" class="size-6" style="color: var(--color-accent)" />
                    <h3 class="font-display text-lg font-bold text-[var(--ink)]">What one certificate saves</h3>
                </div>
                <div class="mt-6 space-y-4">
                    <div class="rounded-xl bg-slate-50 p-5">
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Without a certificate</div>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span class="text-sm text-slate-500">$25,000 inventory &times; 8% tax</span>
                            <span class="font-display text-2xl font-bold text-rose-600">&minus;$2,000</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Paid upfront to your supplier.</p>
                    </div>
                    <div class="rounded-xl p-5 ring-2" style="background: rgba(14,159,110,.06); --tw-ring-color: var(--color-accent)">
                        <div class="text-xs font-semibold uppercase tracking-wide" style="color: var(--color-accent-content)">With a resale certificate</div>
                        <div class="mt-2 flex items-baseline justify-between">
                            <span class="text-sm text-slate-500">Same purchase, tax exempt</span>
                            <span class="font-display text-2xl font-bold" style="color: var(--color-accent-content)">$0</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">You collect the tax from your customers at sale.</p>
                    </div>
                </div>
                <p class="mt-6 text-sm text-slate-500">
                    A single wholesale order can save more than the
                    <span class="font-semibold text-[var(--ink)]">{{ $priceAmount }}/year</span> subscription,
                    and every certificate after that is included.
                </p>
            </div>
        </div>
    </section>

    {{-- ───────────────────────── HOW IT WORKS ───────────────────────── --}}
    <section id="how" class="bg-white">
        <div class="mx-auto max-w-6xl px-6 py-24">
            <div class="max-w-2xl">
                <div class="font-display text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--color-accent)">How it works</div>
                <h2 class="font-display mt-3 text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">
                    Set up once. Generate forever.
                </h2>
                <p class="mt-4 text-lg text-slate-500">
                    The slow part of a resale certificate is filling out the same details
                    on a different state form every time. We keep your details. You just pick the form.
                </p>
            </div>

            <div class="mt-14 grid gap-8 md:grid-cols-3">
                @foreach ([
                    ['n' => '01', 'icon' => 'pencil-square', 'title' => 'Set up your resale profile', 'body' => 'Business details, sales tax registrations, and your adopted e-signature, entered once and reused on every certificate.'],
                    ['n' => '02', 'icon' => 'map', 'title' => 'Pick a state and vendor', 'body' => 'Choose the state form (or an MTC / SST multi-state form) and the vendor it\'s for. We fill the official form automatically.'],
                    ['n' => '03', 'icon' => 'arrow-down-tray', 'title' => 'Download the signed PDF', 'body' => 'Your certificate is e-signed and ready to send. Every certificate stays in your workspace to reissue or re-download anytime.'],
                ] as $step)
                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-7 transition hover:-translate-y-1 hover:border-slate-300 hover:shadow-xl hover:shadow-slate-200/60">
                        <div class="font-display text-5xl font-bold text-slate-100 transition group-hover:text-emerald-100">{{ $step['n'] }}</div>
                        <div class="-mt-6 mb-5 inline-flex size-11 items-center justify-center rounded-xl bg-[var(--ink)]">
                            <flux:icon name="{{ $step['icon'] }}" variant="mini" class="size-5 text-white" />
                        </div>
                        <h3 class="font-display text-lg font-bold text-[var(--ink)]">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $step['body'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12">
                <flux:button href="{{ $startUrl }}" variant="primary" icon:trailing="arrow-right"
                             class="btn-cta !h-12 !px-7 font-semibold">
                    Get started
                </flux:button>
            </div>
        </div>
    </section>

    {{-- ───────────────────────── EVERYTHING INCLUDED ───────────────────────── --}}
    <section id="features" class="mx-auto max-w-6xl px-6 py-24">
        <div class="max-w-2xl">
            <div class="font-display text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--color-accent)">Everything included</div>
            <h2 class="font-display mt-3 text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">
                Built for businesses that buy wholesale every week
            </h2>
        </div>

        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['icon' => 'document-check', 'title' => 'Official state forms', 'body' => 'Certificates generated on each state\'s own form, covering every applicable state. The only states missing are the ones with no sales tax.'],
                ['icon' => 'document-duplicate', 'title' => 'MTC & SST multi-state forms', 'body' => 'The Multistate Tax Commission and Streamlined Sales Tax uniform certificates cover dozens of states with a single document.'],
                ['icon' => 'pencil', 'title' => 'E-signature built in', 'body' => 'Adopt your signature once. Every certificate comes out signed and vendor-ready. No printing, signing, and rescanning.'],
                ['icon' => 'users', 'title' => 'Vendor manager', 'body' => 'Keep your vendors on file and issue a certificate per vendor in a couple of clicks. New supplier? Two minutes, done.'],
                ['icon' => 'bell-alert', 'title' => 'Expiration reminders', 'body' => 'Some states expire certificates every few years. We track the dates and email you before anything lapses.'],
                ['icon' => 'clock', 'title' => 'Instant PDFs, stored history', 'body' => 'Download the moment you generate. Every certificate stays in your workspace to re-download or reissue anytime.'],
            ] as $f)
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <flux:icon name="{{ $f['icon'] }}" variant="outline" class="size-6" style="color: var(--color-accent)" />
                    <h3 class="font-display mt-4 text-base font-bold text-[var(--ink)]">{{ $f['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $f['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ───────────────────────── PRICING ───────────────────────── --}}
    <section id="pricing" class="bg-white">
        <div class="mx-auto max-w-3xl px-6 py-24">
            <div class="text-center">
                <div class="font-display text-sm font-semibold uppercase tracking-[0.16em]" style="color: var(--color-accent)">Pricing</div>
                <h2 class="font-display mt-3 text-3xl font-bold tracking-tight text-[var(--ink)] sm:text-4xl">
                    One flat price. Zero per-certificate fees.
                </h2>
                <p class="mt-3 text-slate-500">Every state, every vendor, every renewal. One subscription covers it all.</p>
            </div>

            <div class="mx-auto mt-12 max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-[var(--paper)] shadow-xl shadow-slate-200/60">
                <div class="border-b border-slate-200 px-8 py-10 text-center">
                    <div class="font-display text-sm font-semibold uppercase tracking-wide text-slate-400">Resale Certificate Generator</div>
                    <div class="mt-3 flex items-baseline justify-center gap-1">
                        <span class="font-display text-5xl font-bold text-[var(--ink)]">{{ $priceAmount }}</span>
                        <span class="text-base font-medium text-slate-500">/ year</span>
                    </div>
                    <p class="mt-3 text-sm text-slate-500">Unlimited certificates for your business.</p>
                </div>
                <div class="px-8 py-8">
                    <ul class="space-y-3 text-sm text-slate-600">
                        @foreach ([
                            'Unlimited certificate generation with no per-certificate fees',
                            'Every applicable state form, plus MTC & SST multi-state',
                            'Built-in e-signature on every certificate',
                            'Vendor manager & full certificate history',
                            'Expiration tracking with email reminders',
                            'Instant PDF downloads',
                        ] as $included)
                            <li class="flex items-start gap-3">
                                <flux:icon name="check-circle" variant="mini" class="mt-0.5 size-5 shrink-0" style="color: var(--color-accent)" />
                                <span>{{ $included }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-8">
                        <flux:button href="{{ $startUrl }}" variant="primary" icon:trailing="arrow-right"
                                     class="btn-cta w-full !h-14 !text-base font-semibold">
                            Get started
                        </flux:button>
                    </div>
                    <p class="mt-4 text-center text-xs text-slate-400">
                        Renews at {{ $priceAmount }}/year. Cancel anytime.
                    </p>
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
                    <flux:accordion.heading>Is it really unlimited?</flux:accordion.heading>
                    <flux:accordion.content>
                        Yes. One flat {{ $priceAmount }}/year covers every certificate you generate:
                        every state, every vendor, every reissue. There are no per-certificate,
                        per-state, or per-download fees.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Which states are covered?</flux:accordion.heading>
                    <flux:accordion.content>
                        All applicable states: every state that has a sales tax, plus Washington,
                        D.C., along with the MTC and SST uniform multi-state certificates. Delaware,
                        Montana, New Hampshire, and Oregon have no state sales tax, so no resale
                        certificate is needed there.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>What's the difference between a resale certificate and a sales tax permit?</flux:accordion.heading>
                    <flux:accordion.content>
                        A sales tax permit (seller's permit) is issued by the state and authorizes
                        you to collect sales tax from customers. A resale certificate is a document
                        you provide to vendors when buying goods for resale. It tells them you're
                        exempt from paying sales tax on that purchase because you'll collect it from
                        your buyers. You need a valid sales tax permit before you can issue a resale
                        certificate. Don't have one yet? We handle
                        <a href="{{ route('sales-tax-registration') }}" class="font-medium underline" style="color: var(--color-accent-content)">sales tax registration</a> too.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>How long is a resale certificate valid?</flux:accordion.heading>
                    <flux:accordion.content>
                        Validity varies by state. Some states consider resale certificates valid
                        indefinitely as long as your sales tax permit remains active. Others require
                        renewal every 1 to 3 years or have expiration dates. If your permit lapses or is
                        revoked, your resale certificate is no longer valid. We track expiration
                        dates and email you before a certificate lapses.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Will vendors in all 50 states accept my resale certificate?</flux:accordion.heading>
                    <flux:accordion.content>
                        Most vendors across the country accept properly formatted resale
                        certificates. Some states participate in the Streamlined Sales Tax (SST)
                        multistate certificate, which many vendors recognize. A few states require
                        their own forms for in-state purchases, which is why we generate certificates
                        on each state's official form, so you always have the document a vendor expects.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Can I use a resale certificate for personal purchases?</flux:accordion.heading>
                    <flux:accordion.content>
                        No. Resale certificates may only be used for purchases you intend to resell.
                        Using one for personal or business consumption (e.g., office supplies you use
                        yourself) is tax fraud and can result in penalties, audits, and loss of your
                        sales tax permit. Only use your resale certificate when buying inventory or
                        goods you will sell to customers.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Do I need a resale certificate for services?</flux:accordion.heading>
                    <flux:accordion.content>
                        Resale certificates typically apply to tangible personal property (goods) you
                        buy for resale. For services, rules vary by state. Some allow exemption when
                        you're reselling a service, others treat services differently. If you buy
                        goods that become part of a service (e.g., materials used in a repair), a
                        resale certificate may apply.
                    </flux:accordion.content>
                </flux:accordion.item>

                <flux:accordion.item>
                    <flux:accordion.heading>Can I cancel my subscription?</flux:accordion.heading>
                    <flux:accordion.content>
                        Yes. The subscription renews yearly and can be canceled anytime. Certificates
                        you've already generated and sent to vendors remain valid per their state's rules.
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
                Ready to buy wholesale tax-free?
            </h2>
            <p class="mx-auto mt-4 max-w-md text-lg text-white/70">
                Set up your resale profile once, and your next certificate is minutes away.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <flux:button href="{{ $startUrl }}" variant="primary" icon:trailing="arrow-right"
                             class="btn-cta !h-14 !px-8 !text-base font-semibold shadow-lg shadow-red-900/30">
                    Get started
                </flux:button>
                <span class="text-sm text-white/55">{{ $priceAmount }}/year &middot; Unlimited certificates</span>
            </div>
        </div>
    </section>

</div>
@endsection
