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
                    {{ $selectedCount }} {{ Str::plural('seat', $selectedCount) }},
                    {{ $interval === 'yearly' ? 'billed yearly' : 'billed monthly' }}
                    <span class="block text-xs text-zinc-500">
                        {{ $unitFormatted }}/{{ $perLabel }} per seat{{ $interval === 'yearly' ? ', two months free vs monthly' : ', cancel anytime' }}
                    </span>
                </span>
                <span class="whitespace-nowrap">{{ $amountFormatted }}/{{ $perLabel }}</span>
            </div>

            {{-- Seat picker: owners/admins cover any mix of the team. The
                 PaymentIntent is bound to the total at creation, so once
                 payment is initialized the selection is locked — "change"
                 is a full reload, like the interval toggle. --}}
            @if ($canPickSeats && $members->count() > 1)
                <div class="space-y-2 border-t border-zinc-200 pt-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">Who gets a seat?</p>
                    @foreach ($members as $member)
                        <label class="flex items-center gap-3 rounded-lg border border-zinc-200 px-3 py-2 {{ $isReady ? 'opacity-60' : 'cursor-pointer hover:border-zinc-300' }}">
                            <input
                                type="checkbox"
                                value="{{ $member->id }}"
                                wire:model.live="seatUserIds"
                                @if ($isReady) disabled @endif
                                class="size-4 rounded border-zinc-300 text-blue-600"
                            />
                            <span class="min-w-0 flex-1">
                                <span class="block truncate font-medium text-zinc-900">{{ $member->name }}</span>
                                <span class="block truncate text-xs text-zinc-500">{{ $member->email }}</span>
                            </span>
                            <span class="shrink-0 text-xs uppercase tracking-wide text-zinc-400">{{ $member->pivot->role }}</span>
                        </label>
                    @endforeach
                    @error('seatUserIds')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @if ($isReady)
                        <a href="{{ route('lien.waivers.subscribe', ['interval' => $interval]) }}" class="inline-block text-xs text-zinc-500 underline hover:text-zinc-700">
                            Change selection
                        </a>
                    @endif
                    <p class="text-xs text-zinc-400">You can add or remove seats anytime after subscribing — changes are prorated.</p>
                </div>
            @endif

            <ul class="space-y-1.5 border-t border-zinc-200 pt-3 text-zinc-600">
                <li class="flex items-center gap-2">
                    <flux:icon name="check" class="h-4 w-4 shrink-0 text-green-600" />
                    Unlimited waivers for every seat holder
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

        @if (! $isReady)
            <flux:button wire:click="proceedToPayment" wire:loading.attr="disabled" variant="primary" class="w-full">
                <span wire:loading.remove wire:target="proceedToPayment">Continue to payment</span>
                <span wire:loading wire:target="proceedToPayment">Setting up payment...</span>
            </flux:button>
        @else
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
        @endif

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
