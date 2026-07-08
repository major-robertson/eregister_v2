@if (($trackConversion ?? false) && $payment)
    @push('scripts')
    <!-- Reddit Pixel Conversion (lien waiver subscription) -->
    <script data-navigate-once>
        rdt('track', 'Purchase', {
            value: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            currency: "USD",
            conversionId: "purchase-{{ $payment->id }}"
        });
    </script>
    <!-- OpenAI Ads Conversion (lien waiver subscription) -->
    <script data-navigate-once>
        oaiq("measure", "subscription_created", {
            type: "plan_enrollment",
            amount: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            currency: "USD"
        }, { event_id: "subscription-{{ $payment->id }}" });
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
                    Your lien waiver subscription is active.
                    @if ($payment)
                        A receipt for ${{ number_format($payment->amount_cents / 100, 2) }} is on its way to your inbox.
                    @endif
                </flux:text>

                <flux:text class="text-sm text-zinc-500">
                    You now have unlimited saved waivers, e-signature send &amp; collect,
                    automatic reminders, and signed-copy storage.
                </flux:text>

                <div class="pt-2">
                    <flux:button href="{{ route('lien.waivers.index') }}" variant="primary">
                        Go to Lien Waivers
                    </flux:button>
                </div>
            </div>
        </x-ui.card>
    </div>
</x-layouts.portal>
