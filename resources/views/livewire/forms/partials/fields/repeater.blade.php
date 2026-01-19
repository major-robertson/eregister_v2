@php
    $items = data_get($data, $fieldKey, []);
    $min = $field['min'] ?? 0;
    $itemLabel = $field['item_label'] ?? 'Item';
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <flux:label class="text-base font-medium">{{ $label }}</flux:label>
        <flux:button
            wire:click="addRepeaterItem('{{ $fieldKey }}')"
            wire:loading.attr="disabled"
            wire:target="addRepeaterItem('{{ $fieldKey }}')"
            type="button"
            size="sm"
            variant="ghost"
        >
            <span wire:loading.remove wire:target="addRepeaterItem('{{ $fieldKey }}')">+ Add {{ $itemLabel }}</span>
            <span wire:loading wire:target="addRepeaterItem('{{ $fieldKey }}')">Adding...</span>
        </flux:button>
    </div>

    @error("{$prefix}.{$fieldKey}")
        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
    @enderror

    <div class="space-y-4">
        @forelse ($items as $index => $item)
            <div wire:key="repeater-{{ $item['_id'] ?? $index }}" class="rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="mb-3 flex items-center justify-between">
                    <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                        {{ $itemLabel }} {{ $index + 1 }}
                    </span>
                    @if (count($items) > $min)
                        <flux:button
                            wire:click="removeRepeaterItem('{{ $fieldKey }}', '{{ $item['_id'] ?? '' }}')"
                            wire:loading.attr="disabled"
                            wire:target="removeRepeaterItem('{{ $fieldKey }}', '{{ $item['_id'] ?? '' }}')"
                            type="button"
                            size="sm"
                            variant="ghost"
                            class="text-red-600"
                        >
                            <span wire:loading.remove wire:target="removeRepeaterItem('{{ $fieldKey }}', '{{ $item['_id'] ?? '' }}')">Remove</span>
                            <span wire:loading wire:target="removeRepeaterItem('{{ $fieldKey }}', '{{ $item['_id'] ?? '' }}')">Removing...</span>
                        </flux:button>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach ($field['schema'] ?? [] as $subKey => $subField)
                        @php
                            $subWireModel = "{$prefix}.{$fieldKey}.{$index}.{$subKey}";
                            $subLabel = $subField['label'] ?? ucwords(str_replace('_', ' ', $subKey));
                            $subType = $subField['type'] ?? 'text';
                        @endphp

                        @switch($subType)
                            @case('text')
                                <flux:field>
                                    <flux:label>{{ $subLabel }}</flux:label>
                                    <flux:input wire:model="{{ $subWireModel }}" placeholder="{{ $subField['placeholder'] ?? '' }}" />
                                    @error($subWireModel)
                                        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                                    @enderror
                                </flux:field>
                                @break

                            @case('percent')
                                <flux:field>
                                    <flux:label>{{ $subLabel }}</flux:label>
                                    <div class="relative">
                                        <flux:input
                                            type="number"
                                            wire:model="{{ $subWireModel }}"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                        />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400">%</span>
                                    </div>
                                    @error($subWireModel)
                                        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                                    @enderror
                                </flux:field>
                                @break

                            @case('checkbox')
                                <flux:field>
                                    <label class="flex items-center gap-2">
                                        <flux:checkbox wire:model.live="{{ $subWireModel }}" />
                                        <span>{{ $subLabel }}</span>
                                    </label>
                                    @error($subWireModel)
                                        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                                    @enderror
                                </flux:field>
                                @break

                            @case('email')
                                <flux:field>
                                    <flux:label>{{ $subLabel }}</flux:label>
                                    <flux:input type="email" wire:model="{{ $subWireModel }}" />
                                    @error($subWireModel)
                                        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                                    @enderror
                                </flux:field>
                                @break

                            @case('date')
                                <flux:field>
                                    <flux:label>{{ $subLabel }}</flux:label>
                                    <flux:input type="date" wire:model="{{ $subWireModel }}" />
                                    @error($subWireModel)
                                        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                                    @enderror
                                </flux:field>
                                @break

                            @default
                                <flux:field>
                                    <flux:label>{{ $subLabel }}</flux:label>
                                    <flux:input wire:model="{{ $subWireModel }}" />
                                    @error($subWireModel)
                                        <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                                    @enderror
                                </flux:field>
                        @endswitch
                    @endforeach

                    {{-- State-specific person fields (if this is the responsible_people repeater) --}}
                    @if ($fieldKey === 'responsible_people' && !empty($statePersonFields ?? []))
                        @foreach ($statePersonFields as $stateCode => $stateInfo)
                            <div class="mt-4 border-t border-neutral-200 pt-4 dark:border-neutral-700">
                                <flux:heading size="sm" class="mb-3 text-neutral-600 dark:text-neutral-400">
                                    {{ $stateInfo['name'] }} Requirements
                                </flux:heading>

                                @foreach ($stateInfo['fields'] as $stateFieldKey => $stateField)
                                    @php
                                        $stateWireModel = "{$prefix}.{$fieldKey}.{$index}.{$stateFieldKey}";
                                        $stateFieldLabel = $stateField['label'] ?? ucwords(str_replace('_', ' ', $stateFieldKey));
                                        $stateFieldType = $stateField['type'] ?? 'text';
                                    @endphp

                                    <flux:field class="mb-3">
                                        <flux:label>{{ $stateFieldLabel }}</flux:label>
                                        @switch($stateFieldType)
                                            @case('date')
                                                <flux:input type="date" wire:model="{{ $stateWireModel }}" />
                                                @break
                                            @default
                                                <flux:input
                                                    wire:model="{{ $stateWireModel }}"
                                                    placeholder="{{ $stateField['placeholder'] ?? '' }}"
                                                />
                                        @endswitch
                                        @error($stateWireModel)
                                            <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                                        @enderror
                                        @if (!empty($stateField['help']))
                                            <flux:text class="text-sm text-neutral-500">{{ $stateField['help'] }}</flux:text>
                                        @endif
                                    </flux:field>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-600">
                No {{ strtolower($itemLabel) }}s added yet. Click "Add {{ $itemLabel }}" to add one.
            </div>
        @endforelse
    </div>
</div>
