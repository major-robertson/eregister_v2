<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label>{{ $label }}</flux:label>
    @if (! empty($field['help']))
        @include('livewire.forms.partials.field-help', ['help' => $field['help']])
    @endif
    @if ($needsLive)
        <flux:input
            wire:model.live="{{ $wireModel }}"
            type="{{ $inputType ?? 'text' }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            name="{{ $wireModel }}"
            :mask="$field['mask'] ?? null"
        />
    @else
        <flux:input
            wire:model="{{ $wireModel }}"
            type="{{ $inputType ?? 'text' }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            name="{{ $wireModel }}"
            :mask="$field['mask'] ?? null"
        />
    @endif
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
</flux:field>
