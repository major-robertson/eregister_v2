@php
    $stateName = config("states.{$stateCode}") ?? $stateCode;
    $fieldLabel = str_replace('{state_name}', $stateName, $field['label'] ?? 'Additional Information Per Person');
@endphp

<div class="space-y-4">
    <flux:label class="text-base font-medium">{{ $fieldLabel }}</flux:label>

    @forelse ($responsiblePeople as $person)
        @php $personId = $person['_id'] ?? null; @endphp
        @if ($personId)
            <div wire:key="person-extra-{{ $personId }}" class="rounded-lg border border-neutral-200 p-4 dark:border-neutral-700">
                <div class="mb-3 font-medium">{{ $person['full_name'] ?? 'Person' }}</div>

                <div class="space-y-4">
                    @foreach ($field['schema'] ?? [] as $subKey => $subField)
                        @php
                            $wireModel = "stateData.responsible_people_extra.{$personId}.{$subKey}";
                            $subLabel = $subField['label'] ?? ucwords(str_replace('_', ' ', $subKey));
                            $subType = $subField['type'] ?? 'text';
                        @endphp

                        <flux:field>
                            <flux:label>{{ $subLabel }}</flux:label>
                            @switch($subType)
                                @case('date')
                                    <flux:input type="date" wire:model="{{ $wireModel }}" />
                                    @break
                                @default
                                    <flux:input
                                        wire:model="{{ $wireModel }}"
                                        placeholder="{{ $subField['placeholder'] ?? '' }}"
                                    />
                            @endswitch
                            @error($wireModel)
                                <flux:text class="text-sm text-red-500">{{ $message }}</flux:text>
                            @enderror
                        </flux:field>
                    @endforeach
                </div>
            </div>
        @endif
    @empty
        <div class="rounded-lg border border-dashed border-neutral-300 p-8 text-center text-neutral-500 dark:border-neutral-600">
            No responsible people added yet. Please add them in the Core Info section.
        </div>
    @endforelse
</div>
