<div class="w-full">
    <flux:dropdown class="w-full" position="bottom" align="start">
        <button
            type="button"
            class="flex w-full cursor-pointer items-center gap-2.5 rounded-lg border border-border bg-white px-2.5 py-2 text-start shadow-xs transition-colors hover:border-zinc-300"
        >
            <span class="flex size-6 shrink-0 items-center justify-center rounded-md bg-primary/12 text-[13px] font-bold text-primary">
                {{ mb_strtoupper(mb_substr($currentBusiness?->name ?? 'B', 0, 1)) }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-semibold text-text-primary">{{ $currentBusiness?->name ?? 'Select business' }}</span>
                <span class="block text-[11px] leading-tight text-text-secondary">{{ __('Switch business') }}</span>
            </span>
            <flux:icon name="chevrons-up-down" class="size-4 shrink-0 text-text-secondary" />
        </button>

        <flux:menu class="w-64">
            @foreach ($businesses as $business)
                <flux:menu.item
                    wire:key="business-{{ $business->id }}"
                    wire:click="switchBusiness({{ $business->id }})"
                    class="cursor-pointer"
                >
                    <div class="flex items-center gap-2">
                        <span class="flex size-6 shrink-0 items-center justify-center rounded bg-primary/12 text-xs font-semibold text-primary">
                            {{ mb_strtoupper(mb_substr($business->name ?? 'B', 0, 1)) }}
                        </span>
                        <span class="truncate">{{ $business->name }}</span>
                        @if ($currentBusiness && $business->id === $currentBusiness->id)
                            <flux:icon name="check" class="ml-auto size-4 text-primary" />
                        @endif
                    </div>
                </flux:menu.item>
            @endforeach

            <flux:menu.separator />

            <flux:modal.trigger name="create-business">
                <flux:menu.item icon="plus" class="cursor-pointer text-primary">
                    {{ __('Add new business') }}
                </flux:menu.item>
            </flux:modal.trigger>
        </flux:menu>
    </flux:dropdown>

    <flux:modal name="create-business" class="md:w-96">
        <form wire:submit="createBusiness" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Add new business') }}</flux:heading>
                <flux:text class="mt-2">{{ __("We'll switch you over and walk you through the rest of its details.") }}</flux:text>
            </div>

            <flux:input
                wire:model="newBusinessName"
                :label="__('Business name')"
                placeholder="Acme Builders LLC"
                required
            />

            <div class="flex items-center justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Create business') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
