@php
    use App\Domains\Forms\Engine\Applicability;

    /**
     * anywhere_states field: one yes/no question plus, when "yes" and
     * more than one applicable state, a checklist limited to the
     * applicable ∩ selected states. With exactly one applicable state
     * the selection is implied (save-time normalization fills it).
     *
     * Variables in scope (from field.blade.php):
     *   - $fieldKey, $field, $prefix, $data, $label, $resolvedHelp
     */
    $selectedStates = $this->application->selected_states ?? [];
    $applicable = Applicability::statesFor($field, $selectedStates);
    $anywhere = (string) data_get($data, "{$fieldKey}.anywhere", '');
    $showPicker = $anywhere === '1' && count($applicable) > 1;
@endphp

@if (count($applicable) > 0)
<div class="space-y-3" wire:key="aws-{{ $prefix }}-{{ $fieldKey }}">
    <flux:field>
        <flux:label :badge="$badge['label'] ?? null" :badge-color="$badge['color'] ?? null">
            {{ $label }}
        </flux:label>
        @if (! empty($resolvedHelp))
            @include('livewire.forms.partials.field-help', ['help' => $resolvedHelp])
        @endif
        <flux:radio.group
            wire:model.live="{{ $prefix }}.{{ $fieldKey }}.anywhere"
            variant="segmented"
            class="w-full sm:w-fit"
        >
            <flux:radio value="1" label="Yes" />
            <flux:radio value="0" label="No" />
        </flux:radio.group>
        @error("{$prefix}.{$fieldKey}.anywhere")
            <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
        @enderror
    </flux:field>

    @if ($showPicker)
        <flux:field class="ml-1 border-l-2 border-zinc-200 pl-4 dark:border-zinc-700">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <flux:label>In which states?</flux:label>
                <div class="flex gap-1">
                    <flux:button
                        type="button"
                        size="xs"
                        variant="ghost"
                        x-on:click="$wire.set('{{ $prefix }}.{{ $fieldKey }}.states', @js($applicable))"
                    >
                        All
                    </flux:button>
                    <flux:button
                        type="button"
                        size="xs"
                        variant="ghost"
                        x-on:click="$wire.set('{{ $prefix }}.{{ $fieldKey }}.states', [])"
                    >
                        Clear
                    </flux:button>
                </div>
            </div>
            <flux:checkbox.group
                wire:model.live="{{ $prefix }}.{{ $fieldKey }}.states"
                class="grid grid-cols-2 gap-2 sm:grid-cols-3"
            >
                @foreach ($applicable as $stateCode)
                    <flux:checkbox
                        wire:key="aws-{{ $fieldKey }}-{{ $stateCode }}"
                        value="{{ $stateCode }}"
                        label="{{ config('states.'.$stateCode, $stateCode) }}"
                    />
                @endforeach
            </flux:checkbox.group>
            @error("{$prefix}.{$fieldKey}.states")
                <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
            @enderror
        </flux:field>
    @elseif ($anywhere === '1' && count($applicable) === 1)
        <flux:text class="ml-1 border-l-2 border-zinc-200 pl-4 text-sm text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
            Applies to {{ config('states.'.$applicable[0], $applicable[0]) }}.
        </flux:text>
    @endif
</div>
@endif
