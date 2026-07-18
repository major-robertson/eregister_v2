@extends('layouts.landing')

@section('title', 'Lien Waiver Generator | Free Forms for All 50 States')

@section('meta')
<link rel="canonical" href="{{ route('liens.lien-waivers') }}" />
<meta name="description" content="Generate free lien waiver forms with the correct form for all 50 states, including the exact statutory text where the law prescribes one. Conditional, unconditional, progress, and final waivers with e-signature and automatic reminders.">
@endsection

@section('content')
@php
    // The twelve states whose data files carry prescribed statutory language:
    // ten with true statutory waiver forms plus MA/MO, whose statutes impose
    // special rules rather than a general form. Names and statute cites are
    // pulled from the registry so this strip never drifts from the product.
    $statutoryStrip = ['AZ', 'CA', 'FL', 'GA', 'MI', 'MS', 'NV', 'TX', 'UT', 'WY', 'MA', 'MO'];
    $specialRuleStates = ['MA', 'MO'];
    $freeSaves = config('lien_waivers.free_saved_waivers_per_month', 4);
    $monthlyPrice = number_format(config('lien_waivers.prices.monthly.amount_cents', 9900) / 100);
    $yearlyPrice = number_format(config('lien_waivers.prices.yearly.amount_cents', 99000) / 100);
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            Free to generate, no credit card required
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            Lien Waiver Generator<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Correct Forms for All 50 States</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">
            Generate conditional, unconditional, progress, and final lien waivers with the right form for your project's state, including the exact statutory text where the law prescribes one. Download free, or send for e-signature and get the signed copy stored automatically.
        </p>
        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Create a free lien waiver
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
            <a href="{{ route('liens.lien-waivers.pricing') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-600 px-8 py-4 text-base font-medium text-zinc-300 transition hover:border-zinc-500 hover:text-white">
                See pricing
            </a>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">How it works</h2>
            <p class="mt-3 text-zinc-600">From blank form to signed waiver, without chasing anyone down</p>
        </div>
        <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">1</div>
                <h3 class="mt-6 text-lg font-semibold text-zinc-900">Generate for free</h3>
                <p class="mt-2 text-zinc-600">Pick your state and waiver type. We fill in the correct form (statutory text where required) and you download the PDF free. No watermark.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">2</div>
                <h3 class="mt-6 text-lg font-semibold text-zinc-900">Send for e-signature</h3>
                <p class="mt-2 text-zinc-600">Send the waiver for electronic signature in the states that allow it. The signer clicks a secure link, no account or download needed on their end.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">3</div>
                <h3 class="mt-6 text-lg font-semibold text-zinc-900">Signed copy stored</h3>
                <p class="mt-2 text-zinc-600">The signed waiver is stored on your project with a tamper-evident audit certificate, and both parties automatically receive their copies.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-white">4</div>
                <h3 class="mt-6 text-lg font-semibold text-zinc-900">Automatic reminders</h3>
                <p class="mt-2 text-zinc-600">Unsigned waivers get automatic follow-up reminders until they're signed, so you never have to nag a sub, vendor, or GC yourself.</p>
            </div>
        </div>
    </div>
</section>

