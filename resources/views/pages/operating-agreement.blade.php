@extends('layouts.landing')

@section('title', 'LLC Operating Agreement | Custom Operating Agreement Template')

@section('meta')
<meta name="description"
    content="Custom LLC operating agreement for multi-member and single-member LLCs. Define member rights, profit distribution, management structure, and protect your business. Professional template tailored to your needs.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                    Protect Your LLC with an
                    <span class="text-violet-600">Operating Agreement</span>
                </h1>

                <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                    Define member rights, profit distribution, and management structure. A custom operating agreement strengthens liability protection, prevents disputes, and clarifies how your LLC operates. Single-member or multi-member—we tailor it to your business.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-violet-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-violet-700">
                        Create Operating Agreement
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-r from-violet-50 to-purple-50 opacity-60 blur-xl"></div>
                <div class="relative space-y-4">
                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Custom Drafted</h3>
                            <p class="text-sm text-zinc-500">Tailored to your LLC structure</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Member Rights</h3>
                            <p class="text-sm text-zinc-500">Voting, profit sharing & responsibilities</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Liability Protection</h3>
                            <p class="text-sm text-zinc-500">Strengthen your corporate veil</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- What Is an Operating Agreement --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-violet-600">Overview</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                What Is an Operating Agreement?
            </h2>
            <p class="mt-4 text-lg text-zinc-600">
                An LLC operating agreement is a legal document that defines how your LLC is governed. It sets out member (owner) rights and responsibilities, profit and loss allocation, management structure (member-managed vs. manager-managed), voting procedures, buy-sell provisions, and what happens when a member leaves or the LLC dissolves. Most states do not require you to file an operating agreement with the state—it's an internal document. However, banks, investors, and courts often expect to see one. It reinforces your LLC's separate existence and can help protect the corporate veil. Single-member LLCs benefit from an operating agreement too; it documents that the business is distinct from the owner.
            </p>
        </div>

        {{-- Why You Need One --}}
        <div class="mt-20">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">Why You Need an Operating Agreement</h2>
            <div class="mt-12 grid gap-8 md:grid-cols-3">
                <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">Liability Protection</h3>
                    <p class="mt-2 text-zinc-600">Courts may "pierce the corporate veil" if an LLC lacks formalities. An operating agreement demonstrates that you treat the LLC as a separate entity, helping protect personal assets.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">Prevent Member Disputes</h3>
                    <p class="mt-2 text-zinc-600">Without a written agreement, state default rules apply—and they may not match your expectations. Document profit splits, decision-making, and exit procedures to avoid costly conflicts.</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-8 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 font-bold text-zinc-900">Bank & Lender Requirements</h3>
                    <p class="mt-2 text-zinc-600">Many banks require an operating agreement before opening a business account. Lenders and investors may also request it to understand your ownership structure and governance.</p>
                </div>
            </div>
        </div>

        {{-- What's Included --}}
        <div class="mt-24">
            <h2 class="text-center text-2xl font-bold text-zinc-900 sm:text-3xl">What's Included</h2>
            <div class="mx-auto mt-12 max-w-2xl">
                <ul class="space-y-4 text-zinc-600">
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Member names, ownership percentages, and capital contributions</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Management structure (member-managed or manager-managed)</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Voting rights and decision-making procedures</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Profit and loss allocation</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Buy-sell provisions and transfer restrictions</span>
                    </li>
                    <li class="flex gap-3 rounded-xl bg-white p-4 shadow-sm">
                        <svg class="h-6 w-6 shrink-0 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>Dissolution and winding-up procedures</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-violet-600">Simple Process</p>
            <h2 class="mt-3 text-3xl font-extrabold text-zinc-900 sm:text-4xl">
                How It Works
            </h2>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 text-lg font-bold text-white">1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Answer a Few Questions</h3>
                    <p class="mt-3 text-zinc-600">Tell us about your LLC: members, ownership split, management style, and any special terms. Single-member or multi-member—we adapt to your structure.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">We Draft Your Agreement</h3>
                    <p class="mt-3 text-zinc-600">We generate a custom operating agreement based on your answers and your state's requirements. You'll receive a professional, ready-to-sign document.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Sign & Store Safely</h3>
                    <p class="mt-3 text-zinc-600">All members sign the agreement. Keep it with your LLC records—you don't file it with the state, but banks and lenders may ask to see it.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-sm font-semibold uppercase tracking-widest text-violet-600">FAQ</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                Frequently Asked Questions
            </h2>
        </div>

        <div class="mt-12 space-y-4">
            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is an operating agreement required by law?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Most states do not require LLCs to have an operating agreement, but California, Delaware, Maine, Missouri, and New York require either an operating agreement or similar documentation. Even where not required, having one is strongly recommended for liability protection, clarity, and meeting bank and lender expectations.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do single-member LLCs need an operating agreement?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes. A single-member operating agreement helps demonstrate that the LLC is separate from the owner—critical for maintaining the corporate veil. It documents that the business has its own governing rules, even with one owner. Banks and courts may look for this formality.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What's the difference between member-managed and manager-managed?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    In a member-managed LLC, all members participate in day-to-day decisions. In a manager-managed LLC, one or more designated managers run the business; other members are passive investors. Manager-managed structures are common when some members are investors who don't want operational involvement.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do I file my operating agreement with the state?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    No. The operating agreement is an internal document. You do not file it with the Secretary of State or any agency. Keep signed copies with your LLC records. You may need to provide it to your bank, accountant, or attorney.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I amend my operating agreement later?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes. Operating agreements typically include amendment procedures—often requiring a vote or unanimous consent of members. When you add members, change ownership, or modify management, you should amend the agreement and have all members sign the amendment.
                </div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-900 via-zinc-800 to-zinc-900 px-6 py-20 sm:px-12 sm:py-28">
            <div class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-violet-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-purple-500/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
            </div>

            <div class="relative mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">
                    Protect Your LLC Today
                </h2>
                <p class="mt-6 text-lg text-zinc-300">
                    Get a custom operating agreement tailored to your business. Strengthen your structure and avoid disputes.
                </p>
                <div class="mt-10">
                    <a href="{{ route('register') }}"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-8 py-4 text-base font-semibold text-white shadow-lg shadow-violet-600/25 transition-all hover:bg-violet-500 hover:shadow-xl sm:w-auto">
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
