@php
    use App\Domains\Forms\Engine\Applicability;

    /**
     * Matrix field: one input row per applicable state.
     *
     * Variables in scope (from field.blade.php):
     *   - $fieldKey, $field, $prefix, $data, $label
     *
     * The field label may contain {state_name}; the heading shows it with
     * a generic substitution while each row substitutes its own state.
     */
    $selectedStates = $this->application->selected_states ?? [];
    $rowStates = Applicability::statesFor($field, $selectedStates);
    $cellType = $field['cell_type'] ?? 'text';
    $allowSameForAll = ($field['allow_same_for_all'] ?? false) && count($rowStates) > 1;

    $headingLabel = $field['label'] ?? ucwords(str_replace('_', ' ', $fieldKey));
    // "Date you will start collecting sales tax in {state_name}" reads
    // best as "... in each state" at the heading level.
    $headingLabel = str_replace(['in {state_name}', '{state_name}'], ['in each state', 'each state'], $headingLabel);
@endphp

@if (count($rowStates) > 0)
<div class="space-y-3" wire:key="matrix-{{ $prefix }}-{{ $fieldKey }}">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <flux:label class="text-base font-medium">{{ $headingLabel }}</flux:label>
        @if ($allowSameForAll)
            <flux:button
                type="button"
                size="xs"
                variant="ghost"
                wire:click="applyMatrixValueToAllStates('{{ $fieldKey }}')"
            >
                Same for all states
            </flux:button>
        @endif
    </div>

    @if (! empty($resolvedHelp ?? ($field['help'] ?? null)))
        @include('livewire.forms.partials.field-help', ['help' => $resolvedHelp ?? $field['help']])
    @endif

    <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
        @foreach ($rowStates as $rowState)
            @php
                $cellModel = "{$prefix}.{$fieldKey}.{$rowState}";
                $rowStateName = config("states.{$rowState}", $rowState);
            @endphp
            <div
                wire:key="matrix-row-{{ $fieldKey }}-{{ $rowState }}"
                class="flex items-center gap-4 border-b border-zinc-200 px-4 py-3 last:border-b-0 dark:border-zinc-700 {{ $loop->even ? 'bg-zinc-50 dark:bg-zinc-800/50' : '' }}"
            >
                <div class="w-36 shrink-0 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                    {{ $rowStateName }}
                </div>
                <div class="flex-1">
                    @switch($cellType)
                        @case('date')
                            <flux:input type="date" wire:model="{{ $cellModel }}" name="{{ $cellModel }}" />
                            @break

                        @case('percent')
                            <div class="relative">
                                <flux:input type="number" wire:model="{{ $cellModel }}" name="{{ $cellModel }}" min="0" max="100" step="0.01" />
                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400">%</span>
                            </div>
                            @break

                        @default
                            <flux:input
                                wire:model="{{ $cellModel }}"
                                name="{{ $cellModel }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                :mask="$field['cell_mask'] ?? null"
                            />
                    @endswitch
                    @error($cellModel)
                        <flux:text class="mt-1 text-sm text-red-500">{{ $message }}</flux:text>
                    @enderror
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
