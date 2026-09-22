<div class="mx-auto max-w-lg space-y-6">
    @if ($step === 'order')
        <div class="text-center">
            <flux:heading size="xl">Your order</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Sales &amp; use tax registration, prepared and filed for you</flux:text>
        </div>

        <x-ui.card>
            {{-- States --}}
            <div class="space-y-2 text-sm">
                @foreach ($stateNames as $code => $name)
                    <div class="flex items-center justify-between text-zinc-700 dark:text-zinc-300">
                        <span>{{ $name }} registration</span>
                        <span>{{ $perStateFormatted }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Processing speed --}}
            <div class="mt-6">
                <flux:radio.group wire:model.live="processing" label="Processing speed" variant="cards" :indicator="false" class="max-sm:flex-col">
                    <flux:radio value="standard" label="Standard" description="Prepared and filed within 5 business days. Included." />
                    @if ($rushFormatted)
                        <flux:radio value="rush" label="Rush" description="Prepared and filed within 2 business days. {{ $rushFormatted }} more." />
                    @endif
                </flux:radio.group>
            </div>

            {{-- Totals --}}
            <div class="mt-6 space-y-2 border-t border-zinc-200 pt-4 text-sm dark:border-zinc-700">
                <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                    <span>{{ $stateCount }} {{ \Illuminate\Support\Str::plural('state', $stateCount) }} &times; {{ $perStateFormatted }}</span>
                    <span>{{ $statesSubtotal }}</span>
                </div>
                @if ($processing === 'rush' && $rushFormatted)
                    <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Rush processing</span>
                        <span>{{ $rushFormatted }}</span>
                    </div>
                @endif
                <div class="flex items-center justify-between text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    <span>Total</span>
                    <span>{{ $formattedPrice }}</span>
                </div>
            </div>

            <flux:button wire:click="continueToPayment" variant="primary" icon:trailing="arrow-right" class="mt-6 w-full justify-center">
                Continue to payment
            </flux:button>

            <div class="mt-6 rounded-lg bg-zinc-50 p-4 text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                <p class="font-medium text-zinc-900 dark:text-zinc-100">What happens next</p>
                <ol class="mt-2 list-decimal space-y-1 pl-5">
                    <li>Pay securely by card.</li>
                    <li>Answer about 10 minutes of questions about your business. Save and come back any time.</li>
                    <li>We review your answers, file with {{ $stateCount === 1 ? 'the state' : 'each state' }} and email you the permit number.</li>
                </ol>
                <p class="mt-3">Our fee is refunded in full until we file with the state.</p>
            </div>
        </x-ui.card>

        <div class="text-center">
            <a href="{{ $changeStatesUrl }}" class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300" wire:navigate>
                Change states
            </a>
        </div>
    @else
        <div class="text-center">
            <flux:heading size="xl">Secure payment</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Sales &amp; Use Tax Permit Registration</flux:text>
        </div>

        <x-ui.card>
            {{-- Order summary --}}
            <div class="mb-6 space-y-2 text-sm">
                <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                    <span>{{ $stateCount }} {{ \Illuminate\Support\Str::plural('state', $stateCount) }} &times; {{ $perStateFormatted }}</span>
                    <span>{{ $statesSubtotal }}</span>
                </div>
                @if ($processing === 'rush' && $rushFormatted)
                    <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400">
                        <span>Rush processing (filed within 2 business days)</span>
                        <span>{{ $rushFormatted }}</span>
                    </div>
                @endif
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
                Our fee is refunded in full until we file with the state.
                By completing this purchase, you agree to our
                <a href="{{ route('terms-of-service') }}" class="underline" target="_blank">Terms of Service</a>
                and
                <a href="{{ route('privacy-policy') }}" class="underline" target="_blank">Privacy Policy</a>.
            </p>
        </x-ui.card>

        <div class="text-center">
            @if ($showsOrderLink)
                <a href="{{ $orderUrl }}" class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300">
                    Back to order options
                </a>
            @else
                <a
                    href="{{ route('sales-tax.registrations.show', $application) }}"
                    class="text-sm text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
                    wire:navigate
                >
                    Cancel and return to application
                </a>
            @endif
        </div>
    @endif
</div>
