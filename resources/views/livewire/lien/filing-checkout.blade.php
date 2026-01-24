<div class="max-w-md mx-auto space-y-6">
    <div class="text-center">
        <flux:heading size="xl">Secure payment</flux:heading>
    </div>

    <x-ui.card>
        {{-- Compact order summary --}}
        <div class="flex justify-between items-center text-sm text-zinc-600 dark:text-zinc-400 mb-6">
            <span>{{ $filing->documentType->name }}</span>
            <span>{{ $filing->service_level->label() }}</span>
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

        <p class="text-xs text-zinc-500 text-center mt-4">
            By completing this purchase, you agree to our
            <a href="{{ route('terms-of-service') }}" class="underline" target="_blank">Terms of Service</a>
            and
            <a href="{{ route('privacy-policy') }}" class="underline" target="_blank">Privacy Policy</a>.
        </p>
    </x-ui.card>

    <div class="text-center">
        <a 
            href="{{ route('lien.projects.show', $filing->project) }}" 
            class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
            wire:navigate
        >
            Cancel and return to project
        </a>
    </div>
</div>
