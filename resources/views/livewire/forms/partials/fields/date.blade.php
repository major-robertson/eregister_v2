<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label>{{ $label }}</flux:label>
    @if (! empty($field['help']))
        <flux:description>{{ $field['help'] }}</flux:description>
    @endif
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
</flux:field>
