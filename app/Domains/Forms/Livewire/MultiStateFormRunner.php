<?php

namespace App\Domains\Forms\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Engine\RulesBuilder;
use App\Domains\Forms\Engine\SensitiveDataProtector;
use App\Domains\Forms\Engine\Validation\CrossFieldValidatorRegistry;
use App\Domains\Forms\Engine\VisibleFieldResolver;
use App\Domains\Forms\Models\FormApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Component;

class MultiStateFormRunner extends Component
{
    public Business $business;

    public FormApplication $application;

    public array $coreData = [];

    public array $stateData = [];

    public string $currentPhase = 'core';

    public ?string $currentStepKey = null;

    public int $currentStateIndex = 0;

    private array $definition = [];

    /**
     * Base validation rules for Livewire.
     * Actual rules are built dynamically in validateCurrentStep().
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [];
    }

    public function boot(): void
    {
        // Reload definition on every request since it's a private property
        // and doesn't persist across Livewire requests
        if (isset($this->application) && $this->application->exists) {
            $this->loadDefinition();
        }
    }

    public function mount(FormApplication $application): void
    {
        $business = Auth::user()->currentBusiness();

        if (! $business) {
            $this->redirect(route('portal.select-business'));

            return;
        }

        Gate::authorize('update', $application);

        if ($application->isLocked()) {
            session()->flash('error', 'This application has been submitted and cannot be edited.');

            $this->redirect(route('dashboard'));

            return;
        }

        $this->business = $business;
        $this->application = $application;

        $this->loadDefinition();
        $this->loadData();
    }

    protected function loadDefinition(): void
    {
        if ($this->application->definition_snapshot) {
            $this->definition = $this->application->definition_snapshot;
        } else {
            $registry = app(FormRegistry::class);
            $this->definition = [
                'base' => $registry->getBase($this->application->form_type),
                'states' => [],
            ];

            foreach ($this->application->selected_states as $stateCode) {
                $this->definition['states'][$stateCode] = $registry->get($this->application->form_type, $stateCode);
            }
        }
    }

    protected function loadData(): void
    {
        $protector = app(SensitiveDataProtector::class);

        // Load core data
        $this->coreData = $protector->decryptCoreData(
            $this->application->core_data ?? [],
            $this->definition['base']
        );

        // Pre-fill from business profile on first load (empty core_data)
        if (empty($this->application->core_data)) {
            $this->prefillFromBusinessProfile();
        }

        // Load current phase and state
        $this->currentPhase = $this->application->current_phase ?? 'core';
        $this->currentStateIndex = $this->application->current_state_index ?? 0;

        // Load state data if in states phase
        if ($this->currentPhase === 'states' || $this->currentPhase === 'review') {
            $stateRecord = $this->application->currentStateRecord();
            if ($stateRecord) {
                $stateCode = $this->currentStateCode();
                $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
                $this->stateData = $protector->decryptStateData($stateRecord->data ?? [], $stateDef);
            }
        }

        // Set current step key
        $this->currentStepKey = $this->application->current_step_key ?? $this->getFirstStepKey();

        // Skip forward past any empty steps on initial load
        if ($this->currentPhase !== 'review') {
            $this->skipEmptyStepsForward();
            $this->updateApplicationPhase();
        }
    }

    /**
     * Pre-fill core data from the business profile.
     * Only non-sensitive fields are copied.
     */
    protected function prefillFromBusinessProfile(): void
    {
        $business = $this->business;

        // Pre-fill basic business info (fallback to name if legal_name not set)
        $legalName = $business->legal_name ?? $business->name;
        if ($legalName) {
            $this->coreData['legal_name'] = $legalName;
        }
        if ($business->dba_name) {
            $this->coreData['dba_name'] = $business->dba_name;
        }
        if ($business->entity_type) {
            $this->coreData['entity_type'] = $business->entity_type;
        }
        if ($business->business_address) {
            $this->coreData['business_address'] = $business->business_address;
        }
        if ($business->mailing_address) {
            $this->coreData['mailing_address'] = $business->mailing_address;
        }

        // Pre-fill responsible people (non-sensitive fields only)
        if (! empty($business->responsible_people)) {
            $this->coreData['responsible_people'] = $this->prepareResponsiblePeopleForForm(
                $business->responsible_people
            );
        }
    }

