@php
    $items = data_get($data, $fieldKey, []);
    $min = $field['min'] ?? 0;
    $itemLabel = $field['item_label'] ?? 'Item';
    $schema = $field['schema'] ?? [];

    $effectiveMin = $min;
    if (!empty($field['conditional_min'])) {
        $condField = $field['conditional_min']['field'];
        $condValues = $field['conditional_min']['values'] ?? [];
        $currentValue = $data[$condField] ?? null;
        if ($currentValue !== null && isset($condValues[$currentValue])) {
            $effectiveMin = max($min, $condValues[$currentValue]);
        }
    }
@endphp

<div class="space-y-4">
    <div>
        <flux:label class="text-base font-medium">{{ $label }}</flux:label>
    </div>

    @error("{$prefix}.{$fieldKey}")
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror

    <div class="space-y-3">
        @foreach ($items as $index => $item)
            <div wire:key="repeater-{{ $item['_id'] ?? $index }}" class="flex items-start justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <button
                    type="button"
                    wire:click="openRepeaterModal('{{ $fieldKey }}', {{ $index }})"
                    class="flex-1 text-left"
                >
                    @php
                        // Repeaters that hold person records (responsible_people, members)
                        // always have first_name + last_name in their schema; combine them.
                        // Use `title` as the badge when present (e.g. "Owner", "Member").
                        $hasFirstLast = isset($schema['first_name']) && isset($schema['last_name']);
                        if ($hasFirstLast) {
                            $combined = trim(($item['first_name'] ?? '') . ' ' . ($item['last_name'] ?? ''));
                            $primaryLabel = $combined !== '' ? $combined : ($itemLabel . ' ' . ($index + 1));
                            $badgeKey = isset($schema['title']) ? 'title' : null;
                        } else {
                            // Non-person repeaters fall back to "first schema key" display.
                            $primaryLabel = $item[$schema ? array_key_first($schema) : ''] ?? ($itemLabel . ' ' . ($index + 1));
                            $badgeKey = array_keys($schema)[1] ?? null;
                        }
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $primaryLabel }}
                        </span>
                        @if ($badgeKey && !empty($item[$badgeKey]))
                            <flux:badge size="sm">{{ $item[$badgeKey] }}</flux:badge>
                        @endif
                    </div>
                    @if (!empty($item['ownership_percent']))
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $item['ownership_percent'] }}% ownership
                        </div>
                    @endif
                </button>

                <div class="ml-4 flex items-center gap-1">
                    <flux:button
                        wire:click="openRepeaterModal('{{ $fieldKey }}', {{ $index }})"
                        type="button"
                        size="sm"
                        variant="ghost"
                        icon="pencil"
                    />
                    @if (count($items) > $effectiveMin)
                        <flux:button
                            wire:click="removeRepeaterItem('{{ $fieldKey }}', '{{ $item['_id'] ?? '' }}')"
                            wire:loading.attr="disabled"
                            wire:target="removeRepeaterItem('{{ $fieldKey }}', '{{ $item['_id'] ?? '' }}')"
                            wire:confirm="Are you sure you want to remove this {{ strtolower($itemLabel) }}?"
                            type="button"
                            size="sm"
                            variant="ghost"
                            icon="trash"
                            class="text-red-600 hover:text-red-700"
                        />
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Empty required slots for positions not yet filled --}}
        @for ($i = count($items); $i < $effectiveMin; $i++)
            <div class="rounded-lg border-2 border-dashed border-zinc-300 px-5 py-6 dark:border-zinc-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $itemLabel }} {{ $i + 1 }}</span>
                        <flux:badge size="sm" color="red">Required</flux:badge>
                    </div>
                    <flux:button
                        wire:click="openRepeaterModal('{{ $fieldKey }}')"
                        type="button"
                        size="sm"
                        variant="primary"
                        icon="plus"
                    >
                        Add
                    </flux:button>
                </div>
            </div>
        @endfor
    </div>

    {{-- Optional add row --}}
    @if (count($items) >= $effectiveMin)
        <div class="rounded-lg border border-dashed border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <span class="text-sm text-zinc-400 dark:text-zinc-500">Add another {{ strtolower($itemLabel) }} (optional)</span>
                <flux:button
                    wire:click="openRepeaterModal('{{ $fieldKey }}')"
                    type="button"
                    size="sm"
                    variant="ghost"
                    icon="plus"
                >
                    Add
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Repeater Modal.
         Wider than the default flux:modal so the inline-row pairs in
         schema_groups (first/last name, phone/email, dob/ssn, etc.)
         have breathing room. The internal layout is a flex column with
         a scrollable body and a non-scrolling footer so the Save /
         Cancel actions and the error-overview callout stay visible
         even when the form is taller than the viewport. --}}
    <flux:modal wire:model="showRepeaterModal" class="max-w-3xl">
        @php
            // Errors raised inside saveRepeaterItem() are namespaced
            // 'repeaterForm.*'. Counting them separately keeps the
            // modal's overview in sync with what the user can fix
            // here, without bleeding in step-level errors that belong
            // to the page behind the modal.
            $repeaterErrorKeys = collect($errors->keys())
                ->filter(fn ($k) => str_starts_with($k, 'repeaterForm.'))
                ->values();
            $repeaterErrorCount = $repeaterErrorKeys->count();
        @endphp

        <div class="flex max-h-[85vh] flex-col">
            <div class="border-b border-zinc-200 pb-4 dark:border-zinc-700">
                <flux:heading>{{ $this->editingRepeaterIndex !== null ? 'Edit' : 'Add' }} {{ $itemLabel }}</flux:heading>
            </div>

            <div
                class="-mx-6 flex-1 space-y-4 overflow-y-auto px-6 py-4"
                @keydown.enter.prevent="$wire.saveRepeaterItem()"
            >
                @if (! empty($field['schema_groups']))
                    @include('livewire.forms.partials.grouped-fields', [
                        'groups' => $field['schema_groups'],
                        'visibleFields' => $schema,
                        'fieldPartial' => 'livewire.forms.partials.fields.repeater-subfield',
                        'fieldContext' => [],
                        'sectionWrapper' => 'card',
                        'headingSize' => 'base',
                    ])
                @else
                    {{-- Flat layout: render every schema entry in
                         declaration order, wrapped in a single card so
                         the modal still gets the soft-surface treatment
                         instead of bare fields against the modal body. --}}
                    <x-ui.card>
                        <div class="space-y-6">
                            @foreach ($schema as $subKey => $subField)
                                @include('livewire.forms.partials.fields.repeater-subfield', [
                                    'subKey' => $subKey,
                                    'subField' => $subField,
                                ])
                            @endforeach
                        </div>
                    </x-ui.card>
                @endif

                {{-- State-specific person fields: one card per state so
                     the visual rhythm matches the schema_groups cards
                     above instead of dropping back to separator-and-
                     heading bands. --}}
                @if ($fieldKey === 'responsible_people' && !empty($statePersonFields ?? []))
                    @foreach ($statePersonFields as $stateCode => $stateInfo)
                        <x-ui.card>
                            <flux:heading size="base" class="mb-4">
                                {{ $stateInfo['name'] }} Requirements
                            </flux:heading>

                            <div class="space-y-6">
                                @foreach ($stateInfo['fields'] as $stateFieldKey => $stateField)
                                    @php
                                        $stateFieldLabel = $stateField['label'] ?? ucwords(str_replace('_', ' ', $stateFieldKey));
                                        $stateFieldType = $stateField['type'] ?? 'text';
                                    @endphp

                                    <flux:field>
                                        <flux:label>{{ $stateFieldLabel }}</flux:label>
                                        @switch($stateFieldType)
                                            @case('date')
                                                <flux:input type="date" wire:model="repeaterForm.{{ $stateFieldKey }}" />
                                                @break
                                            @default
                                                <flux:input
                                                    wire:model="repeaterForm.{{ $stateFieldKey }}"
                                                    placeholder="{{ $stateField['placeholder'] ?? '' }}"
                                                />
                                        @endswitch
                                        <flux:error name="repeaterForm.{{ $stateFieldKey }}" />
                                        @if (!empty($stateField['help']))
                                            <flux:text class="text-sm text-zinc-500">{{ $stateField['help'] }}</flux:text>
                                        @endif
                                    </flux:field>
                                @endforeach
                            </div>
                        </x-ui.card>
                    @endforeach
                @endif
            </div>

            {{-- Sticky footer: error overview + actions. The flex
                 layout above (max-h-[85vh] + flex-1 scroll body) keeps
                 this region pinned to the bottom of the modal viewport
                 without needing position: sticky. The error overview
                 mirrors the main form's flux:callout so users get the
                 same "fix N fields" feedback without scrolling up. --}}
            <div class="space-y-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
                @if ($repeaterErrorCount > 0)
                    <flux:callout variant="danger" icon="exclamation-triangle">
                        <flux:callout.heading>
                            Please fix {{ $repeaterErrorCount }} {{ Str::plural('field', $repeaterErrorCount) }}
                        </flux:callout.heading>
                        <flux:callout.text>
                            Some required fields are missing or invalid. Each highlighted field above shows what to fix.
                        </flux:callout.text>
                    </flux:callout>
                @endif

                <div class="flex justify-end gap-3">
                    <flux:button type="button" wire:click="closeRepeaterModal" variant="ghost">
                        Cancel
                    </flux:button>
                    <flux:button type="button" wire:click="saveRepeaterItem" variant="primary">
                        {{ $this->editingRepeaterIndex !== null ? 'Save Changes' : 'Add ' . $itemLabel }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:modal>
</div>
