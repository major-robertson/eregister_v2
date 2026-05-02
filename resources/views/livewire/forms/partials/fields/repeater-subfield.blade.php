@php
    /**
     * Render a single repeater sub-field inside the repeater modal.
     *
     * Variables in scope:
     *   - $subKey   (string)    schema key for this sub-field
     *   - $subField (array)     schema entry for this sub-field
     *
     * Used by both the grouped (`schema_groups`) and flat (`schema`)
     * paths in repeater.blade.php so the @switch lives in one place.
     */
    $subLabel = $subField['label'] ?? ucwords(str_replace('_', ' ', $subKey));
    $subType = $subField['type'] ?? 'text';
@endphp

@switch($subType)
    @case('percent')
        <flux:field>
            <flux:label>{{ $subLabel }}</flux:label>
            <div class="relative">
                <flux:input
                    type="number"
                    wire:model="repeaterForm.{{ $subKey }}"
                    min="0"
                    max="100"
                    step="0.01"
                />
                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400">%</span>
            </div>
            <flux:error name="repeaterForm.{{ $subKey }}" />
        </flux:field>
        @break

    @case('checkbox')
        <flux:field>
            <label class="flex items-center gap-2">
                <flux:checkbox wire:model="repeaterForm.{{ $subKey }}" />
                <span>{{ $subLabel }}</span>
            </label>
            <flux:error name="repeaterForm.{{ $subKey }}" />
        </flux:field>
        @break

    @case('email')
        <flux:field>
            <flux:label>{{ $subLabel }}</flux:label>
            <flux:input
                type="email"
                wire:model="repeaterForm.{{ $subKey }}"
                placeholder="{{ $subField['placeholder'] ?? '' }}"
            />
            <flux:error name="repeaterForm.{{ $subKey }}" />
        </flux:field>
        @break

    @case('date')
        <flux:field>
            <flux:label>{{ $subLabel }}</flux:label>
            <flux:input type="date" wire:model="repeaterForm.{{ $subKey }}" />
            <flux:error name="repeaterForm.{{ $subKey }}" />
        </flux:field>
        @break

    @case('select')
        <flux:field>
            <flux:label>{{ $subLabel }}</flux:label>
            <flux:select wire:model="repeaterForm.{{ $subKey }}">
                <flux:select.option value="">Select...</flux:select.option>
                @foreach ($subField['options'] ?? [] as $optValue => $optLabel)
                    <flux:select.option value="{{ $optValue }}">{{ $optLabel }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="repeaterForm.{{ $subKey }}" />
        </flux:field>
        @break

    @case('address')
        {{-- Hide the address widget's outer label inside a repeater
             modal: the parent schema_groups card already supplies a
             title (e.g. "Home Address"), so showing the widget label
             below it would render the same string twice immediately
             above the "Street Address" sub-field. --}}
        @include('livewire.forms.partials.fields.address', [
            'fieldKey' => $subKey,
            'field' => $subField,
            'prefix' => 'repeaterForm',
            'label' => $subLabel,
            'hideLabel' => true,
        ])
        @break

    @default
        <flux:field>
            <flux:label>{{ $subLabel }}</flux:label>
            <flux:input
                wire:model="repeaterForm.{{ $subKey }}"
                placeholder="{{ $subField['placeholder'] ?? '' }}"
                :mask="$subField['mask'] ?? null"
            />
            <flux:error name="repeaterForm.{{ $subKey }}" />
        </flux:field>
@endswitch
