<?php

namespace App\Domains\Forms\Livewire;

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Engine\SensitiveDataProtector;
use App\Domains\Forms\Livewire\Concerns\WithFormDataIO;
use App\Domains\Forms\Livewire\Concerns\WithFormPrefill;
use App\Domains\Forms\Livewire\Concerns\WithFormValidation;
use App\Domains\Forms\Livewire\Concerns\WithPhaseProgress;
use App\Domains\Forms\Livewire\Concerns\WithRepeaterModal;
use App\Domains\Forms\Livewire\Concerns\WithStepNavigation;
use App\Domains\Forms\Models\FormApplication;
use App\Support\Workspaces\WorkspaceRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Conducts a multi-step, multi-state form (currently used by Sales Tax
 * Permit and the LLC Formation flow). The bulk of the per-concern logic
 * lives in the six `Concerns/With*` traits below; this class is the
 * orchestrator that binds them together — it owns the seven core
 * properties, the lifecycle hooks (boot, mount, render), and the public
 * navigation actions (nextStep, previousStep, submit) that mix
 * validation, persistence, and cursor mutation into a single user-facing
 * step.
 */
class MultiStateFormRunner extends Component
{
    use WithFormDataIO;
    use WithFormPrefill;
    use WithFormValidation;
    use WithPhaseProgress;
    use WithRepeaterModal;
    use WithStepNavigation;

    public Business $business;

    public FormApplication $application;

    /** @var array<string, mixed> */
    public array $coreData = [];

    /** @var array<string, mixed> */
    public array $stateData = [];

    public string $currentPhase = 'core';

    public ?string $currentStepKey = null;

    public int $currentStateIndex = 0;

    /** @var array{base: array<string, mixed>, states: array<string, array<string, mixed>>} */
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

        // Workspace alignment is enforced by the application.access
        // middleware (EnsureHasAccess), which fires before this mount
        // and 404s when the request URL belongs to a workspace that
        // doesn't claim the application's form_type. Livewire::test()
        // bypasses the middleware by design, so no in-mount guard here.

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
        // Always load fresh from FormRegistry rather than reading
        // $application->definition_snapshot. The snapshot is stored in a
        // MySQL JSON column, which normalizes object keys (sort by length,
        // then lexicographically) and destroys the step ordering authored in
        // base.php. The snapshot is still written at application creation
        // for historical/audit purposes — we just don't use it to drive the
        // form runtime. If you ever need to switch back to snapshot-driven
        // behavior, migrate definition_snapshot from JSON to LONGTEXT first
        // so insertion order is preserved.
        $registry = app(FormRegistry::class);
        $this->definition = [
            'base' => $registry->getBase($this->application->form_type),
            'states' => [],
        ];

        foreach ($this->application->selected_states as $stateCode) {
            $this->definition['states'][$stateCode] = $registry->get($this->application->form_type, $stateCode);
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

        // Set current step key. If the stored key no longer exists in the
        // current step set (e.g. step was renamed/split in a definition update
        // and the application is still on the old key), fall back to the first
        // step in the current phase rather than letting skipEmptyStepsForward
        // bail out into the next phase entirely.
        $this->currentStepKey = $this->application->current_step_key ?? $this->getFirstStepKey();
        $availableStepKeys = array_keys($this->getCurrentSteps());
        if ($this->currentStepKey !== null && ! in_array($this->currentStepKey, $availableStepKeys, true)) {
            $this->currentStepKey = $this->getFirstStepKey();
        }

        // Skip forward past any empty steps on initial load
        if ($this->currentPhase !== 'review') {
            $this->skipEmptyStepsForward();
            $this->updateApplicationPhase();
        }
    }

    public function currentStateCode(): ?string
    {
        return $this->application->selected_states[$this->currentStateIndex] ?? null;
    }

    /**
     * Jump back to a specific core step (e.g. the locked principal
     * address in the locations modal links back to Contact & Address).
     * Saves in-flight data without validating — same contract as
     * previousStep().
     */
    public function jumpToCoreStep(string $stepKey): void
    {
        $this->assertNotLocked();

        if (! array_key_exists($stepKey, $this->definition['base']['core_steps'] ?? [])) {
            return;
        }

        $this->closeRepeaterModal();

        if ($this->currentPhase === 'core') {
            $this->saveCoreData();
        } else {
            $this->saveStateData();
        }

        $this->currentPhase = 'core';
        $this->currentStepKey = $stepKey;
        $this->updateApplicationPhase();
    }

    /**
     * "Same for all states" shortcut on matrix fields: copy the first
     * applicable row's value into every other applicable row.
     */
    public function applyMatrixValueToAllStates(string $fieldKey): void
    {
        $this->assertNotLocked();

        $field = null;
        foreach ($this->definition['base']['core_steps'] ?? [] as $step) {
            if (isset($step['fields'][$fieldKey])) {
                $field = $step['fields'][$fieldKey];
                break;
            }
        }

        if (! $field || ($field['type'] ?? '') !== 'matrix') {
            return;
        }

        $states = \App\Domains\Forms\Engine\Applicability::statesFor(
            $field,
            $this->application->selected_states ?? []
        );

        $first = $states[0] ?? null;
        if ($first === null) {
            return;
        }

        $value = $this->coreData[$fieldKey][$first] ?? null;
        foreach ($states as $stateCode) {
            $this->coreData[$fieldKey][$stateCode] = $value;
        }
    }