    /**
     * Prepare responsible people from business profile for form use.
     * Adds UUIDs for repeater tracking.
     *
     * @param  array<int, array>  $people
     * @return array<int, array>
     */
    protected function prepareResponsiblePeopleForForm(array $people): array
    {
        return array_map(fn (array $person) => array_merge($person, [
            '_id' => $person['_id'] ?? Str::uuid()->toString(),
        ]), $people);
    }

    protected function getFirstStepKey(): ?string
    {
        $steps = $this->getCurrentSteps();

        return array_key_first($steps) ?? null;
    }

    public function getCurrentSteps(): array
    {
        if ($this->currentPhase === 'core') {
            return $this->definition['base']['core_steps'] ?? [];
        }

        if ($this->currentPhase === 'states') {
            $stateCode = $this->currentStateCode();
            $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
            $steps = $stateDef['state_steps'] ?? [];

            // Skip state_responsible_people step - those fields are now in the core responsible_people repeater
            unset($steps['state_responsible_people']);

            return $steps;
        }

        // Review phase - return empty steps
        return [];
    }

    public function getCurrentStepProperty(): ?array
    {
        if ($this->currentPhase === 'review') {
            return null;
        }

        $steps = $this->getCurrentSteps();

        return $steps[$this->currentStepKey] ?? null;
    }

    public function getVisibleFieldsProperty(): array
    {
        $step = $this->getCurrentStepProperty();
        if (! $step) {
            return [];
        }

        $resolver = app(VisibleFieldResolver::class);

        return $resolver->resolve($step, $this->buildContext());
    }

    /**
     * Check if a specific step has visible fields.
     */
    protected function stepHasVisibleFields(array $step): bool
    {
        $resolver = app(VisibleFieldResolver::class);
        $visibleFields = $resolver->resolve($step, $this->buildContext());

        return count($visibleFields) > 0;
    }

    /**
     * Check if the current step has visible fields.
     */
    protected function currentStepHasVisibleFields(): bool
    {
        $step = $this->getCurrentStepProperty();
        if (! $step) {
            return false;
        }

        return $this->stepHasVisibleFields($step);
    }

    /**
     * Skip forward through empty steps until we find one with fields or reach the end.
     */
    protected function skipEmptyStepsForward(): void
    {
        $maxIterations = 50; // Prevent infinite loops
        $iterations = 0;

        while ($iterations < $maxIterations) {
            $iterations++;

            // If we're in review phase, we're done
            if ($this->currentPhase === 'review') {
                return;
            }

            // If current step has visible fields, we're done
            if ($this->currentStepHasVisibleFields()) {
                return;
            }

            // Otherwise, advance to next step/phase
            $stepKeys = array_keys($this->getCurrentSteps());
            $currentIndex = array_search($this->currentStepKey, $stepKeys);

            if ($currentIndex !== false && $currentIndex < count($stepKeys) - 1) {
                // Move to next step in current phase
                $this->currentStepKey = $stepKeys[$currentIndex + 1];
            } else {
                // Move to next phase or state
                $this->advancePhaseOrStateInternal();
            }
        }
    }

    /**
     * Skip backward through empty steps until we find one with fields or reach the beginning.
     */
    protected function skipEmptyStepsBackward(): void
    {
        $maxIterations = 50; // Prevent infinite loops
        $iterations = 0;

        while ($iterations < $maxIterations) {
            $iterations++;

            // If current step has visible fields, we're done
            if ($this->currentStepHasVisibleFields()) {
                return;
            }

            // Otherwise, go back to previous step/phase
            $stepKeys = array_keys($this->getCurrentSteps());
            $currentIndex = array_search($this->currentStepKey, $stepKeys);

            if ($currentIndex !== false && $currentIndex > 0) {
                // Move to previous step in current phase
                $this->currentStepKey = $stepKeys[$currentIndex - 1];
            } else {
                // Move to previous phase or state
                $this->goBackToPreviousPhaseOrStateInternal();

                // If we couldn't go back any further (still at core phase, first step), stop
                if ($this->currentPhase === 'core') {
                    $coreSteps = array_keys($this->definition['base']['core_steps'] ?? []);
                    if ($this->currentStepKey === ($coreSteps[0] ?? null)) {
                        return;
                    }
                }
            }
        }
    }

    public function currentStateCode(): ?string
    {
        return $this->application->selected_states[$this->currentStateIndex] ?? null;
    }

