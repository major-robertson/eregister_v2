<div class="mx-auto max-w-md space-y-6 px-6 py-10">
    <div class="text-center">
        <flux:heading size="xl">Secure payment</flux:heading>
        <flux:text class="mt-1 text-zinc-500">{{ $stateName }} LLC Formation</flux:text>
    </div>

    <x-ui.card>
        {{-- Order summary --}}
        <div class="mb-6 space-y-3 text-sm">
            <div class="flex items-start justify-between text-zinc-600 dark:text-zinc-400">
                <span>
                    LLC membership
                    <span class="block text-xs text-zinc-500">
                        Formation, EIN, registered agent &amp; yearly filings — billed annually
                    </span>
                </span>
                <span class="whitespace-nowrap">{{ $membershipFormatted }}/yr</span>
            </div>

            <div class="flex items-start justify-between text-zinc-600 dark:text-zinc-400">
                <span>
                    {{ $stateName }} state filing fee
                    <span class="block text-xs text-zinc-500">One-time, paid to the state</span>
                </span>
                <span class="whitespace-nowrap">{{ $stateFeeFormatted }}</span>
            </div>

            <div class="flex items-center justify-between border-t border-zinc-200 pt-3 text-base font-medium text-zinc-900 dark:border-zinc-700 dark:text-zinc-100">
                <span>Total due today</span>
                <span>{{ $totalFormatted }}</span>
            </div>
        </div>

        <div>
            <x-billing.stripe-payment-element
                :client-secret="$clientSecret"
                :payment-intent-id="$paymentIntentId"
                :payment-id="$paymentId"
                :return-url="$returnUrl"
                :formatted-amount="$totalFormatted"
                :is-ready="$isReady"
            />
        </div>

        <p class="mt-4 text-center text-xs text-zinc-500">
            The membership renews at {{ $membershipFormatted }}/year and can be canceled anytime.
            By completing this purchase, you agree to our
            <a href="{{ route('terms-of-service') }}" class="underline" target="_blank">Terms of Service</a>
            and
            <a href="{{ route('privacy-policy') }}" class="underline" target="_blank">Privacy Policy</a>.
        </p>
    </x-ui.card>

    <div class="text-center">
        <a
            href="{{ route('formations.show', $application) }}"
            class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
            wire:navigate
        >
            Cancel and return to application
        </a>
    </div>
</div>
