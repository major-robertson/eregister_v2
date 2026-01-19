<div class="mx-auto max-w-3xl px-4 py-8">
    {{-- Progress indicator --}}
    <div class="mb-8">
        <div class="mb-2 flex items-center justify-between text-sm">
            <span class="font-medium {{ $isCore ? 'text-primary' : 'text-text-secondary' }}">Core Info</span>
            <span class="font-medium {{ $isStates ? 'text-primary' : 'text-text-secondary' }}">
                @if ($isStates)
                    {{ $currentStateName }} ({{ $stateProgress['current'] }}/{{ $stateProgress['total'] }})
                @else
                    State Details
                @endif
            </span>
            <span class="font-medium {{ $isReview ? 'text-primary' : 'text-text-secondary' }}">Review</span>
        </div>
        <div class="flex gap-1">
            <div class="h-2 flex-1 rounded {{ $isCore || $isStates || $isReview ? 'bg-primary' : 'bg-zinc-200' }}"></div>
            <div class="h-2 flex-1 rounded {{ $isStates || $isReview ? 'bg-primary' : 'bg-zinc-200' }}"></div>
            <div class="h-2 flex-1 rounded {{ $isReview ? 'bg-primary' : 'bg-zinc-200' }}"></div>
        </div>
    </div>

    @if ($isReview)
        {{-- Review Phase --}}
        <div class="space-y-8">
            <flux:heading size="xl" class="text-center">Review Your Application</flux:heading>
            <p class="text-center text-text-secondary">
                Please review all your information before submitting.
            </p>

            {{-- Core Data Summary --}}
            <x-ui.card>
                <x-slot:header>
                    <flux:heading size="lg">Business Information</flux:heading>
                </x-slot:header>
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($this->coreData as $key => $value)
                        @if (!is_array($value))
                            <div>
                                <dt class="text-sm text-text-secondary">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                                <dd class="font-medium text-text-primary">{{ $value ?: '-' }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                @if (!empty($this->coreData['responsible_people']))
                    <div class="mt-6">
                        <flux:heading size="base" class="mb-3">Responsible People</flux:heading>
                        @foreach ($this->coreData['responsible_people'] as $person)
                            <div class="mb-2 rounded border border-border p-3">
                                <p class="font-medium text-text-primary">{{ $person['full_name'] ?? 'Person' }}</p>
                                <p class="text-sm text-text-secondary">Ownership: {{ $person['ownership_percent'] ?? 0 }}%</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            {{-- Per-State Summary --}}
            @foreach ($this->application->selected_states as $stateCode)
                @php
                    $stateRecord = $this->application->stateRecord($stateCode);
                    $stateDataForReview = $stateRecord?->data ?? [];
                @endphp
                <x-ui.card>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <flux:heading size="lg">{{ config("states.{$stateCode}") }}</flux:heading>
                            @if ($stateRecord?->isComplete())
                                <flux:badge color="green" size="sm">Complete</flux:badge>
                            @else
                                <flux:badge color="yellow" size="sm">Incomplete</flux:badge>
                            @endif
                        </div>
                    </x-slot:header>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach ($stateDataForReview as $key => $value)
                            @if (!is_array($value))
                                <div>
                                    <dt class="text-sm text-text-secondary">{{ ucwords(str_replace('_', ' ', $key)) }}</dt>
                                    <dd class="font-medium text-text-primary">{{ $value ?: '-' }}</dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                </x-ui.card>
            @endforeach

            <div class="flex justify-between pt-4">
                <flux:button wire:click="previousStep" type="button" variant="ghost">
                    Back
                </flux:button>
                <flux:button
                    wire:click="submit"
                    type="button"
                    variant="primary"
                    :disabled="!$allStatesComplete"
                >
                    Submit Application
                </flux:button>
            </div>
        </div>
    @else
        {{-- Form Steps --}}
        <x-ui.card class="relative">
            {{-- Loading overlay for repeater actions --}}
            <div 
                wire:loading.flex 
                wire:target="addRepeaterItem, removeRepeaterItem"
                class="absolute inset-0 z-10 items-center justify-center bg-white/75 dark:bg-zinc-800/75"
            >
                <div class="flex items-center gap-2 text-text-secondary">
                    <svg class="h-5 w-5 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Updating...</span>
                </div>
            </div>

            @if ($currentStep)
                <flux:heading size="lg" class="mb-2">
                    {{ str_replace('{state_name}', $currentStateName ?? '', $currentStep['title'] ?? 'Step') }}
                </flux:heading>
                @if (!empty($currentStep['description']))
                    <p class="mb-6 text-text-secondary">
                        {{ str_replace('{state_name}', $currentStateName ?? '', $currentStep['description']) }}
                    </p>
                @endif
            @endif

            @php
                // Get mailing address field directly from step definition (not visible fields)
                // because we handle its visibility ourselves with the Yes/No toggle
                $stepFields = $currentStep['fields'] ?? [];
                $mailingAddressField = $stepFields['mailing_address'] ?? null;
                $mailingAddressSameField = $stepFields['mailing_address_same'] ?? null;
                $hasMailingAddressFields = $mailingAddressField || $mailingAddressSameField;
                
                // Filter out mailing address fields from main form (they're handled separately)
                $mainFields = collect($visibleFields)->filter(function($field, $key) {
                    return !in_array($key, ['mailing_address', 'mailing_address_same']);
                })->all();
            @endphp

            <form wire:submit="nextStep" class="space-y-6">
                @foreach ($mainFields as $fieldKey => $field)
                    @include('livewire.forms.partials.field', [
                        'fieldKey' => $fieldKey,
                        'field' => $field,
                        'prefix' => $isCore ? 'coreData' : 'stateData',
                        'data' => $isCore ? $this->coreData : $this->stateData,
                        'drivesConditional' => $field['drives_conditional'] ?? false,
                        'stateCode' => $isCore ? null : $this->currentStateCode(),
                        'statePersonFields' => $statePersonFields ?? [],
                    ])
                @endforeach

                @if (empty($mainFields) && !$hasMailingAddressFields)
                    <p class="text-center text-text-secondary">No fields to display in this step.</p>
                @endif

                @if ($hasMailingAddressFields)
                    @php
                        $mailingPrefix = $isCore ? 'coreData' : 'stateData';
                        $initialMailingValue = $isCore 
                            ? ($this->coreData['mailing_address_same'] ?? '1') 
                            : ($this->stateData['mailing_address_same'] ?? '1');
                        $showMailingInitial = $initialMailingValue === '0' || $initialMailingValue === 0 || $initialMailingValue === false;
                    @endphp
                    {{-- Mailing address section with Alpine for instant show/hide --}}
                    <div x-data="{ showMailing: {{ $showMailingInitial ? 'true' : 'false' }} }">
                        {{-- Different mailing address toggle --}}
                        <div class="rounded-lg border border-border bg-surface-secondary p-4">
                            <flux:radio.group 
                                wire:model.live="{{ $mailingPrefix }}.mailing_address_same"
                                x-on:change="showMailing = $event.target.value === '0'"
                                label="Do you have a different mailing address?" 
                                variant="segmented"
                            >
                                <flux:radio value="1" label="No" />
                                <flux:radio value="0" label="Yes" />
                            </flux:radio.group>
                        </div>

                        {{-- Mailing Address Card (shown instantly via Alpine) --}}
                        @if ($mailingAddressField)
                            <div x-show="showMailing" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="mt-6">
                                <x-ui.card>
                                    <flux:heading size="lg" class="mb-4">Mailing Address</flux:heading>
                                    @include('livewire.forms.partials.fields.address', [
                                        'fieldKey' => 'mailing_address',
                                        'field' => $mailingAddressField,
                                        'prefix' => $mailingPrefix,
                                        'label' => 'Mailing Address',
                                        'hideLabel' => true,
                                    ])
                                </x-ui.card>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="flex justify-between pt-4">
                    @if (!($isCore && $currentStepKey === array_key_first($stepKeys)))
                        <flux:button wire:click="previousStep" type="button" variant="ghost">
                            Previous
                        </flux:button>
                    @else
                        <div></div>
                    @endif

                    <flux:button type="submit" variant="primary">
                        Next
                    </flux:button>
                </div>
            </form>
        </x-ui.card>
    @endif
</div>
