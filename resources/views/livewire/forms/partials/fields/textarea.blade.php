<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label :badge="$badge['label'] ?? null" :badge-color="$badge['color'] ?? null">
        {{ $label }}
    </flux:label>
    @if (! empty($resolvedHelp))
        @include('livewire.forms.partials.field-help', ['help' => $resolvedHelp])
    @endif
    @if ($needsLive)
        <flux:textarea
            wire:model.live="{{ $wireModel }}"
            rows="{{ $field['rows'] ?? 3 }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            name="{{ $wireModel }}"
        />
    @else
        <flux:textarea
            wire:model="{{ $wireModel }}"
            rows="{{ $field['rows'] ?? 3 }}"
            placeholder="{{ $field['placeholder'] ?? '' }}"
            name="{{ $wireModel }}"
        />
    @endif
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
</flux:field>
