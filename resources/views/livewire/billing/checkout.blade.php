<div class="mx-auto max-w-xl px-4 py-12">
    <flux:heading size="xl" class="mb-8 text-center">Checkout</flux:heading>

    <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading size="lg" class="mb-4">Order Summary</flux:heading>

        <div class="mb-6 space-y-3">
            <div class="flex justify-between">
                <span class="text-zinc-600 dark:text-zinc-400">Service</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $formTypeName }}</span>
            </div>

            <div class="flex justify-between">
                <span class="text-zinc-600 dark:text-zinc-400">{{ $stateCount === 1 ? 'State' : 'Number of States'
                    }}</span>
                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stateCount }}</span>
            </div>

            <div class="border-t border-zinc-200 pt-3 dark:border-zinc-700">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">Selected {{ $stateCount === 1 ? 'State' : 'States'
                    }}:</div>
                <div class="mt-1 flex flex-wrap gap-2">
                    @foreach ($selectedStates as $stateCode)
                    <flux:badge size="sm">{{ config("states.{$stateCode}") }}</flux:badge>
                    @endforeach
                </div>
            </div>
        </div>

        <flux:separator class="my-6" />

        @if ($isSubscription)
        <div class="mb-6">
            <div class="flex justify-between text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                <span>Subscription</span>
                <span>$199.00/{{ $subscriptionInterval }}</span>
            </div>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Your subscription will renew automatically each {{ $subscriptionInterval }}. You can cancel anytime.
            </p>
        </div>
        @else
        <div class="mb-6 flex justify-between text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            <span>Total</span>
            <span>${{ number_format($stateCount * 29.99, 2) }}</span>
        </div>
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
            $29.99 per state (one-time payment)
        </p>
        @endif

        <flux:button wire:click="checkout" variant="primary" class="w-full">
            @if ($isSubscription)
            Start Subscription
            @else
            Complete Purchase
            @endif
        </flux:button>

        <p class="mt-4 text-center text-xs text-zinc-400 dark:text-zinc-500">
            Secure payment powered by Stripe
        </p>
    </div>
</div>