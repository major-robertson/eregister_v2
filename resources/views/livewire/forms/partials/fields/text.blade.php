<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label>{{ $label }}</flux:label>
    @if ($needsLive)
        <flux:input
            wire:model.live="{{ $wireModel }}"
            type="{{ $inputType ?? 'text' }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            name="{{ $wireModel }}"
        />
    @else
        <flux:input
            wire:model="{{ $wireModel }}"
            type="{{ $inputType ?? 'text' }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
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