    /**
     * Get all state-specific person fields from selected states.
     * Used to display state-specific fields inline in the responsible_people repeater.
     */
    public function getStatePersonFieldsProperty(): array
    {
        $stateFields = [];

        foreach ($this->application->selected_states as $stateCode) {
            $stateDef = $this->definition['states'][$stateCode] ?? [];
            $personExtra = $stateDef['state_steps']['state_responsible_people']['fields']['responsible_people_extra']['schema'] ?? [];

            if (! empty($personExtra)) {
                $stateFields[$stateCode] = [
                    'name' => config("states.{$stateCode}", $stateCode),
                    'fields' => $personExtra,
                ];
            }
        }

        return $stateFields;
    }

    protected function buildContext(): array
    {
        return [
            'coreData' => $this->coreData,
            'stateData' => $this->stateData,
            'rowData' => [],
            'stateCode' => $this->currentStateCode(),
            'stateIndex' => $this->currentStateIndex,
            'selectedStates' => $this->application->selected_states,
        ];
    }

    protected function saveCoreData(): void
    {
        $protector = app(SensitiveDataProtector::class);
        $encrypted = $protector->encryptCoreData($this->coreData, $this->definition['base']);

        $this->application->update([
            'core_data' => $encrypted,
            'current_phase' => $this->currentPhase,
            'current_step_key' => $this->currentStepKey,
            'current_state_index' => $this->currentStateIndex,
        ]);

        // Persist marked fields back to the business profile
        $this->persistToBusinessProfile();
    }

    /**
     * Persist fields marked with persist_to_business back to the business model.
     * This allows form data to be reused in future applications.
     */
    protected function persistToBusinessProfile(): void
    {
        $businessData = [];

        // Iterate through all core steps to find persistable fields
        foreach ($this->definition['base']['core_steps'] ?? [] as $step) {
            foreach ($step['fields'] ?? [] as $fieldKey => $field) {
                if (! ($field['persist_to_business'] ?? false)) {
                    continue;
                }

                // Skip if no data for this field
                if (! array_key_exists($fieldKey, $this->coreData)) {
                    continue;
                }

                $value = $this->coreData[$fieldKey];

                // Handle repeater fields (like responsible_people) - only persist non-sensitive subfields
                if (($field['type'] ?? '') === 'repeater' && is_array($value)) {
                    $value = $this->extractPersistableRepeaterData($value, $field['schema'] ?? []);
                }

                $businessData[$fieldKey] = $value;
            }
        }

        if (! empty($businessData)) {
            $this->business->update($businessData);
        }
    }

    /**
     * Extract only persistable (non-sensitive) fields from repeater data.
     *
     * @param  array<int, array>  $items
     * @param  array<string, array>  $schema
     * @return array<int, array>
     */
    protected function extractPersistableRepeaterData(array $items, array $schema): array
    {
        return array_map(function (array $item) use ($schema): array {
            $persistable = [];
            foreach ($schema as $subKey => $subField) {
                if (($subField['persist_to_business'] ?? false) && array_key_exists($subKey, $item)) {
                    $persistable[$subKey] = $item[$subKey];
                }
            }
            // Keep the _id for tracking
            if (isset($item['_id'])) {
                $persistable['_id'] = $item['_id'];
            }

            return $persistable;
        }, $items);
    }

    protected function saveStateData(): void
    {
        $stateCode = $this->currentStateCode();
        if (! $stateCode) {
            return;
        }

        $protector = app(SensitiveDataProtector::class);
        $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
        $encrypted = $protector->encryptStateData($this->stateData, $stateDef);

        $stateRecord = $this->application->currentStateRecord();
        $stateRecord?->update([
            'data' => $encrypted,
            'current_step_key' => $this->currentStepKey,
        ]);

        $this->application->update([
            'current_phase' => $this->currentPhase,
            'current_step_key' => $this->currentStepKey,
            'current_state_index' => $this->currentStateIndex,
        ]);
    }

    public function nextStep(): void
    {
        $this->assertNotLocked();

        // Review phase has no "next" - only submit
        if ($this->currentPhase === 'review') {
            return;
        }

        $this->validateCurrentStep();

        // Save current data
        if ($this->currentPhase === 'core') {
            $this->saveCoreData();
        } else {
            $this->saveStateData();
        }

        $stepKeys = array_keys($this->getCurrentSteps());
        $currentIndex = array_search($this->currentStepKey, $stepKeys);

        if ($currentIndex !== false && $currentIndex < count($stepKeys) - 1) {
            // Move to next step in current phase
            $this->currentStepKey = $stepKeys[$currentIndex + 1];
            $this->skipEmptyStepsForward();
            // Use updateApplicationPhase since skipping could change phase/state
            $this->updateApplicationPhase();
        } else {
            // Move to next phase or state
            $this->advancePhaseOrState();
        }
    }

