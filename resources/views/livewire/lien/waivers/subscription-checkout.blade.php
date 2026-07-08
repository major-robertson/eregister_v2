<div class="mx-auto max-w-md space-y-6 px-6 py-10">
    <div class="text-center">
        <flux:heading size="xl">Secure payment</flux:heading>
        <flux:text class="mt-1 text-zinc-500">Lien Waiver Subscription</flux:text>
    </div>

    {{-- Interval toggle. Plain anchors (no wire:navigate) on purpose: the
         PaymentIntent is bound to one price, so switching intervals must be
         a full redirect that re-runs mount and initializes a fresh one. --}}
    <div class="mx-auto grid max-w-xs grid-cols-2 rounded-lg border border-zinc-200 bg-zinc-100 p-1 text-center text-sm">
        <a
            href="{{ route('lien.waivers.subscribe', ['interval' => 'monthly']) }}"
            class="rounded-md px-3 py-1.5 {{ $interval === 'monthly' ? 'bg-white font-medium text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}"
        >
            Monthly
        </a>
        <a
            href="{{ route('lien.waivers.subscribe', ['interval' => 'yearly']) }}"
            class="rounded-md px-3 py-1.5 {{ $interval === 'yearly' ? 'bg-white font-medium text-zinc-900 shadow-sm' : 'text-zinc-500 hover:text-zinc-700' }}"
        >
            Yearly
            <span class="ml-1 rounded-full bg-green-100 px-1.5 py-0.5 text-[10px] font-medium text-green-700">
                2 months free
            </span>
        </a>
    </div>

    <x-ui.card>
        <div class="mb-6 space-y-3 text-sm">
            <div class="flex items-start justify-between text-zinc-600">
                <span>
                    {{ $interval === 'yearly' ? 'Annual' : 'Monthly' }} subscription
                    <span class="block text-xs text-zinc-500">
                        {{ $interval === 'yearly' ? '$990/year, two months free vs monthly' : '$99/month, cancel anytime' }}
                    </span>
                </span>
                <span class="whitespace-nowrap">{{ $amountFormatted }}/{{ $perLabel }}</span>
            </div>

            <ul class="space-y-1.5 border-t border-zinc-200 pt-3 text-zinc-600">
                <li class="flex items-center gap-2">
                    <flux:icon name="check" class="h-4 w-4 shrink-0 text-green-600" />
                    Unlimited saved waivers
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon name="check" class="h-4 w-4 shrink-0 text-green-600" />
                    E-signature send &amp; collect
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon name="check" class="h-4 w-4 shrink-0 text-green-600" />
                    Automatic signer reminders
                </li>
                <li class="flex items-center gap-2">
                    <flux:icon name="check" class="h-4 w-4 shrink-0 text-green-600" />
                    Signed-copy storage + audit certificates
                </li>
            </ul>

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
            The subscription renews at {{ $amountFormatted }}/{{ $interval === 'yearly' ? 'year' : 'month' }} and can be canceled anytime.
            By completing this purchase, you agree to our
            <a href="{{ route('terms-of-service') }}" class="underline" target="_blank">Terms of Service</a>
            and
            <a href="{{ route('privacy-policy') }}" class="underline" target="_blank">Privacy Policy</a>.
        </p>
    </x-ui.card>

    <div class="text-center">
        <a href="{{ route('lien.waivers.index') }}" class="text-sm text-zinc-500 hover:text-zinc-700">
            Cancel and go back
        </a>
    </div>
</div>
