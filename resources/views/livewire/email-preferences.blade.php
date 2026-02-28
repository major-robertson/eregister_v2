<div class="space-y-6">
    @if($saved)
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-700">
            Your preferences have been saved.
        </div>
    @endif

    <div class="space-y-4">
        @foreach($categoryLabels as $key => $label)
            <div class="flex items-center justify-between rounded-lg border border-zinc-200 p-4">
                <div>
                    <p class="font-medium text-zinc-900">{{ $label }}</p>
                    <p class="mt-1 text-sm text-zinc-500">
                        @if($key === 'abandon_checkout')
                            Reminders about incomplete orders and checkout sessions.
                        @elseif($key === 'marketing')
                            Promotional offers, product updates, and tips.
                        @endif
                    </p>
                </div>
                <flux:switch
                    wire:model.live="categories.{{ $key }}"
                    wire:change="toggleCategory('{{ $key }}')"
                    :disabled="$unsubscribedFromAll"
                    :checked="!$categories[$key]"
                />
            </div>
        @endforeach
    </div>

    <div class="border-t border-zinc-200 pt-6">
        <div class="flex items-center justify-between rounded-lg border border-red-200 bg-red-50 p-4">
            <div>
                <p class="font-medium text-zinc-900">Unsubscribe from all emails</p>
                <p class="mt-1 text-sm text-zinc-500">
                    You will only receive essential transactional emails like payment receipts.
                </p>
            </div>
            <flux:switch
                wire:model.live="unsubscribedFromAll"
                wire:change="toggleUnsubscribeAll"
            />
        </div>
    </div>
</div>
