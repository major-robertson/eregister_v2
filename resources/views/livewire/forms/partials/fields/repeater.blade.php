@php
    $items = data_get($data, $fieldKey, []);
    $min = $field['min'] ?? 0;
    $itemLabel = $field['item_label'] ?? 'Item';
    $schema = $field['schema'] ?? [];
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <flux:label class="text-base font-medium">{{ $label }}</flux:label>
        <flux:button
            wire:click="openRepeaterModal('{{ $fieldKey }}')"
            type="button"
            size="sm"
            variant="primary"
            icon="plus"
        >
            Add {{ $itemLabel }}
        </flux:button>
    </div>

    @error("{$prefix}.{$fieldKey}")
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror

    <div class="space-y-3">
        @forelse ($items as $index => $item)
            <div wire:key="repeater-{{ $item['_id'] ?? $index }}" class="flex items-start justify-between rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <button
                    type="button"
                    wire:click="openRepeaterModal('{{ $fieldKey }}', {{ $index }})"
                    class="flex-1 text-left"
                >
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $item[$schema ? array_key_first($schema) : ''] ?? $itemLabel . ' ' . ($index + 1) }}
                        </span>
                        @php
                            $secondKey = array_keys($schema)[1] ?? null;
                        @endphp
                        @if ($secondKey && !empty($item[$secondKey]))
                            <flux:badge size="sm">{{ $item[$secondKey] }}</flux:badge>
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
                    @if (count($items) > $min)
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
        @empty
            <div class="rounded-lg border-2 border-dashed border-zinc-300 px-5 py-6 dark:border-zinc-600">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $itemLabel }}</span>
                        @if ($min > 0)
                            <flux:badge size="sm" color="red">Required</flux:badge>
                        @endif
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
        @endforelse
    </div>

    {{-- Repeater Modal --}}
    <flux:modal wire:model="showRepeaterModal" class="max-w-lg">
        <div class="space-y-4">
            <flux:heading>{{ $this->editingRepeaterIndex !== null ? 'Edit' : 'Add' }} {{ $itemLabel }}</flux:heading>

            <div class="space-y-4" @keydown.enter.prevent="$wire.saveRepeaterItem()">
                @foreach ($schema as $subKey => $subField)
                    @php
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
                                <flux:input type="email" wire:model="repeaterForm.{{ $subKey }}" />
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

                        @default
                            <flux:field>
                                <flux:label>{{ $subLabel }}</flux:label>
                                <flux:input wire:model="repeaterForm.{{ $subKey }}" placeholder="{{ $subField['placeholder'] ?? '' }}" />
                                <flux:error name="repeaterForm.{{ $subKey }}" />
                            </flux:field>
                    @endswitch
                @endforeach

                {{-- State-specific person fields --}}
                @if ($fieldKey === 'responsible_people' && !empty($statePersonFields ?? []))
                    @foreach ($statePersonFields as $stateCode => $stateInfo)
                        <flux:separator />
                        <flux:heading size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $stateInfo['name'] }} Requirements
                        </flux:heading>

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
                    @endforeach
                @endif

                <div class="flex justify-end gap-3 pt-4">
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
