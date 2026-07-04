<div class="mx-auto max-w-md space-y-6 px-6 py-10">
    <div class="text-center">
        <flux:heading size="xl">Secure payment</flux:heading>
        <flux:text class="mt-1 text-zinc-500">Resale Certificate Generator</flux:text>
    </div>

    <x-ui.card>
        <div class="mb-6 space-y-3 text-sm">
            <div class="flex items-start justify-between text-zinc-600">
                <span>
                    Annual subscription
                    <span class="block text-xs text-zinc-500">
                        Unlimited resale certificates, all states — billed yearly
                    </span>
                </span>
                <span class="whitespace-nowrap">{{ $amountFormatted }}/yr</span>
            </div>

            <div class="flex items-center justify-between border-t border-zinc-200 pt-3 text-base font-medium text-zinc-900">
                <span>Total due today</span>
                <span>{{ $amountFormatted }}</span>
            </div>
        </div>

        <div>
            <x-billing.stripe-payment-element
                :client-secret="$clientSecret"
                :payment-intent-id="$paymentIntentId"
                :payment-id="$paymentId"
                :return-url="$returnUrl"
                :formatted-amount="$amountFormatted"
                :is-ready="$isReady"
            />
        </div>

        <p class="mt-4 text-center text-xs text-zinc-500">
            The subscription renews at {{ $amountFormatted }}/year and can be canceled anytime.
            By completing this purchase, you agree to our
            <a href="{{ route('terms-of-service') }}" class="underline" target="_blank">Terms of Service</a>
            and
            <a href="{{ route('privacy-policy') }}" class="underline" target="_blank">Privacy Policy</a>.
        </p>
    </x-ui.card>

    <div class="text-center">
        <a href="{{ route('resale-cert.dashboard') }}" class="text-sm text-zinc-500 hover:text-zinc-700">
            Cancel and go back
        </a>
    </div>
</div>
