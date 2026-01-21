<div class="max-w-lg mx-auto space-y-6">
    <x-ui.page-header title="Checkout">
        <x-slot:breadcrumbs>
            <x-ui.breadcrumb :items="[
                ['label' => 'Lien Projects', 'url' => route('lien.projects.index')],
                ['label' => $filing->project->name, 'url' => route('lien.projects.show', $filing->project)],
                ['label' => 'Checkout'],
            ]" />
        </x-slot:breadcrumbs>
    </x-ui.page-header>

    <x-ui.card>
        <x-slot:header>Order Summary</x-slot:header>

        <div class="space-y-4">
            <x-ui.info-list>
                <x-ui.info-list.item label="Document">
                    {{ $filing->documentType->name }}
                </x-ui.info-list.item>
                <x-ui.info-list.item label="Project">
                    {{ $filing->project->name }}
                </x-ui.info-list.item>
                <x-ui.info-list.item label="Service Level">
                    {{ $filing->service_level->label() }}
                </x-ui.info-list.item>
                @if($filing->amount_claimed_cents)
                    <x-ui.info-list.item label="Amount Claimed">
                        {{ $filing->formattedAmountClaimed() }}
                    </x-ui.info-list.item>
                @endif
            </x-ui.info-list>

            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <div class="flex justify-between items-center text-lg font-semibold">
                    <span>Total</span>
                    <span>{{ $formattedPrice }}</span>
                </div>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <x-slot:header>Payment</x-slot:header>

        <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4">
            You will be redirected to Stripe to complete your payment securely.
        </p>

        <flux:button
            wire:click="checkout"
            variant="primary"
            class="w-full justify-center py-3"
        >
            Pay {{ $formattedPrice }}
        </flux:button>

        <p class="text-xs text-zinc-500 text-center mt-4">
            By completing this purchase, you agree to our Terms of Service and Privacy Policy.
        </p>
    </x-ui.card>

    <div class="text-center">
        <flux:button
            href="{{ route('lien.projects.show', $filing->project) }}"
            variant="ghost"
        >
            Cancel
        </flux:button>
    </div>
</div>
