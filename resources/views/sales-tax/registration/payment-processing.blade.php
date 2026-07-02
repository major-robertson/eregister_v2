<x-layouts.portal title="Processing Payment">
    <div class="mx-auto max-w-lg space-y-6 px-6 py-10">
        <x-ui.card>
            <div
                class="space-y-4 text-center"
                x-data="salesTaxPaymentPoller(@js(route('sales-tax.api.registrations.payment-status', $application)), @js(route('sales-tax.registrations.payment-confirmation', $application)), @js(route('sales-tax.registrations.checkout', $application)))"
                x-init="startPolling()"
            >
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                    <flux:icon name="arrow-path" class="h-8 w-8 animate-spin text-amber-600 dark:text-amber-400" />
                </div>

                <flux:heading size="lg">Processing your payment...</flux:heading>

                <flux:text class="text-zinc-600 dark:text-zinc-400">
                    Please wait while we confirm your payment. This usually takes just a few seconds.
                </flux:text>

                <flux:text class="text-sm text-zinc-500">
                    You'll be redirected automatically once payment is confirmed.
                </flux:text>
            </div>
        </x-ui.card>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('salesTaxPaymentPoller', (statusUrl, confirmationUrl, checkoutUrl) => ({
            polling: false,
            attempts: 0,
            maxAttempts: 30, // 30 x 2s = 1 minute max

            startPolling() {
                this.polling = true;
                this.poll();
            },

            async poll() {
                if (!this.polling || this.attempts >= this.maxAttempts) {
                    return;
                }

                this.attempts++;

                try {
                    const response = await fetch(statusUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    if (response.ok) {
                        const data = await response.json();

                        if (data.status === 'succeeded' || data.paid) {
                            window.location.href = confirmationUrl;
                            return;
                        }

                        if (data.status === 'failed' || data.status === 'canceled') {
                            window.location.href = checkoutUrl;
                            return;
                        }
                    }
                } catch (error) {
                    console.error('Polling error:', error);
                }

                setTimeout(() => this.poll(), 2000);
            },

            stopPolling() {
                this.polling = false;
            },
        }));
    });
    </script>
</x-layouts.portal>