    public function previousStep(): void
    {
        $this->assertNotLocked();

        // Special handling for review phase
        if ($this->currentPhase === 'review') {
            $this->goBackFromReview();

            return;
        }

        // Save current data
        if ($this->currentPhase === 'core') {
            $this->saveCoreData();
        } else {
            $this->saveStateData();
        }

        $stepKeys = array_keys($this->getCurrentSteps());
        $currentIndex = array_search($this->currentStepKey, $stepKeys);

        if ($currentIndex !== false && $currentIndex > 0) {
            $this->currentStepKey = $stepKeys[$currentIndex - 1];
            $this->skipEmptyStepsBackward();
            // Use updateApplicationPhase since skipping could change phase/state
            $this->updateApplicationPhase();
        } else {
            $this->goBackToPreviousPhaseOrState();
        }
    }

    protected function goBackFromReview(): void
    {
        // Go back to last state's last step
        $this->currentPhase = 'states';
        $this->currentStateIndex = count($this->application->selected_states) - 1;

        // Get available steps (excluding state_responsible_people)
        $steps = $this->getCurrentSteps();
        $stateStepKeys = array_keys($steps);
        $this->currentStepKey = end($stateStepKeys) ?: null;

        // Load state data
        $this->loadStateDataForCurrentState();

        // Skip backward through empty steps
        $this->skipEmptyStepsBackward();

        $this->updateApplicationPhase();
    }

    protected function advancePhaseOrState(): void
    {
        $this->advancePhaseOrStateInternal();
        $this->skipEmptyStepsForward();
        $this->updateApplicationPhase();
    }

    /**
     * Internal method to advance phase/state without skipping empty steps or saving.
     */
    protected function advancePhaseOrStateInternal(): void
    {
        if ($this->currentPhase === 'core') {
            // Move to states phase
            $this->currentPhase = 'states';
            $this->currentStateIndex = 0;
            $this->loadStateDataForCurrentState();

            $steps = $this->getCurrentSteps();
            $this->currentStepKey = array_key_first($steps);
        } elseif ($this->currentPhase === 'states') {
            // Mark current state as complete
            $this->application->currentStateRecord()?->markComplete();

            if ($this->currentStateIndex < count($this->application->selected_states) - 1) {
                // Move to next state
                $this->currentStateIndex++;
                $this->loadStateDataForCurrentState();

                $steps = $this->getCurrentSteps();
                $this->currentStepKey = array_key_first($steps);
            } else {
                // Move to review phase
                $this->currentPhase = 'review';
                $this->currentStepKey = null;
            }
        }
    }

    protected function goBackToPreviousPhaseOrState(): void
    {
        $this->goBackToPreviousPhaseOrStateInternal();
        $this->skipEmptyStepsBackward();
        $this->updateApplicationPhase();
    }

    /**
     * Internal method to go back to previous phase/state without skipping empty steps or saving.
     */
    protected function goBackToPreviousPhaseOrStateInternal(): void
    {
        if ($this->currentPhase === 'states' && $this->currentStateIndex > 0) {
            // Go to previous state's last step
            $this->currentStateIndex--;
            $this->loadStateDataForCurrentState();

            $steps = $this->getCurrentSteps();
            $stepKeys = array_keys($steps);
            $this->currentStepKey = end($stepKeys) ?: null;
        } elseif ($this->currentPhase === 'states' && $this->currentStateIndex === 0) {
            // Go back to core phase's last step
            $this->currentPhase = 'core';
            $steps = $this->definition['base']['core_steps'] ?? [];
            $stepKeys = array_keys($steps);
            $this->currentStepKey = end($stepKeys) ?: null;
        }
    }

    protected function loadStateDataForCurrentState(): void
    {
        $stateCode = $this->currentStateCode();
        if (! $stateCode) {
            $this->stateData = [];

            return;
        }

        $stateRecord = $this->application->stateRecord($stateCode);
        if ($stateRecord) {
            $protector = app(SensitiveDataProtector::class);
            $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
            $this->stateData = $protector->decryptStateData($stateRecord->data ?? [], $stateDef);
        } else {
            $this->stateData = [];
        }
    }

    protected function updateApplicationStep(): void
    {
        $this->application->update([
            'current_step_key' => $this->currentStepKey,
        ]);
    }

    protected function updateApplicationPhase(): void
    {
        $this->application->update([
            'current_phase' => $this->currentPhase,
            'current_step_key' => $this->currentStepKey,
            'current_state_index' => $this->currentStateIndex,
        ]);
    }

