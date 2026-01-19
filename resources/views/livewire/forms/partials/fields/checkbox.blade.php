<flux:field wire:key="field-{{ $wireModel }}">
    <label class="flex items-center gap-2">
        <flux:checkbox wire:model.live="{{ $wireModel }}" name="{{ $wireModel }}" />
        <span>{{ $label }}</span>
    </label>
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
    @if (!empty($field['help']))
        <flux:text class="text-sm text-neutral-500">{{ $field['help'] }}</flux:text>
    @endif
</flux:field>
