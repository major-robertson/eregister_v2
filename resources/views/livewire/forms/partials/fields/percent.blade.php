<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label>{{ $label }}</flux:label>
    <div class="relative">
        @if ($needsLive)
            <flux:input
                type="number"
                wire:model.live="{{ $wireModel }}"
                min="0"
                max="100"
                step="0.01"
                placeholder="0"
                class="pr-8"
                name="{{ $wireModel }}"
            />
        @else
            <flux:input
                type="number"
                wire:model="{{ $wireModel }}"
                min="0"
                max="100"
                step="0.01"
                placeholder="0"
                class="pr-8"
                name="{{ $wireModel }}"
            />
        @endif
        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400">%</span>
    </div>
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
    @if (!empty($field['help']))
        <flux:text class="text-sm text-neutral-500">{{ $field['help'] }}</flux:text>
    @endif
</flux:field>
