<div class="mx-auto max-w-md space-y-6">
    <div class="text-center">
        <flux:heading size="xl">Secure payment</flux:heading>
        <flux:text class="mt-1 text-zinc-500">Sales &amp; Use Tax Permit Registration</flux:text>
    </div>

    <x-ui.card>
        {{-- Order summary: per-state pricing --}}
        <div class="mb-6 space-y-2 text-sm">
            <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                <span>{{ $stateCount }} {{ \Illuminate\Support\Str::plural('state', $stateCount) }} &times; $199.00</span>
                <span>{{ $formattedPrice }}</span>
            </div>
            <div class="flex items-center justify-between border-t border-zinc-200 pt-2 font-medium text-zinc-900 dark:border-zinc-700 dark:text-zinc-100">
                <span>Total</span>
                <span>{{ $formattedPrice }}</span>
            </div>
        </div>

        <div>
            <x-billing.stripe-payment-element
                :client-secret="$clientSecret"
                :payment-intent-id="$paymentIntentId"
                :payment-id="$paymentId"
                :return-url="$returnUrl"
                :formatted-amount="$formattedPrice"
                :is-ready="$isReady"
            />
        </div>

        <p class="mt-4 text-center text-xs text-zinc-500">
            By completing this purchase, you agree to our
            <a href="{{ route('terms-of-service') }}" class="underline" target="_blank">Terms of Service</a>
            and
            <a href="{{ route('privacy-policy') }}" class="underline" target="_blank">Privacy Policy</a>.
        </p>
    </x-ui.card>

    <div class="text-center">
        <a
            href="{{ route('sales-tax.registrations.show', $application) }}"
            class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
            wire:navigate
        >
            Cancel and return to application
        </a>
    </div>
</div>