    /**
     * Get all state-specific person fields from selected states.
     * Used to display state-specific fields inline in the responsible_people repeater.
     *
     * @return array<string, array{name: string, fields: array<string, mixed>}>
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

    /**
     * @return array<string, mixed>
     */
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

    public function nextStep(): void
    {
        $this->assertNotLocked();

        // Review phase has no "next" - only submit
        if ($this->currentPhase === 'review') {
            return;
        }

        try {
            $this->validateCurrentStep();
        } catch (ValidationException $ve) {
            // Tell the form to scroll to the first invalid field; long
            // steps otherwise hide errors hundreds of pixels above where
            // the user clicked Next, making the click look like a no-op.
            $this->dispatch('validation-failed');

            throw $ve;
        }

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
            // Mark current state as complete. Resolve through the
            // component's own cursor — the model's current_state_index
            // is only persisted after the skip loop finishes, so during
            // skip-through of empty states it lags behind and would mark
            // the first state repeatedly while leaving the rest
            // incomplete (blocking the review screen's payment button).
            $currentCode = $this->currentStateCode();
            if ($currentCode) {
                $this->application->stateRecord($currentCode)?->markComplete();
            }

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

    public function submit(): void
    {
        $this->assertNotLocked();

        // Final validation of all steps and cross-validators
        $this->validateAllSteps();

        // Run cross-field validators
        $this->runCrossFieldValidators();

        if ($this->application->isPaid()) {
            $this->application->update([
                'status' => 'submitted',
                'submitted_at' => now(),
                'locked_at' => now(),
            ]);

            session()->flash('success', 'Your application has been submitted successfully.');

            $this->redirect(route('dashboard'));
        } else {
            $this->redirect(route('portal.checkout', $this->application));
        }
    }

    protected function assertNotLocked(): void
    {
        if ($this->application->isLocked()) {
            throw new \RuntimeException('Application is locked and cannot be modified.');
        }
    }

    /**
     * Whether any selected state still has its own state-step questions.
     * With the clean answer shape, many selections (e.g. AL + CO + ID)
     * are fully covered by the shared core answers — the wizard then
     * runs Core → Review with no states phase to show.
     */
    public function selectedStatesHaveQuestions(): bool
    {
        return collect($this->application->selected_states ?? [])
            ->contains(function (string $stateCode) {
                $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];

                return collect($stateDef['state_steps'] ?? [])
                    ->except('state_responsible_people')
                    ->contains(fn ($step) => ! empty($step['fields']));
            });
    }

    /**
     * Position within the current state's substeps for the progress bar,
     * e.g. "California (2/3)". Mirrors how the Core segment counts steps.
     * Only substeps that will actually render are counted, so the number
     * doesn't jump when inapplicable substeps (e.g. fuel) are skipped.
     *
     * @return array{current: int, total: int}
     */
    protected function stateSubstepProgress(): array
    {
        if ($this->currentPhase !== 'states') {
            return ['current' => 0, 'total' => 0];
        }

        $steps = $this->getCurrentSteps();
        $stepKeys = $this->visibleStepKeys($steps);
        if (! in_array($this->currentStepKey, $stepKeys, true) && isset($steps[$this->currentStepKey])) {
            // Cursor sits on a substep whose fields just became hidden —
            // fall back to the unfiltered list until skip logic moves on.
            $stepKeys = array_keys($steps);
        }

        $index = array_search($this->currentStepKey, $stepKeys, true);

        return [
            'current' => $index === false ? 1 : $index + 1,
            'total' => max(count($stepKeys), 1),
        ];
    }

    public function render(): View
    {
        $currentStep = $this->getCurrentStepProperty();
        $stepKeys = array_keys($this->getCurrentSteps());

        $workspace = app(WorkspaceRegistry::class)->findByFormType($this->application->form_type);

        $layout = $workspace ? 'layouts.workspace' : 'layouts.app';
        $layoutData = ['title' => 'Application'];
        if ($workspace) {
            $layoutData['key'] = $workspace->key;
        }

        return view('livewire.forms.multi-state-form-runner', [
            'currentStep' => $currentStep,
            'visibleFields' => $this->getVisibleFieldsProperty(),
            'stepKeys' => $stepKeys,
            'isCore' => $this->currentPhase === 'core',
            'isStates' => $this->currentPhase === 'states',
            'isReview' => $this->currentPhase === 'review',
            'canGoNext' => $this->currentPhase !== 'review',
            'currentStateName' => config('states.'.$this->currentStateCode()),
            'stateProgress' => $this->stateSubstepProgress(),
            'phaseProgress' => $this->getPhaseProgressProperty(),
            'hasStateQuestions' => $this->selectedStatesHaveQuestions(),
            'allStatesComplete' => $this->application->allStatesComplete(),
            'states' => config('states'),
            'statePersonFields' => $this->getStatePersonFieldsProperty(),
            // URL to return to when the user clicks Previous on the very first
            // step of the wizard. Falls back to the dashboard if the workspace
            // doesn't expose a start route (shouldn't happen in practice).
            'startUrl' => $workspace?->startRouteFor($this->application->form_type) ?? route('dashboard'),
        ])->layout($layout, $layoutData);
    }
}
