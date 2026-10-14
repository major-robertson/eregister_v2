@if (($trackConversion ?? false) && $payment)
    @push('scripts')
    @include('partials.google-purchase-tracking', ['itemName' => 'Sales Tax Registration'])
    <!-- Google Ads Conversion Tracking (one-time purchase) -->
    <script data-navigate-once>
        gtag('event', 'conversion', {
            send_to: "AW-984288380/7C62CMuqrbYBEPyYrNUD",
            value: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            currency: "USD",
            transaction_id: "{{ $payment->id }}"
        });
    </script>
    <!-- Reddit Pixel Conversion (one-time purchase) -->
    <script data-navigate-once>
        rdt('track', 'Purchase', {
            value: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            currency: "USD",
            conversionId: "purchase-{{ $payment->id }}"
        });
    </script>
    <!-- OpenAI Ads Conversion (one-time purchase) -->
    <script data-navigate-once>
        oaiq("measure", "order_created", {
            type: "contents",
            amount: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            currency: "USD"
        }, { event_id: "order-{{ $payment->id }}" });
    </script>
    <script data-navigate-once>
        // Drop ?payment_intent so a refresh doesn't re-arm the conversion guard.
        history.replaceState(history.state, '', window.location.pathname);
    </script>
    @endpush
@endif

@php
    // Two moments share this page: right after paying (the questions are
    // still to come) and after the wizard's submit (we take over). The
    // dashboard's "View" for a submitted application lands here too.
    $awaitingAnswers = $application->isAwaitingAnswers();
    $rush = $application->isRush();
    $stateNames = collect($application->selected_states ?? [])
        ->map(fn (string $code) => config("states.{$code}", $code))
        ->implode(', ');

    // The resale certificate add-on (EREG-116): null when not ordered, else
    // created, failed, skipped (already subscribed) or pending.
    $resaleStatus = $resaleCerts['status'] ?? null;
    $resaleAmount = isset($resaleCerts['amount_cents']) ? '$'.number_format($resaleCerts['amount_cents'] / 100, 2) : null;
    $resaleSubscribedNow = $resaleStatus !== null
        && $application->business?->subscribed(config('resale_cert.subscription_type'));
@endphp

<x-layouts.portal :title="$awaitingAnswers ? 'Payment received' : 'Application submitted'">
    <div class="mx-auto max-w-lg space-y-6 px-6 py-10">
        <x-ui.card>
            <div class="space-y-4 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="check" class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>

                @if ($awaitingAnswers)
                    <flux:heading size="lg">Payment received. Now the questions.</flux:heading>

                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        @if ($payment && $resaleStatus === 'created' && $resaleAmount)
                            Thank you. We charged {{ $payment->formattedAmount() }} for the registration
                            and {{ $resaleAmount }} for a year of resale certificates.
                        @elseif ($payment)
                            Thank you. We charged {{ $payment->formattedAmount() }}.
                        @endif
                        Next, answer about 10 minutes of questions about your business so we can
                        prepare your {{ $stateNames }} registration. You can save and come back any time.
                    </flux:text>
                @else
                    <flux:heading size="lg">Your application is in.</flux:heading>

                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                        Thank you. We are preparing your {{ $stateNames }} registration. Here is what happens next.
                    </flux:text>
                @endif
            </div>

            <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                <x-ui.info-list>
                    <x-ui.info-list.item label="Service">
                        Sales &amp; Use Tax Permit Registration
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="States">
                        {{ $application->stateCount() }} ({{ implode(', ', $application->selected_states ?? []) }})
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="Processing">
                        {{ $rush ? 'Rush: filed within 2 business days' : 'Standard: filed within 5 business days' }}
                    </x-ui.info-list.item>
                    @if ($payment)
                        <x-ui.info-list.item label="Amount Paid">
                            {{ $payment->formattedAmount() }}
                        </x-ui.info-list.item>
                    @endif
                    @if ($resaleStatus === 'created')
                        <x-ui.info-list.item label="Resale Certificates">
                            {{ $resaleAmount ? $resaleAmount.' a year. ' : '' }}Renews each year. Cancel anytime.
                        </x-ui.info-list.item>
                    @endif
                </x-ui.info-list>
            </div>

            @if ($resaleStatus === 'created')
                <flux:callout color="emerald" icon="check-circle" class="mt-6" data-resale-addon="created">
                    <flux:callout.heading>Your resale certificates are active</flux:callout.heading>
                    <flux:callout.text>
                        When your permit number arrives, add it to your resale profile. Then you can make certificates for your vendors.
                    </flux:callout.text>
                </flux:callout>
            @elseif ($resaleStatus === 'failed' && ! $resaleSubscribedNow)
                <flux:callout color="amber" icon="exclamation-triangle" class="mt-6" data-resale-addon="failed">
                    <flux:callout.heading>We could not start your resale certificates</flux:callout.heading>
                    <flux:callout.text>
                        Your registration is paid and on its way. We did not charge you for the certificates.
                        You can subscribe to them from the resale certificates page.
                    </flux:callout.text>
                    <x-slot name="actions">
                        <flux:button href="{{ route('resale-cert.checkout') }}" variant="primary" size="sm">
                            Subscribe to resale certificates
                        </flux:button>
                    </x-slot>
                </flux:callout>
            @elseif ($resaleStatus === 'pending')
                <flux:callout color="zinc" icon="clock" class="mt-6" data-resale-addon="pending">
                    <flux:callout.text>
                        We are still setting up your resale certificates. Check the resale certificates page in a few minutes.
                    </flux:callout.text>
                </flux:callout>
            @endif

            @unless ($awaitingAnswers)
                <ol class="mt-6 space-y-3 border-t border-zinc-200 pt-6 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-400">
                    <li>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">1. We review your answers.</span>
                        A specialist checks them against each state's rules and contacts you if anything is missing.
                    </li>
                    <li>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">2. We file within {{ $rush ? 2 : 5 }} business days.</span>
                        You get an email when it is in.
                    </li>
                    <li>
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">3. The state issues your number.</span>
                        Each state takes its own time. We email it to you and it appears on your dashboard.
                    </li>
                </ol>
            @endunless

            <p class="mt-6 text-center text-xs text-zinc-500">
                Our fee is refunded in full until we file with the state.
            </p>
        </x-ui.card>

        <div class="flex flex-wrap justify-center gap-4">
            @if ($awaitingAnswers)
                <flux:button href="{{ route('sales-tax.registrations.show', $application) }}" variant="primary" icon:trailing="arrow-right" wire:navigate>
                    Start the questions
                </flux:button>
                <flux:button href="{{ route('sales-tax.dashboard') }}" variant="ghost" wire:navigate>
                    Later, from my dashboard
                </flux:button>
            @else
                <flux:button href="{{ route('sales-tax.dashboard') }}" variant="primary" wire:navigate>
                    Back to Sales Tax
                </flux:button>
            @endif
        </div>

        @unless ($awaitingAnswers || $resaleStatus === 'created')
            <p class="text-center text-sm text-zinc-500">
                Once your permit arrives we can generate your resale certificates for every state you buy in.
                We will show you how on your dashboard.
            </p>
        @endunless
    </div>
</x-layouts.portal>
