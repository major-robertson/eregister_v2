<div class="min-h-screen bg-gradient-to-b from-amber-50 to-white">
    <!-- Hero Section -->
    <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <!-- Business Name -->
            <h1 class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl">
                {{ $lead->display_name }}
            </h1>

            @if ($lead->property_address)
            <p class="mt-2 text-lg text-zinc-600">
                Project: {{ $lead->property_address }}, {{ $lead->property_city }}, {{ $lead->property_state }}
            </p>
            @endif

            <!-- Value Proposition -->
            <div class="mt-8 rounded-2xl bg-white p-8 shadow-lg ring-1 ring-zinc-200">
                <h2 class="text-2xl font-semibold text-zinc-900">
                    Protect Your Right to Payment
                </h2>
                <p class="mt-4 text-lg text-zinc-600">
                    Don't let unpaid invoices drain your business. Our lien services ensure you get paid for the work
                    you've done.
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100">
                            <svg class="size-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-left text-zinc-700">
                            <strong>Preliminary Notices</strong> — Preserve your lien rights from day one
                        </p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100">
                            <svg class="size-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-left text-zinc-700">
                            <strong>Mechanics Liens</strong> — Secure your claim when payment doesn't come
                        </p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-amber-100">
                            <svg class="size-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <p class="text-left text-zinc-700">
                            <strong>Deadline Tracking</strong> — Never miss a critical filing date
                        </p>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="mt-10">
                    <a href="{{ route('register') }}" wire:click="recordCtaClick"
                        class="inline-flex items-center justify-center rounded-lg bg-amber-500 px-8 py-4 text-lg font-semibold text-white shadow-lg transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                        Get Started — It's Free
                    </a>
                    <p class="mt-3 text-sm text-zinc-500">
                        No credit card required. Start protecting your payments today.
                    </p>
                </div>
            </div>

            <!-- Contact Info (if available) -->
            @if ($lead->phone)
            <div class="mt-8">
                <p class="text-zinc-600">Questions? Call us:</p>
                <a href="tel:{{ $lead->phone }}" wire:click="recordCallClick"
                    class="text-xl font-semibold text-amber-600 hover:text-amber-700">
                    {{ $lead->phone }}
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Trust Indicators -->
    <div class="border-t border-zinc-200 bg-white py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-8 sm:grid-cols-3">
                <div class="text-center">
                    <div class="text-3xl font-bold text-amber-600">50+</div>
                    <div class="mt-1 text-sm text-zinc-600">States Covered</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-amber-600">Fast</div>
                    <div class="mt-1 text-sm text-zinc-600">Same-Day Filing</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-amber-600">Secure</div>
                    <div class="mt-1 text-sm text-zinc-600">Bank-Level Security</div>
                </div>
            </div>
        </div>
    </div>
</div>