    protected function validateCurrentStep(): void
    {
        $step = $this->getCurrentStepProperty();
        if (! $step) {
            return;
        }

        $builder = app(RulesBuilder::class);
        $validation = $builder->buildForLivewire(
            $step,
            $this->coreData,
            $this->stateData,
            $this->currentStateCode(),
            $this->currentPhase
        );

        $this->validate($validation['rules'], [], $validation['attributes']);
    }

    public function addRepeaterItem(string $fieldKey): void
    {
        $this->assertNotLocked();

        $newItem = ['_id' => Str::uuid()->toString()];

        if ($this->currentPhase === 'core') {
            $this->coreData[$fieldKey][] = $newItem;
        } else {
            $this->stateData[$fieldKey][] = $newItem;
        }
    }

    public function removeRepeaterItem(string $fieldKey, string $id): void
    {
        $this->assertNotLocked();

        if ($this->currentPhase === 'core') {
            $this->coreData[$fieldKey] = array_values(
                array_filter(
                    $this->coreData[$fieldKey] ?? [],
                    fn ($item) => ($item['_id'] ?? '') !== $id
                )
            );
        } else {
            $this->stateData[$fieldKey] = array_values(
                array_filter(
                    $this->stateData[$fieldKey] ?? [],
                    fn ($item) => ($item['_id'] ?? '') !== $id
                )
            );
        }
    }

    public function submit(): void
    {
        $this->assertNotLocked();

        // Final validation of all steps and cross-validators
        $this->validateAllSteps();

        // Run cross-field validators
        $this->runCrossFieldValidators();

        // Mark as submitted
        $this->application->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'locked_at' => now(),
        ]);

        session()->flash('success', 'Your application has been submitted successfully.');

        $this->redirect(route('dashboard'));
    }

    protected function validateAllSteps(): void
    {
        $builder = app(RulesBuilder::class);

        // Validate core steps
        foreach ($this->definition['base']['core_steps'] ?? [] as $step) {
            $validation = $builder->buildForArray(
                $step,
                $this->coreData,
                [],
                null
            );

            validator($this->coreData, $validation['rules'], [], $validation['attributes'])->validate();
        }

        // Validate each state
        foreach ($this->application->selected_states as $index => $stateCode) {
            $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
            $stateRecord = $this->application->stateRecord($stateCode);
            $stateData = $stateRecord?->data ?? [];

            foreach ($stateDef['state_steps'] ?? [] as $step) {
                $validation = $builder->buildForArray(
                    $step,
                    $this->coreData,
                    $stateData,
                    $stateCode
                );

                validator($stateData, $validation['rules'], [], $validation['attributes'])->validate();
            }
        }
    }

    protected function runCrossFieldValidators(): void
    {
        $registry = app(CrossFieldValidatorRegistry::class);

        // Run core cross-validators
        foreach ($this->definition['base']['core_steps'] ?? [] as $step) {
            foreach ($step['cross_validations'] ?? [] as $validation) {
                if (($validation['phase'] ?? 'core') === 'core') {
                    $registry->validateWithPrefix(
                        $validation['rule'],
                        $this->coreData,
                        $validation['field'],
                        $validation,
                        'coreData'
                    );
                }
            }
        }
    }

    protected function assertNotLocked(): void
    {
        if ($this->application->isLocked()) {
            throw new \RuntimeException('Application is locked and cannot be modified.');
        }
    }

    public function render(): View
    {
        $currentStep = $this->getCurrentStepProperty();
        $stepKeys = array_keys($this->getCurrentSteps());

        return view('livewire.forms.multi-state-form-runner', [
            'currentStep' => $currentStep,
            'visibleFields' => $this->getVisibleFieldsProperty(),
            'stepKeys' => $stepKeys,
            'isCore' => $this->currentPhase === 'core',
            'isStates' => $this->currentPhase === 'states',
            'isReview' => $this->currentPhase === 'review',
            'canGoNext' => $this->currentPhase !== 'review',
            'currentStateName' => config('states.'.$this->currentStateCode()),
            'stateProgress' => [
                'current' => $this->currentStateIndex + 1,
                'total' => count($this->application->selected_states),
            ],
            'allStatesComplete' => $this->application->allStatesComplete(),
            'states' => config('states'),
            'statePersonFields' => $this->getStatePersonFieldsProperty(),
        ])->layout('layouts.app', ['title' => 'Application']);
    }
}
