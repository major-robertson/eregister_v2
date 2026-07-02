@php
    $rawOptions = $field['options'] ?? [];

    // `<<selected_states>>` token: resolve to the application's selected
    // states at render time (used by e.g. temporary_events[].state_code).
    if ($rawOptions === '<<selected_states>>') {
        $rawOptions = collect($this->application->selected_states ?? [])
            ->mapWithKeys(fn ($code) => [$code => config("states.{$code}", $code)])
            ->all();
    }

    $isGrouped = ! empty($rawOptions) && collect($rawOptions)->contains(fn ($v) => is_array($v));

    // Long flat lists (states, counties) get a searchable combobox; short
    // or grouped lists keep the native select (combobox has no optgroups).
    $useCombobox = ! $isGrouped && count($rawOptions) > 15;
@endphp
<flux:field wire:key="field-{{ $wireModel }}">
    <flux:label :badge="$badge['label'] ?? null" :badge-color="$badge['color'] ?? null">
        {{ $label }}
    </flux:label>
    @if (! empty($resolvedHelp))
        @include('livewire.forms.partials.field-help', ['help' => $resolvedHelp])
    @endif
    @if ($useCombobox)
        <flux:select
            variant="combobox"
            clearable
            placeholder="Select..."
            wire:model.live="{{ $wireModel }}"
            name="{{ $wireModel }}"
        >
            @foreach ($rawOptions as $value => $optionLabel)
                <flux:select.option value="{{ $value }}">{{ $optionLabel }}</flux:select.option>
            @endforeach
        </flux:select>
    @else
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
    @endif
    @error($wireModel)
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror
</flux:field>
