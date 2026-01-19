<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label>{{ $label }}</flux:label>
    <flux:select wire:model.live="{{ $wireModel }}" name="{{ $wireModel }}">
        <flux:select.option value="">Select...</flux:select.option>
        @foreach ($field['options'] ?? [] as $value => $optionLabel)
            <flux:select.option value="{{ $value }}">{{ $optionLabel }}</flux:select.option>
        @endforeach
    </flux:select>
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
    @if (!empty($field['help']))
        <flux:text class="text-sm text-neutral-500">{{ $field['help'] }}</flux:text>
    @endif
</flux:field>
