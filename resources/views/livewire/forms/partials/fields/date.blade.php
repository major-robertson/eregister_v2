<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label>{{ $label }}</flux:label>
    @if ($needsLive)
        <flux:input
            type="date"
            wire:model.live="{{ $wireModel }}"
            name="{{ $wireModel }}"
        />
    @else
        <flux:input
            type="date"
            wire:model="{{ $wireModel }}"
            name="{{ $wireModel }}"
        />
    @endif
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
    @if (!empty($field['help']))
        <flux:text class="text-sm text-neutral-500">{{ $field['help'] }}</flux:text>
    @endif
</flux:field>
