@extends('layouts.landing')

@section('title', 'Resale Certificates | Get a Resale Certificate for Tax-Exempt Purchases')

@section('meta')
<meta name="description" content="Obtain resale certificates for tax-exempt wholesale purchases. Our service helps businesses get valid resale certificates accepted in all 50 states.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                Get Your <span class="text-emerald-600">Resale Certificate</span>
            </h1>
            <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                Buy wholesale without paying sales tax. A resale certificate lets you make tax-exempt purchases for items you'll resell—accepted by vendors in all 50 states.
            </p>
            <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-emerald-700">
                    Get Started
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>

{{-- What Is a Resale Certificate --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">Overview</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                What Is a Resale Certificate?
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                A resale certificate (sometimes called a resale license or exemption certificate) is a document that allows you to buy goods from suppliers without paying sales tax, because you intend to resell those goods to end customers. In that case, you collect sales tax from the buyer instead—you're not the end user. Each state has its own rules and forms; some accept multi-state resale certificates, while others require their state-specific form. A valid resale certificate typically includes your business name, address, tax ID (EIN), and a statement that the items are for resale. Vendors keep it on file to support the tax-exempt sale. Our service helps you obtain and manage resale certificates that are accepted across the country.
            </p>
        </div>

        {{-- When You Need One --}}
        <div class="mt-20">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">When You Need One</h2>
            <div class="mt-12 grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">Buying Wholesale</h3>
                    <p class="mt-2 text-zinc-600">When you purchase inventory from distributors, manufacturers, or wholesalers for resale, a resale certificate lets you buy tax-free. Without it, you pay sales tax upfront—money you'll only recover when you sell to customers.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">Reselling Goods</h3>
                    <p class="mt-2 text-zinc-600">Retailers, e-commerce sellers, dropshippers, and anyone who buys products to resell need a resale certificate. If you're registered for sales tax in your state and plan to collect tax from buyers, you can buy inventory exempt from sales tax.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">Simple Process</p>
            <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                How It Works
            </h2>
        </div>
        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Verify Your Eligibility</h3>
                    <p class="mt-3 text-zinc-600">You need a valid sales tax permit in at least one state to issue a resale certificate. We verify your registration status and ensure you meet the requirements to make tax-exempt purchases.</p>
                </div>
            </div>
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Get Your Certificate</h3>
                    <p class="mt-3 text-zinc-600">We prepare a valid resale certificate with your business details, tax ID, and proper wording. You receive a document you can present to vendors—print or digital formats accepted by most suppliers.</p>
                </div>
            </div>
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Use It With Vendors</h3>
                    <p class="mt-3 text-zinc-600">Present your resale certificate when buying wholesale. Vendors keep it on file and sell to you without charging sales tax. You collect tax from your end customers when you resell the goods.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">Frequently Asked Questions</h2>
        </div>
        <div class="mt-12 space-y-4">
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between a resale certificate and a sales tax permit?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">A sales tax permit (seller's permit) is issued by the state and authorizes you to collect sales tax from customers. A resale certificate is a document you provide to vendors when buying goods for resale—it tells them you're exempt from paying sales tax on that purchase because you'll collect it from your buyers. You need a valid sales tax permit before you can issue a resale certificate.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How long is a resale certificate valid?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">Validity varies by state. Some states consider resale certificates valid indefinitely as long as your sales tax permit remains active. Others require renewal every 1–3 years or have expiration dates. If your permit lapses or is revoked, your resale certificate is no longer valid. We help you keep your certificates current and compliant.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Will vendors in all 50 states accept my resale certificate?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">Most vendors across the country accept properly formatted resale certificates. Some states participate in the Streamlined Sales Tax (SST) multistate certificate, which many vendors recognize. A few states require their own forms for in-state purchases. We provide certificates that work with the broadest range of vendors and can help with state-specific forms when needed.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I use a resale certificate for personal purchases?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">No. Resale certificates may only be used for purchases you intend to resell. Using one for personal or business consumption (e.g., office supplies you use yourself) is tax fraud and can result in penalties, audits, and loss of your sales tax permit. Only use your resale certificate when buying inventory or goods you will sell to customers.</div>
            </details>
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do I need a resale certificate for services?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">Resale certificates typically apply to tangible personal property (goods) you buy for resale. For services, rules vary by state—some allow exemption when you're reselling a service, others treat services differently. If you buy goods that become part of a service (e.g., materials used in a repair), a resale certificate may apply. We can help clarify what's valid in your situation.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-emerald-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-emerald-500/20 blur-3xl"></div>
            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Ready to Get Started?</h2>
                <p class="mt-6 text-lg text-zinc-300">Get a valid resale certificate and start buying wholesale tax-free. Accepted by vendors across all 50 states.</p>
                <div class="mt-10">
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-8 py-4 text-base font-semibold text-white shadow-lg transition-all hover:bg-emerald-500 hover:shadow-xl">
                        Get Started Now
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
