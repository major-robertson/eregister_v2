@extends('layouts.landing')

@section('title', 'Preliminary Notice | File a Preliminary Lien Notice in Any State')

@section('meta')
<meta name="description" content="Protect your lien rights with a preliminary notice. Our service helps contractors, subcontractors, and suppliers file preliminary lien notices in all 50 states. Preserve your right to file a mechanics lien.">
@endsection

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-24 lg:py-32">
    <div class="relative mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/20 bg-amber-500/10 px-4 py-1.5 text-sm text-amber-400">
            Available in all 50 states
        </div>
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            File a Preliminary Notice<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Preserve Your Lien Rights</span>
        </h1>
        <p class="mx-auto mt-6 max-w-2xl text-lg text-zinc-400">Protect your right to file a mechanics lien. Our service helps contractors, subcontractors, and suppliers send state-compliant preliminary notices to preserve construction lien rights in every state.</p>
        <div class="mt-10">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                Get Started
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

{{-- What is a preliminary notice --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-amber-600">Lien Rights</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">What Is a Preliminary Notice?</h2>
                <p class="mt-4 text-lg text-zinc-600">
                    A preliminary notice (sometimes called a pre-lien notice, 20-day notice, or notice to owner) is a document sent early in a construction project to notify the property owner and general contractor that you're providing labor, materials, or services. It's the critical first step to preserve your right to file a mechanics lien if you're not paid.
                </p>
                <p class="mt-4 text-zinc-600">
                    In many states, sending a preliminary notice is required before you can later file a construction lien. Missing the deadline or sending an incomplete notice can invalidate your lien rights entirely—even if you've done the work. Our service ensures your preliminary lien notice is correct and timely.
                </p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-8">
                <h3 class="font-semibold text-zinc-900">Why Send One Early?</h3>
                <ul class="mt-4 space-y-3 text-zinc-600">
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Establishes your lien rights from day one
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Required in most states before filing a mechanics lien
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Often improves cash flow by putting everyone on notice
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="mt-1 text-amber-500">✓</span>
                        Protects subcontractors and material suppliers
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- Why it matters --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Why a Preliminary Notice Matters</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">Preserving your lien rights isn't optional—it's essential for protecting your construction payments.</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Preserves Lien Rights</h3>
                <p class="mt-2 text-zinc-600">Without a proper preliminary notice, you lose your right to file a mechanics lien—no matter how much you're owed. Send early to stay protected.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M3 6l6.063.75M3 6v12a2 2 0 002 2h12a2 2 0 002-2V6" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Required in Many States</h3>
                <p class="mt-2 text-zinc-600">Most states mandate preliminary notices with strict deadlines—often 20, 30, or 60 days from first furnishing. Miss the window and your lien rights are gone.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-white p-6">
                <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-zinc-900">Subcontractor Protection</h3>
                <p class="mt-2 text-zinc-600">Subcontractors and suppliers are especially vulnerable. A preliminary notice puts you on the payment chain and protects your ability to lien the property.</p>
            </div>
        </div>
    </div>
</section>

{{-- Who needs to send one --}}
<section class="bg-white py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Who Needs to Send a Preliminary Notice?</h2>
            <p class="mx-auto mt-4 max-w-2xl text-lg text-zinc-600">If you furnish labor or materials to a construction project and don't have a direct contract with the owner, you likely need one.</p>
        </div>
        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5">
                <h3 class="font-semibold text-zinc-900">Subcontractors</h3>
                <p class="mt-1 text-sm text-zinc-600">Required in most states to preserve lien rights when working under a GC.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5">
                <h3 class="font-semibold text-zinc-900">Material Suppliers</h3>
                <p class="mt-1 text-sm text-zinc-600">Suppliers who furnish materials to projects must typically send preliminary notices.</p>
            </div>
            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-5">
                <h3 class="font-semibold text-zinc-900">Equipment Rental</h3>
                <p class="mt-1 text-sm text-zinc-600">Companies renting equipment to construction projects may need to send notices.</p>
            </div>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">How It Works</h2>
            <p class="mt-3 text-zinc-600">File your preliminary lien notice in three simple steps</p>
        </div>
        <div class="relative mt-16">
            <div class="absolute top-7 hidden h-0.5 bg-zinc-300 lg:block" style="left: calc(16.67% + 1.75rem); right: calc(16.67% + 1.75rem);"></div>
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">1</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">Enter project details</h3>
                    <p class="mt-2 text-zinc-600">Provide the property address, owner, general contractor, and your work description. We'll confirm your state's requirements.</p>
                </div>
                <div class="relative text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-900 text-xl font-bold text-white">2</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We prepare the notice</h3>
                    <p class="mt-2 text-zinc-600">Our team creates your state-compliant preliminary notice with the correct form, language, and deadlines for your jurisdiction.</p>
                </div>
                <div class="text-center">
                    <div class="relative z-10 mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-500 text-xl font-bold text-white">3</div>
                    <h3 class="mt-6 text-xl font-semibold text-zinc-900">We send it for you</h3>
                    <p class="mt-2 text-zinc-600">We deliver your preliminary notice to all required parties—property owner, general contractor, and lender if applicable—with proof of service.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="bg-zinc-50 py-24">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-3xl font-bold text-zinc-900">Frequently Asked Questions</h2>
        </div>
        <div class="mt-12 space-y-4">
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    What happens if I don't send a preliminary notice?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">In states that require preliminary notices, failing to send one—or sending it late—typically means you lose your right to file a mechanics lien. You can't recover payment through a construction lien if you've waived your lien rights by missing the preliminary notice deadline.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    When do I need to send a preliminary notice?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Deadlines vary by state. Common timeframes include within 20 days of first furnishing (e.g., California, Arizona), 60 days, or before the project is completed. Some states don't require preliminary notices at all. Our service checks your state and ensures you meet the correct deadline.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Do general contractors need to send preliminary notices?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">Often no—general contractors usually have a direct contract with the property owner, so they may not need a preliminary notice. But state rules differ. Subcontractors, suppliers, and lower-tier parties almost always need one. Check your state's mechanics lien laws for specifics.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Is a preliminary notice the same as a mechanics lien?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">No. A preliminary notice is an informational document that preserves your right to later file a mechanics lien. It doesn't create a lien on the property. A mechanics lien is filed with the county recorder and creates a security interest on the property. You send the preliminary notice first; you file the lien only if you're not paid.</div>
            </details>
            <details class="group rounded-xl border border-zinc-200 bg-white">
                <summary class="flex cursor-pointer items-center justify-between p-5 font-medium text-zinc-900 [&::-webkit-details-marker]:hidden">
                    Does every state require preliminary notices?
                    <svg class="h-5 w-5 shrink-0 text-zinc-400 transition group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="border-t border-zinc-100 px-5 py-4 text-zinc-600">No. Some states don't require preliminary notices (e.g., Texas for many parties, some other states). Others require them only for certain parties like subcontractors. Our service automatically determines your state's rules and whether you need to send one, then prepares the correct document.</div>
            </details>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="mx-auto mb-16 max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-zinc-900 to-zinc-800 py-20">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-white sm:text-4xl">Protect Your Lien Rights Today</h2>
            <p class="mt-4 text-lg text-zinc-400">File a preliminary notice in any state. Preserve your right to file a mechanics lien and get paid for your construction work.</p>
            <div class="mt-8">
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 font-semibold text-zinc-900 shadow-lg transition hover:scale-105 hover:bg-zinc-50">
                    Get Started
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