{{-- The two directions --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Lien waivers flow both ways</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Whether you're signing waivers to get your check released or collecting them before you cut one, the same tool handles both sides of the exchange.</p>
        </div>
        <div class="mt-12 grid gap-8 lg:grid-cols-2">
            <div class="rounded-2xl border border-zinc-200 bg-white p-8">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Need to send a waiver to get paid</h3>
                <p class="mt-3 text-zinc-600">
                    Subcontractors and suppliers: the GC or owner won't release your check until a signed waiver lands on their desk. Generate the correct one for the payment and the project's state, sign it electronically where allowed, and send it back in minutes, not days.
                </p>
                <ul class="mt-4 space-y-2 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-amber-500">&#10003;</span> The right conditional or unconditional form for the payment</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-amber-500">&#10003;</span> Statutory wording where the state requires it</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-amber-500">&#10003;</span> Your copy of every waiver you've signed, in one place</li>
                </ul>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-8">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-zinc-900">Need to collect waivers from subs &amp; vendors</h3>
                <p class="mt-3 text-zinc-600">
                    General contractors and owners: every payment you make should come back with a signed waiver, or you're paying twice for the same lien exposure. Request waivers from everyone you pay, watch who has and hasn't signed, and keep every signed copy on the project.
                </p>
                <ul class="mt-4 space-y-2 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-blue-500">&#10003;</span> Send signature requests to subs, vendors, and suppliers</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-blue-500">&#10003;</span> Automatic reminders chase the stragglers for you</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-blue-500">&#10003;</span> Signed copies organized by project, ready for the lender</li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Statutory states strip --}}
<section class="bg-zinc-900 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-white">12 states prescribe the form: we generate the exact statutory text</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-400">
                In these states a lien waiver only works if it follows the wording the legislature wrote. Use the wrong form and the waiver can be invalid, or waive more than you meant to. We generate the statutory text, sized and formatted the way the statute demands.
            </p>
        </div>
        <div class="mt-12 grid gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($statutoryStrip as $stripCode)
            @php $stripRules = $states[$stripCode] ?? []; @endphp
            <a href="{{ route('liens.lien-waivers.state', strtolower($stripCode)) }}" class="rounded-xl border border-zinc-700 bg-zinc-800/60 p-4 transition hover:border-amber-500/50 hover:bg-zinc-800">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-semibold text-white">{{ $stripRules['state_name'] ?? $stripCode }}</span>
                    @if (in_array($stripCode, $specialRuleStates, true))
                    <span class="shrink-0 rounded-full bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-400">Special rules</span>
                    @endif
                </div>
                @if (!empty($stripRules['statute']))
                <div class="mt-1 text-xs text-zinc-400">{{ $stripRules['statute'] }}</div>
                @endif
            </a>
            @endforeach
        </div>
        <p class="mt-8 text-center text-sm text-zinc-500">Every other state gets our attorney-reviewed house forms with that state's execution rules built in.</p>
    </div>
</section>

{{-- Pricing --}}
<section id="pricing" class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Simple pricing</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">The free plan is the full product — download, e-signatures, reminders, signed-copy storage — for {{ $freeSaves }} waivers a month. Upgrade when you need unlimited. See the <a href="{{ route('liens.lien-waivers.pricing') }}" class="font-medium text-amber-600 underline hover:text-amber-700">full pricing breakdown</a>.</p>
        </div>
        <div class="mx-auto mt-12 grid max-w-4xl gap-8 lg:grid-cols-2">
            {{-- Free --}}
            <div class="flex flex-col rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="text-lg font-semibold text-zinc-900">Free</h3>
                <div class="mt-4 flex items-baseline gap-1">
                    <span class="text-5xl font-bold tracking-tight text-zinc-900">$0</span>
                    <span class="text-zinc-500">/month</span>
                </div>
                <p class="mt-3 text-sm text-zinc-600">Everything you need to generate a correct waiver and get it out the door.</p>
                <ul class="mt-6 flex-1 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> {{ $freeSaves }} waivers per month with every Pro feature, no watermark</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Correct forms for all 50 states, statutory text included</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> E-signature send &amp; collect, reminders, signed-copy storage</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Free project &amp; deadline tracking</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-8 inline-flex items-center justify-center gap-2 rounded-lg border border-zinc-300 bg-white px-6 py-3 font-semibold text-zinc-900 transition hover:border-zinc-400">
                    Start free
                </a>
            </div>
            {{-- Pro --}}
            <div class="relative flex flex-col rounded-2xl border-2 border-amber-500 bg-white p-8 shadow-lg">
                <span class="absolute -top-3.5 left-1/2 -translate-x-1/2 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-zinc-900">Most popular</span>
                <h3 class="text-lg font-semibold text-zinc-900">Pro</h3>
                <div class="mt-4 flex items-baseline gap-1">
                    <span class="text-5xl font-bold tracking-tight text-zinc-900">${{ $monthlyPrice }}</span>
                    <span class="text-zinc-500">/month</span>
                </div>
                <p class="mt-3 text-sm text-zinc-600">Or ${{ $yearlyPrice }}/year, 2 months free. For teams exchanging waivers on every draw.</p>
                <ul class="mt-6 flex-1 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Everything in Free</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> Unlimited waivers every month, no cap</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> One flat price for your whole team</li>
                    <li class="flex items-start gap-2"><span class="mt-1 text-emerald-500">&#10003;</span> No per-waiver or per-signature charges</li>
                </ul>
                <a href="{{ route('register') }}" class="group mt-8 inline-flex items-center justify-center gap-2 rounded-lg bg-zinc-900 px-6 py-3 font-semibold text-white shadow-sm transition hover:bg-zinc-800">
                    Start with Pro
                    <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- State directory --}}
