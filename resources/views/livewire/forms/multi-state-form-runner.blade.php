<div class="mx-auto max-w-3xl px-4 py-8">
    {{-- Progress indicator: each segment fills proportionally to step
         progress within its phase, so multi-step phases (Core Info,
         State Details across N states) actually show movement as the
         user advances. --}}
    {{-- Progress indicator. The State Details segment only renders when
         at least one selected state actually has state-specific
         questions — selections fully covered by shared answers run
         Core → Review. --}}
    <div class="mb-8">
        <div class="mb-2 flex items-center justify-between text-sm">
            <span class="font-medium {{ $isCore ? 'text-primary' : 'text-text-secondary' }}">
                Core Info
                @if ($isCore && $phaseProgress['core']['total'] > 1)
                    ({{ $phaseProgress['core']['current'] }}/{{ $phaseProgress['core']['total'] }})
                @endif
            </span>
            @if ($hasStateQuestions)
                <span class="font-medium {{ $isStates ? 'text-primary' : 'text-text-secondary' }}">
                    @if ($isStates)
                        {{ $currentStateName }} ({{ $stateProgress['current'] }}/{{ $stateProgress['total'] }})
                    @else
                        State Details
                    @endif
                </span>
            @endif
            <span class="font-medium {{ $isReview ? 'text-primary' : 'text-text-secondary' }}">Review</span>
        </div>
        <div class="flex gap-1">
            <div class="relative h-2 flex-1 overflow-hidden rounded bg-zinc-200">
                <div
                    class="absolute inset-y-0 left-0 rounded bg-primary transition-all duration-300"
                    style="width: {{ $phaseProgress['core']['fill'] }}%"
                ></div>
            </div>
            @if ($hasStateQuestions)
                <div class="relative h-2 flex-1 overflow-hidden rounded bg-zinc-200">
                    <div
                        class="absolute inset-y-0 left-0 rounded bg-primary transition-all duration-300"
                        style="width: {{ $phaseProgress['states']['fill'] }}%"
                    ></div>
                </div>
            @endif
            <div class="relative h-2 flex-1 overflow-hidden rounded bg-zinc-200">
                <div
                    class="absolute inset-y-0 left-0 rounded bg-primary transition-all duration-300"
                    style="width: {{ $phaseProgress['review']['fill'] }}%"
                ></div>
            </div>
        </div>
    </div>

    @if ($isReview)
        {{-- Review Phase --}}
        <div class="space-y-8">
            <flux:heading size="xl" class="text-center">Review Your Application</flux:heading>
            <p class="text-center text-text-secondary">
                Please review all your information before submitting.
            </p>

            {{-- Shared answers (asked once). The "shared across states"
                 framing only applies to multi-state sales tax filings;
                 formations (LLC, corporation, etc.) file in a single
                 state, so they get a plain "Your Information" header. --}}
            @php
                $isMultiState = $this->application->form_type === \App\Domains\Forms\Models\SalesTaxRegistration::FORM_TYPE;
            @endphp
            <x-ui.card>
                <x-slot:header>
                    <flux:heading size="lg">{{ $isMultiState ? 'Shared Answers' : 'Your Information' }}</flux:heading>
                </x-slot:header>
                @if ($isMultiState)
                    <p class="mb-4 text-sm text-text-secondary">
                        Answered once — applied to every state in your application.
                    </p>
                @endif
                @include('livewire.forms.partials.answer-summary', [
                    'data' => $this->coreData,
                    'exclude' => ['responsible_people'],
                ])

                @if (!empty($this->coreData['responsible_people']))
                    <div class="mt-6">
                        <flux:heading size="base" class="mb-3">Responsible People</flux:heading>
                        @foreach ($this->coreData['responsible_people'] as $person)
                            @php
                                $displayName = trim(($person['first_name'] ?? '') . ' ' . ($person['last_name'] ?? ''));
                                $displayName = $displayName !== '' ? $displayName : 'Person';
                            @endphp
                            <div class="mb-2 rounded border border-border p-3">
                                <p class="font-medium text-text-primary">{{ $displayName }}</p>
                                <p class="text-sm text-text-secondary">Ownership: {{ $person['ownership_percent'] ?? 0 }}%</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>

            {{-- Per-State Summary (state-only answers) — only relevant for
                 sales tax registrations, where each state is a separate filing.
                 Formations (LLC, corporation, etc.) file in a single state. --}}
            @if ($isMultiState)
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
                    @if (empty($stateDataForReview) || collect($stateDataForReview)->except('responsible_people_extra')->filter()->isEmpty())
                        <p class="text-sm text-text-secondary">
                            No state-specific questions — your shared answers cover everything {{ config("states.{$stateCode}") }} needs.
                        </p>
                    @else
                        @include('livewire.forms.partials.answer-summary', [
                            'data' => $stateDataForReview,
                            'exclude' => ['responsible_people_extra'],
                            'stripPrefix' => strtolower($stateCode).'_',
                        ])
                    @endif
                </x-ui.card>
            @endforeach
            @endif

            <div class="flex justify-between pt-4">
                <flux:button wire:click="previousStep" type="button" variant="ghost">
                    Back
                </flux:button>
                <x-ui.action-button
                    wire:click="submit"
                    type="button"
                    :disabled="!$allStatesComplete"
                >
                    Proceed to Payment
                </x-ui.action-button>
            </div>
        </div>
    @else
        @php
            $stepFields = $currentStep['fields'] ?? [];
            $stepGroups = $currentStep['groups'] ?? null;
            $mailingAddressField = $stepFields['mailing_address'] ?? null;
            $hasMailingAddressFields = isset($stepFields['mailing_address_same']) || $mailingAddressField;
            $mailingFieldKeys = ['mailing_address', 'mailing_address_same'];
        @endphp

        <form
            wire:submit="nextStep"
            class="space-y-6"
            x-data
            x-on:validation-failed.window="
                $nextTick(() => {
                    const target = $el.querySelector('[data-flux-control][data-invalid], [data-flux-control].is-invalid, .text-red-500')
                        ?? $el.querySelector('[name^=\'coreData.\']:invalid, [name^=\'stateData.\']:invalid');
                    target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            "
        >
            @if ($currentStep)
                <div class="mb-2">
                    <flux:heading size="lg">
                        {{ str_replace('{state_name}', $currentStateName ?? '', $currentStep['title'] ?? 'Step') }}
                    </flux:heading>
                    @if (!empty($currentStep['description']))
                        <p class="mt-1 text-text-secondary">
                            {{ str_replace('{state_name}', $currentStateName ?? '', $currentStep['description']) }}
                        </p>
                    @endif
                </div>
            @endif

            @php
                // Field context shared by every field include below —
                // both the grouped-fields partial and the no-groups
                // fallback path use the same shape, so it lives in one
                // place to keep them in lockstep.
                $fieldContext = [
                    'prefix' => $isCore ? 'coreData' : 'stateData',
                    'data' => $isCore ? $this->coreData : $this->stateData,
                    'stateCode' => $isCore ? null : $this->currentStateCode(),
                    'statePersonFields' => $statePersonFields ?? [],
                ];

                // Strip mailing-address fields from $visibleFields before
                // the grouped renderer runs; they have their own composite
                // partial and are handled at the call site below.
                $visibleFieldsForGroups = collect($visibleFields)
                    ->reject(fn ($_, $key) => in_array($key, $mailingFieldKeys, true))
                    ->all();
            @endphp

            @if ($stepGroups)
                {{-- The mailing-address composite renders as its own card
                     directly after the group that references the mailing
                     keys (usually "Address"), so later groups like
                     "Additional Contacts" don't wedge between them. --}}
                @php
                    // Group fields may use the inline-row pair syntax
                    // (['phone', 'email']); flatten before intersecting
                    // or the array entries blow up the string comparison.
                    $mailingGroupIndex = collect($stepGroups)->search(
                        fn ($g) => collect($g['fields'] ?? [])
                            ->flatMap(fn ($entry) => is_array($entry) ? $entry : [$entry])
                            ->intersect($mailingFieldKeys)
                            ->isNotEmpty()
                    );
                    $stepGroupReferencesMailing = $mailingGroupIndex !== false && $hasMailingAddressFields;
                    $groupsThroughMailing = $stepGroupReferencesMailing
                        ? array_slice($stepGroups, 0, $mailingGroupIndex + 1)
                        : $stepGroups;
                    $groupsAfterMailing = $stepGroupReferencesMailing
                        ? array_slice($stepGroups, $mailingGroupIndex + 1)
                        : [];
                @endphp

                @include('livewire.forms.partials.grouped-fields', [
                    'groups' => $groupsThroughMailing,
                    'visibleFields' => $visibleFieldsForGroups,
                    'fieldPartial' => 'livewire.forms.partials.field',
                    'fieldContext' => $fieldContext,
                    'sectionWrapper' => 'card',
                    'beforeFields' => 'livewire.forms.partials.loading-overlay',
                ])

                @if ($stepGroupReferencesMailing)
                    <x-ui.card class="relative">
                        @include('livewire.forms.partials.loading-overlay')
                        <div class="space-y-6">
                            @include('livewire.forms.partials.mailing-address', [
                                'mailingAddressField' => $mailingAddressField,
                            ])
                        </div>
                    </x-ui.card>
                @endif

                @if (! empty($groupsAfterMailing))
                    @include('livewire.forms.partials.grouped-fields', [
                        'groups' => $groupsAfterMailing,
                        'visibleFields' => $visibleFieldsForGroups,
                        'fieldPartial' => 'livewire.forms.partials.field',
                        'fieldContext' => $fieldContext,
                        'sectionWrapper' => 'card',
                        'beforeFields' => 'livewire.forms.partials.loading-overlay',
                    ])
                @endif

                {{-- Fallback card for any visible fields not claimed by a
                     group. State-specific definitions (e.g. TX) often
                     append fields beyond what base.php's groups list,
                     and without this catch-all those fields would render
                     nowhere. Server-side validation still requires them,
                     so the user would see "Please fix N fields above"
                     with no obvious place to fix them. --}}
                @php
                    $allGroupedKeys = collect($stepGroups)
                        ->flatMap(function ($g) {
                            return collect($g['fields'] ?? [])
                                ->flatMap(fn ($entry) => is_array($entry) ? $entry : [$entry]);
                        })
                        ->unique()
                        ->values()
                        ->all();

                    $orphanFields = collect($visibleFieldsForGroups)
                        ->reject(fn ($field, $key) => in_array($key, $allGroupedKeys, true))
                        ->all();
                @endphp
                @if (! empty($orphanFields))
                    <x-ui.card class="relative">
                        <flux:heading size="lg" class="mb-4">Additional Information</flux:heading>
                        <div class="space-y-6">
                            @foreach ($orphanFields as $fieldKey => $field)
                                @include('livewire.forms.partials.field', array_merge($fieldContext, [
                                    'fieldKey' => $fieldKey,
                                    'field' => $field,
                                    'drivesConditional' => $field['drives_conditional'] ?? false,
                                ]))
                            @endforeach
                        </div>
                    </x-ui.card>
                @endif
            @else
                {{-- No groups: single card (default behavior) --}}
                <x-ui.card class="relative">
                    @include('livewire.forms.partials.loading-overlay')

                    <div class="space-y-6">
                        @foreach ($visibleFieldsForGroups as $fieldKey => $field)
                            @include('livewire.forms.partials.field', array_merge($fieldContext, [
                                'fieldKey' => $fieldKey,
                                'field' => $field,
                                'drivesConditional' => $field['drives_conditional'] ?? false,
                            ]))
                        @endforeach

                        @if (empty($visibleFieldsForGroups) && !$hasMailingAddressFields)
                            <p class="text-center text-text-secondary">No fields to display in this step.</p>
                        @endif

                        @if ($hasMailingAddressFields)
                            @include('livewire.forms.partials.mailing-address', [
                                'mailingAddressField' => $mailingAddressField,
                            ])
                        @endif
                    </div>
                </x-ui.card>
            @endif

            {{-- Validation error summary: rendered when the server bounces
                 the step back with errors. Inline @error blocks live
                 next to each field, but on long steps (e.g. TX state
                 details has 40+ fields) those messages are scrolled
                 offscreen — the user clicks Next and sees nothing
                 change. The summary + scroll-to-error event ensures
                 they can't miss it. --}}
            @if ($errors->any())
                <flux:callout variant="danger" icon="exclamation-triangle">
                    <flux:callout.heading>
                        Please fix {{ $errors->count() }} {{ Str::plural('field', $errors->count()) }} above
                    </flux:callout.heading>
                    <flux:callout.text>
                        Some required fields are missing or invalid. Scroll up to review the highlighted entries.
                    </flux:callout.text>
                </flux:callout>
            @endif

            <div class="flex justify-between pt-4">
                @if ($isCore && $currentStepKey === ($stepKeys[0] ?? null))
                    {{-- First step: Previous returns to the state-selector page.
                         $stepKeys is numerically indexed (from array_keys), so
                         we compare against $stepKeys[0] not array_key_first(). --}}
                    <flux:button :href="$startUrl" type="button" variant="ghost">
                        Back to state selection
                    </flux:button>
                @else
                    <flux:button wire:click="previousStep" type="button" variant="ghost">
                        Previous
                    </flux:button>
                @endif

                <flux:button type="submit" variant="primary">
                    Next
                </flux:button>
            </div>
        </form>
    @endif
</div>
