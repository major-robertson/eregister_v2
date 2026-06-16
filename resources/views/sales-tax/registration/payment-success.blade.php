@if (($trackConversion ?? false) && $payment)
    @push('scripts')
    <!-- Google Ads Conversion Tracking (one-time purchase) -->
    <script>
        gtag('event', 'conversion', {
            send_to: "AW-984288380/7C62CMuqrbYBEPyYrNUD",
            value: {{ number_format($payment->amount_cents / 100, 2, '.', '') }},
            currency: "USD",
            transaction_id: "{{ $payment->id }}"
        });
    </script>
    @endpush
@endif

<x-layouts.workspace key="sales_tax" title="Payment Successful">
    <div class="mx-auto max-w-lg space-y-6 px-6 py-10">
        <x-ui.card>
            <div class="space-y-4 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <flux:icon name="check" class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>

                <flux:heading size="lg">Thank you for your payment!</flux:heading>

                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    @if ($payment)
                        Your payment of {{ $payment->formattedAmount() }} has been processed successfully and your
                        registration has been submitted.
                    @else
                        Your registration has been submitted successfully.
                    @endif
                </flux:text>
            </div>

            <div class="mt-6 border-t border-zinc-200 pt-6 dark:border-zinc-700">
                <x-ui.info-list>
                    <x-ui.info-list.item label="Service">
                        Sales &amp; Use Tax Permit Registration
                    </x-ui.info-list.item>
                    <x-ui.info-list.item label="States">
                        {{ $application->stateCount() }} ({{ implode(', ', $application->selected_states ?? []) }})
                    </x-ui.info-list.item>
                    @if ($payment)
                        <x-ui.info-list.item label="Amount Paid">
                            {{ $payment->formattedAmount() }}
                        </x-ui.info-list.item>
                    @endif
                </x-ui.info-list>
            </div>
        </x-ui.card>

        <div class="flex justify-center gap-4">
            <flux:button href="{{ route('sales-tax.dashboard') }}" variant="primary" wire:navigate>
                Back to Sales Tax
            </flux:button>
        </div>
    </div>
</x-layouts.workspace>
