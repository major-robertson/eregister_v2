@if (($trackConversion ?? false) && $payment)
    @push('scripts')
    <!-- Reddit Pixel Conversion (annual subscription) -->
    <script data-navigate-once>
        rdt('track', 'Purchase', {
            value: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            currency: "USD",
            conversionId: "purchase-{{ $payment->id }}"
        });
    </script>
    <script data-navigate-once>
        // Drop ?payment_intent so a refresh doesn't re-arm the conversion guard.
        history.replaceState(history.state, '', window.location.pathname);
    </script>
    @endpush
@endif

<x-layouts.portal title="Subscription Active">
    <div class="mx-auto max-w-lg space-y-6 px-6 py-10">
        <x-ui.card>
            <div class="space-y-4 py-4 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                    <flux:icon name="check" class="h-8 w-8 text-green-600" />
                </div>

                <flux:heading size="lg">You're subscribed!</flux:heading>

                <flux:text class="text-zinc-600">
                    Your Resale Certificate Generator subscription is active.
                    @if ($payment)
                        A receipt for ${{ number_format($payment->amount_cents / 100, 2) }} is on its way to your inbox.
                    @endif
                </flux:text>

                <flux:text class="text-sm text-zinc-500">
                    Next: set up your resale profile so certificates can be generated — takes about 5 minutes.
                </flux:text>

                <div class="pt-2">
                    <x-ui.action-button href="{{ route('resale-cert.onboarding') }}">
                        Set Up Your Resale Profile
                    </x-ui.action-button>
                </div>

                <flux:button href="{{ route('resale-cert.dashboard') }}" variant="ghost" size="sm">
                    Go to dashboard
                </flux:button>
            </div>
        </x-ui.card>
    </div>
</x-layouts.portal>
