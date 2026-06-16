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
     *
     * The locations[] principal row is system-managed: its address
     * mirrors the Principal Business Address (read-only here, edited on
     * the Contact & Address step) and its is_principal flag can't be
     * toggled. Non-principal rows never show the flag at all — exactly
     * one principal exists by construction.
     */
    $subLabel = $subField['label'] ?? ucwords(str_replace('_', ' ', $subKey));
    $subType = $subField['type'] ?? 'text';

    $isLocationsRepeater = ($this->editingRepeaterField ?? '') === 'locations';
    $editingPrincipalRow = $isLocationsRepeater && ! empty($this->repeaterForm['is_principal']);
@endphp

@if ($isLocationsRepeater && $subKey === 'is_principal')
    @if ($editingPrincipalRow)
        <flux:callout icon="building-office-2">
            <flux:callout.heading>Principal business location</flux:callout.heading>
            <flux:callout.text>
                This is your principal business location. It always uses the
                Principal Business Address from the Contact &amp; Address step.
            </flux:callout.text>
        </flux:callout>
    @endif
@elseif ($editingPrincipalRow && $subKey === 'address')
    {{-- Same address widget as every other location, but disabled: the
         principal row mirrors the Principal Business Address and is edited
         on the Contact & Address step. A notification explains why. --}}
    <div class="space-y-3">
        <flux:callout icon="lock-closed" variant="secondary">
            <flux:callout.text>
                This address mirrors your Principal Business Address and can't be
                edited here.
                <button
                    type="button"
                    class="font-medium text-primary underline underline-offset-2"
                    wire:click="jumpToCoreStep('contact_and_address')"
                >
                    Edit it on the Contact &amp; Address step.
                </button>
            </flux:callout.text>
        </flux:callout>
        @include('livewire.forms.partials.fields.address', [
            'fieldKey' => $subKey,
            'field' => $subField,
            'prefix' => 'repeaterForm',
            'label' => $subLabel,
            'hideLabel' => true,
            'disabled' => true,
        ])
    </div>
@else
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
        @php
            $subOptions = $subField['options'] ?? [];
            $skipSubField = false;

            // `<<selected_states>>` token: resolve to the application's
            // selected states (e.g. temporary_events[].state_code).
            if ($subOptions === '<<selected_states>>') {
                $subOptions = collect($this->application->selected_states ?? [])
                    ->mapWithKeys(fn ($code) => [$code => config("states.{$code}", $code)])
                    ->all();
            }

            // `<<row_state_counties>>` token: county list keyed by the
            // row's own address state (locations[].county). Hides the
            // selector when the state has no configured county list.
            if ($subOptions === '<<row_state_counties>>') {
                $rowState = data_get($this->repeaterForm, 'address.state');
                $counties = $rowState ? config("counties.{$rowState}", []) : [];
                $skipSubField = $counties === [];
                $subOptions = $skipSubField ? [] : array_combine($counties, $counties);
            }
        @endphp
        @if (! $skipSubField)
            <flux:field>
                <flux:label>{{ $subLabel }}</flux:label>
                <flux:select wire:model="repeaterForm.{{ $subKey }}">
                    <flux:select.option value="">Select...</flux:select.option>
                    @foreach ($subOptions as $optValue => $optLabel)
                        <flux:select.option value="{{ $optValue }}">{{ $optLabel }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="repeaterForm.{{ $subKey }}" />
            </flux:field>
        @endif
        @break

    @case('radio')
        <flux:field>
            <flux:label>{{ $subLabel }}</flux:label>
            <flux:radio.group
                wire:model.live="repeaterForm.{{ $subKey }}"
                variant="segmented"
                class="w-full sm:w-fit"
            >
                @foreach ($subField['options'] ?? [] as $optValue => $optLabel)
                    <flux:radio value="{{ $optValue }}" label="{{ $optLabel }}" />
                @endforeach
            </flux:radio.group>
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
                :autocomplete="! empty($subField['sensitive']) ? 'off' : null"
            />
            <flux:error name="repeaterForm.{{ $subKey }}" />
        </flux:field>
@endswitch
@endif
