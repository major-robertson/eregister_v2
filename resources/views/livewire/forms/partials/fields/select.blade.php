@php
    $rawOptions = $field['options'] ?? [];
    $isGrouped = ! empty($rawOptions) && collect($rawOptions)->contains(fn ($v) => is_array($v));
@endphp
<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label :badge="$badge['label'] ?? null" :badge-color="$badge['color'] ?? null">
        {{ $label }}
    </flux:label>
    @if (! empty($resolvedHelp))
        @include('livewire.forms.partials.field-help', ['help' => $resolvedHelp])
    @endif
    <flux:select wire:model.live="{{ $wireModel }}" name="{{ $wireModel }}">
        <flux:select.option value="">Select...</flux:select.option>
        @if ($isGrouped)
            @foreach ($rawOptions as $groupLabel => $groupOptions)
                @if (is_array($groupOptions))
                    <optgroup label="{{ $groupLabel }}">
                        @foreach ($groupOptions as $value => $optionLabel)
                            <flux:select.option value="{{ $value }}">{{ $optionLabel }}</flux:select.option>
                        @endforeach
                    </optgroup>
                @else
                    {{-- Mixed array: top-level option alongside groups. --}}
                    <flux:select.option value="{{ $groupLabel }}">{{ $groupOptions }}</flux:select.option>
                @endif
            @endforeach
        @else
            @foreach ($rawOptions as $value => $optionLabel)
                <flux:select.option value="{{ $value }}">{{ $optionLabel }}</flux:select.option>
            @endforeach
        @endif
    </flux:select>
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
</flux:field>
