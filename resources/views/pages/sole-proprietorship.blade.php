@extends('layouts.landing')

@section('title', 'Register a Sole Proprietorship | Start Your Business Today')

@section('meta')
<meta name="description"
    content="Register a sole proprietorship with eRegister. Start your business as a sole proprietor in all 50 states. DBA registration, EIN, and compliance. Simple setup for solo entrepreneurs.">
@endsection

@section('content')
{{-- Hero Section --}}
<section class="relative bg-white pb-20 pt-16 lg:pb-28 lg:pt-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-20">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight text-zinc-900 sm:text-5xl xl:text-6xl">
                    Start Your Sole Proprietorship
                    <span class="text-violet-600">Today</span>
                </h1>

                <p class="mt-6 text-xl leading-relaxed text-zinc-600">
                    The simplest way to launch your business. Register as a sole proprietor, get your DBA and EIN, and start operating—without forming an LLC or corporation.
                </p>

                <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                    <a href="{{ route('register') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-violet-600 px-8 py-4 text-base font-semibold text-white transition hover:bg-violet-700">
                        Get Started
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
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Sole Proprietorship Setup</h3>
                            <p class="text-sm text-zinc-500">DBA, EIN, and local registration</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">Quick & Simple</h3>
                            <p class="text-sm text-zinc-500">No incorporation paperwork required</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 rounded-2xl border border-zinc-100 bg-white p-5 shadow-lg">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-zinc-900">All 50 States</h3>
                            <p class="text-sm text-zinc-500">State and local compliance support</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- What is a Sole Proprietorship --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 items-center gap-16 lg:grid-cols-2">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-violet-600">Learn</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                    What is a Sole Proprietorship?
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    A sole proprietorship is an unincorporated business owned and operated by one person. There is no legal distinction between you and your business—you report business income and expenses on your personal tax return.
                </p>

                <p class="mt-6 text-zinc-600">
                    Many freelancers, consultants, and small-scale entrepreneurs start as sole proprietors because setup is simple and inexpensive. You may still need to register a DBA, obtain an EIN, or comply with local licensing requirements—we help with all of that.
                </p>
            </div>

            {{-- Pros and Cons --}}
            <div class="relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-tr from-violet-100 via-purple-50 to-fuchsia-100 blur-2xl"></div>
                <div class="relative overflow-hidden rounded-2xl border border-zinc-200 bg-white p-8 shadow-2xl">
                    <h3 class="text-xl font-bold text-zinc-900">Pros & Cons</h3>

                    <div class="mt-6">
                        <h4 class="font-bold text-emerald-700">Advantages</h4>
                        <ul class="mt-2 space-y-2 text-zinc-600">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Easiest and cheapest business structure
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                No formation paperwork or state fees
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Simple tax filing (Schedule C)
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Full control and flexibility
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6">
                        <h4 class="font-bold text-amber-700">Considerations</h4>
                        <ul class="mt-2 space-y-2 text-zinc-600">
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Personal liability for business debts
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                Harder to raise capital or get business credit
                            </li>
                            <li class="flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                May need DBA and EIN for banking and contracts
                            </li>
                        </ul>
                    </div>
                </div>
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
                Register in 3 Easy Steps
            </h2>
        </div>

        <div class="mt-16 grid gap-8 md:grid-cols-3">
            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 text-lg font-bold text-white">1</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">File Your DBA (if needed)</h3>
                    <p class="mt-3 text-zinc-600">If you're operating under a name other than your personal name, file a DBA with your county or state.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 text-lg font-bold text-white">2</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Obtain an EIN</h3>
                    <p class="mt-3 text-zinc-600">Get a free Employer Identification Number from the IRS for banking, hiring, and tax purposes.</p>
                </div>
            </div>

            <div class="relative rounded-2xl bg-zinc-50 p-8 shadow-sm">
                <div class="absolute -top-5 left-8 flex h-10 w-10 items-center justify-center rounded-full bg-violet-600 text-lg font-bold text-white">3</div>
                <div class="pt-4">
                    <h3 class="text-xl font-bold text-zinc-900">Open Accounts & Comply</h3>
                    <p class="mt-3 text-zinc-600">Open a business bank account, get required licenses or permits, and you're ready to operate.</p>
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
                    Do I need to register a sole proprietorship?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Technically, a sole proprietorship exists automatically when you earn income from a business. But you often need to register a DBA if you use a trade name, obtain an EIN for banking or hiring, and comply with local business licenses or permits. We help you with these registrations.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Should I use my Social Security Number or get an EIN?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Sole proprietors can use their SSN for tax purposes, but an EIN is recommended for opening business bank accounts, hiring employees, applying for business credit, and protecting your SSN from exposure. EINs are free from the IRS.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    When should I switch from sole proprietorship to LLC?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Consider forming an LLC when you have significant liability exposure, hire employees, take on partners, need business credit, or want to protect personal assets from business debts. Many entrepreneurs start as sole proprietors and convert when the business grows.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    How do I pay taxes as a sole proprietor?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    You report business income and expenses on Schedule C with your personal Form 1040. You may need to pay estimated quarterly taxes (income tax and self-employment tax). Consult a tax professional for your specific situation.
                </div>
            </details>

            <details class="group rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <summary class="flex cursor-pointer items-center justify-between px-6 py-5 text-left font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Can I have employees as a sole proprietor?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition-transform group-open:rotate-45" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </summary>
                <div class="px-6 pb-5 text-zinc-600">
                    Yes. Sole proprietors can hire employees. You'll need an EIN, and you must withhold payroll taxes, pay employer taxes, and comply with labor laws. Many growing sole proprietors form an LLC or corporation once they hire to clarify structure and liability.
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
                    Ready to Start Your Business?
                </h2>
                <p class="mt-6 text-lg text-zinc-300">
                    Register your sole proprietorship, get your DBA and EIN, and launch today.
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
