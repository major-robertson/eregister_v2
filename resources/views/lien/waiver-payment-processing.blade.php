<x-layouts.portal title="Processing Payment">
    <div class="mx-auto max-w-lg space-y-6 px-6 py-10">
        <x-ui.card>
            @if ($attempts < 20)
                {{-- No JSON polling route exists for waivers, so "polling" is a
                     timed re-request of the confirmation URL itself. The
                     controller re-checks the webhook + Stripe on every load
                     and renders success as soon as either lands. --}}
                <div class="space-y-4 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                        <flux:icon name="arrow-path" class="h-8 w-8 animate-spin text-amber-600" />
                    </div>

                    <flux:heading size="lg">Processing your payment...</flux:heading>

                    <flux:text class="text-zinc-600">
                        Please wait while we confirm your payment. This usually takes just a few seconds.
                    </flux:text>

                    <flux:text class="text-sm text-zinc-500">
                        This page refreshes automatically until payment is confirmed.
                    </flux:text>
                </div>

                <script data-navigate-once>
                    setTimeout(() => { window.location.replace(@js($retryUrl)); }, 3000);
                </script>
            @else
                <div class="space-y-4 text-center">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100">
                        <flux:icon name="clock" class="h-8 w-8 text-amber-600" />
                    </div>

                    <flux:heading size="lg">Still confirming your payment</flux:heading>

                    <flux:text class="text-zinc-600">
                        Your payment is taking longer than usual to confirm. If your card was
                        charged, your subscription will activate automatically, so check back in a
                        few minutes. Otherwise you can retry checkout below.
                    </flux:text>

                    <div class="flex justify-center gap-4 pt-2">
                        <flux:button href="{{ route('lien.waivers.index') }}" variant="primary">
                            Go to Lien Waivers
                        </flux:button>
                        <flux:button href="{{ route('lien.waivers.subscribe') }}" variant="ghost">
                            Back to checkout
                        </flux:button>
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>
</x-layouts.portal>