<section class="border-t border-zinc-200 bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Lien waiver forms by state</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Every state has its own rules on statutory forms, notarization, witnesses, and e-signature. Pick your state to see exactly what applies.</p>
        </div>
        <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($states as $stateCode => $stateRules)
            <a href="{{ route('liens.lien-waivers.state', strtolower($stateCode)) }}" class="group flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 py-3 text-center transition hover:border-amber-300 hover:shadow-sm">
                <span class="text-sm font-medium text-zinc-900 group-hover:text-amber-700">{{ $stateRules['state_name'] ?? $stateCode }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Frequently asked questions</h2>
        </div>
        <div class="mt-12 space-y-4">
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between a conditional and an unconditional lien waiver?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">A conditional waiver only takes effect once the payment it describes actually arrives; if the check bounces or never comes, your lien rights survive. An unconditional waiver gives up lien rights immediately upon signing, whether or not you're ever paid. The safe practice: sign conditional waivers when payment is promised, and unconditional waivers only after the money is in hand.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between a progress waiver and a final waiver?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">A progress (or partial/interim) waiver covers a single progress payment; it waives lien rights only for work through a specific date, leaving rights for later work intact. A final waiver is signed with the last payment on the project and waives all remaining lien rights. Most projects exchange a progress waiver at every draw and one final waiver at closeout.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do lien waivers need to be notarized?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Almost never. Only Mississippi and Wyoming require lien waivers to be notarized, and Georgia requires a witness signature on its statutory forms. In the other 47 states an ordinary signature is enough, though a customer's contract can always ask for more than the statute does. Each of our state pages spells out exactly what that state requires.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is an electronic signature valid on a lien waiver?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes, in most states. The federal E-SIGN Act and each state's UETA make electronic signatures legally equivalent to ink for documents like lien waivers, and our e-signature flow produces a tamper-evident audit certificate with every signed copy. A few states' statutory forms carry execution requirements (like Georgia's witness signature) that don't fit an online signing flow; in those states we disable e-signature and generate a print-ready PDF instead, and the state page tells you why.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do I have to pay to create a lien waiver?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">No. Generating and downloading the correct waiver for your state is free, with no watermark and no credit card. You only pay when you want us to do the follow-up work: sending waivers for e-signature, chasing signatures with automatic reminders, and storing the signed copies on your projects. Free accounts can also save up to {{ $freeSaves }} waivers to their projects each month.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Does the person I send a waiver to need an account to sign it?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">No. The signer gets a secure link by email, verifies their identity with a one-time code, reviews the waiver, and signs, all in the browser with no account, password, or download. After signing they're offered a free account to keep track of everything they've signed, but it's never required.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Which states require a specific lien waiver form?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Twelve states regulate the form itself: Arizona, California, Florida, Georgia, Michigan, Mississippi, Nevada, Texas, Utah, and Wyoming prescribe statutory waiver forms, and Massachusetts and Missouri impose special rules. In those states we generate the exact statutory text, sized and formatted the way the statute demands. The other 38 states leave the format to the parties, and our attorney-reviewed house forms apply with that state's execution rules built in.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I upload a waiver that was signed on paper?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Yes. If a waiver was wet-signed, notarized, or witnessed on paper (as Mississippi, Wyoming, and Georgia require), you can upload the signed copy and store it on the project alongside everything else, so your whole waiver history lives in one place regardless of how each one was signed.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-zinc-50">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between sending a waiver and collecting one?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Sending is for subcontractors and suppliers who sign their own waiver so a customer will release payment. Collecting is for general contractors and owners who request signed waivers from the subs and vendors they pay. The same tool handles both: it picks the correct form, tracks who has signed, and keeps every signed copy on the project. You choose which direction you're working in when you start a waiver.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Create your first lien waiver free</h2>
            <p class="mt-4 text-lg text-zinc-400">The correct form for your state, filled in and ready to download in about two minutes. No credit card required.</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50">
                    Create a free lien waiver
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
