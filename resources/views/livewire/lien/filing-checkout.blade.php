<div class="max-w-md mx-auto space-y-6">
    <div class="text-center">
        <flux:heading size="xl">Secure payment</flux:heading>
    </div>

    <x-ui.card>
        {{-- Compact order summary: one row per charge, then the total --}}
        <dl class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400 mb-6">
            @foreach ($items as $item)
                <div class="flex justify-between gap-4">
                    <dt>{{ $item['label'] }}</dt>
                    <dd>${{ number_format($item['amount_cents'] / 100, 2) }}</dd>
                </div>
            @endforeach
            <div class="flex justify-between gap-4 border-t border-zinc-200 dark:border-zinc-700 pt-1 font-medium text-zinc-900 dark:text-white">
                <dt>Total</dt>
                <dd>{{ $formattedPrice }}</dd>
            </div>
        </dl>

        @if ($turnaround)
            {{-- How long county recording typically takes (EREG-143) --}}
            <div class="-mt-3 mb-6 text-xs text-zinc-500">
                <p>{{ $turnaround }}</p>
            </div>
        @endif

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
