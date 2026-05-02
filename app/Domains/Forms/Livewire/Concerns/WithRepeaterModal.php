<?php

namespace App\Domains\Forms\Livewire\Concerns;

use Illuminate\Support\Str;

/**
 * Repeater modal state and actions.
 *
 * Owns the modal-specific properties so the component class doesn't have
 * to. The Livewire actions stay public so wire:click bindings still
 * resolve them.
 *
 * Depends on (component-owned):
 *   - $coreData, $stateData, $currentPhase
 *   - getCurrentStepProperty() (from WithStepNavigation)
 *   - saveCoreData(), saveStateData() (from WithFormDataIO)
 *   - assertNotLocked()
 */
trait WithRepeaterModal
{
    public bool $showRepeaterModal = false;

    public ?int $editingRepeaterIndex = null;

    public string $editingRepeaterField = '';

    /** @var array<string, mixed> */
    public array $repeaterForm = [];

    public function openRepeaterModal(string $fieldKey, ?int $index = null): void
    {
        $this->assertNotLocked();
        $this->editingRepeaterField = $fieldKey;
        $this->editingRepeaterIndex = $index;

        if ($index !== null) {
            $items = $this->currentPhase === 'core'
                ? ($this->coreData[$fieldKey] ?? [])
                : ($this->stateData[$fieldKey] ?? []);
            $this->repeaterForm = $items[$index] ?? [];
        } else {
            $this->repeaterForm = ['_id' => Str::uuid()->toString()];
        }

        $this->showRepeaterModal = true;
    }

    public function closeRepeaterModal(): void
    {
        $this->showRepeaterModal = false;
        $this->editingRepeaterIndex = null;
        $this->editingRepeaterField = '';
        $this->repeaterForm = [];
        $this->resetValidation();
    }

    public function saveRepeaterItem(): void
    {
        $this->assertNotLocked();

        $fieldKey = $this->editingRepeaterField;
        $step = $this->getCurrentStepProperty();
        $schema = $step['fields'][$fieldKey]['schema'] ?? [];

        // Bail out cleanly when the modal context is stale. This
        // happens when Livewire batches `nextStep` and
        // `saveRepeaterItem` into the same request: nextStep advances
        // the phase first, and saveRepeaterItem then runs against a
        // step that doesn't define this repeater. Without this guard
        // $rules ends up empty and Livewire's validate() throws
        // MissingRulesException (it treats `[]` as "use my defaults"
        // and refuses to validate against a truly empty rule set),
        // surfacing as a 500 to the user and silently dropping the
        // half-filled row that was sitting in $repeaterForm.
        if ($fieldKey === '' || empty($schema)) {
            $this->closeRepeaterModal();

            return;
        }

        $rules = [];
        $attributes = [];
        foreach ($schema as $subKey => $subField) {
            $subRules = $subField['rules'] ?? [];
            if (! empty($subRules)) {
                $rules["repeaterForm.{$subKey}"] = $subRules;
                $attributes["repeaterForm.{$subKey}"] = $subField['label'] ?? $subKey;
            }
        }

        $this->validate($rules, [], $attributes);

        if ($this->editingRepeaterIndex !== null) {
            if ($this->currentPhase === 'core') {
                $this->coreData[$fieldKey][$this->editingRepeaterIndex] = $this->repeaterForm;
            } else {
                $this->stateData[$fieldKey][$this->editingRepeaterIndex] = $this->repeaterForm;
            }
        } else {
            if ($this->currentPhase === 'core') {
                $this->coreData[$fieldKey][] = $this->repeaterForm;
            } else {
                $this->stateData[$fieldKey][] = $this->repeaterForm;
            }
        }

        // Persist immediately so a hard refresh between modal save and
        // the next step navigation doesn't lose the just-added or
        // just-edited row. Modals are explicit save points; treating
        // them as draft-until-Next confuses users (they clicked Save).
        $this->persistCurrentPhaseData();

        $this->closeRepeaterModal();
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

        // Same persistence rationale as saveRepeaterItem: clicking the
        // trash icon is an explicit destructive intent, and the user
        // will be surprised if a refresh resurrects a removed row.
        $this->persistCurrentPhaseData();
    }

    /**
     * Route the current in-memory state to the right IO method based on
     * which phase the modal was opened from. Both methods live in
     * WithFormDataIO and are guaranteed to be available because both
     * traits are mixed into the same component.
     */
    private function persistCurrentPhaseData(): void
    {
        if ($this->currentPhase === 'core') {
            $this->saveCoreData();
        } else {
            $this->saveStateData();
        }
    }
}
