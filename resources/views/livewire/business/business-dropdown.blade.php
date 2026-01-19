<div class="w-full">
    <flux:dropdown class="w-full">
        <flux:button variant="ghost" class="w-full justify-between" icon-trailing="chevron-down">
            <span class="flex items-center gap-2">
                <span class="flex size-6 shrink-0 items-center justify-center rounded bg-accent-content text-xs font-semibold text-white">
                    {{ substr($currentBusiness?->name ?? 'B', 0, 1) }}
                </span>
                <span class="truncate">{{ $currentBusiness?->name ?? 'Select Business' }}</span>
            </span>
        </flux:button>

        <flux:menu class="w-64">
            @foreach ($businesses as $business)
                <flux:menu.item
                    wire:key="business-{{ $business->id }}"
                    wire:click="switchBusiness({{ $business->id }})"
                    class="cursor-pointer"
                >
                    <div class="flex items-center gap-2">
                        <span class="flex size-6 shrink-0 items-center justify-center rounded bg-zinc-200 text-xs font-semibold text-zinc-700">
                            {{ substr($business->name ?? 'B', 0, 1) }}
                        </span>
                        <span class="truncate">{{ $business->name }}</span>
                        @if ($currentBusiness && $business->id === $currentBusiness->id)
                            <flux:icon name="check" class="ml-auto size-4 text-primary" />
                        @endif
                    </div>
                </flux:menu.item>
            @endforeach

            <flux:menu.separator />

            <flux:menu.item href="{{ route('portal.select-business') }}" icon="plus" wire:navigate>
                Add new business
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
