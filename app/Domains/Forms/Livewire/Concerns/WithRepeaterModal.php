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
    }
}
