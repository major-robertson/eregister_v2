<?php

namespace App\Domains\Forms\Livewire\Concerns;

use App\Domains\Forms\Engine\SensitiveDataProtector;

/**
 * Save / load / persist plumbing for the runner.
 *
 * Handles the encrypt-on-save / decrypt-on-load round-trip via
 * SensitiveDataProtector and the `persist_to_business` field flag that
 * mirrors selected core fields back to the Business model so a returning
 * user doesn't have to retype them on a future application.
 *
 * Depends on (component-owned):
 *   - $business, $application, $coreData, $stateData
 *   - $currentPhase, $currentStepKey, $currentStateIndex, $definition
 *   - currentStateCode()
 */
trait WithFormDataIO
{
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
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, array<string, mixed>>  $schema
     * @return array<int, array<string, mixed>>
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
}
