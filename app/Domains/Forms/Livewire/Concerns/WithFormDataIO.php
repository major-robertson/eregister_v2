<?php

namespace App\Domains\Forms\Livewire\Concerns;

use App\Domains\Forms\Engine\Applicability;
use App\Domains\Forms\Engine\SensitiveDataProtector;
use Illuminate\Support\Str;

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
        $this->coreData = $this->normalizeAnywhereStatesFields($this->coreData);
        $this->coreData = $this->syncPrincipalLocation($this->coreData);

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
     * Normalize every anywhere_states field to canonical shape before
     * persisting:
     *
     *   - anywhere == '0' (or unanswered)        → states = []
     *   - anywhere == '1' + 1 applicable state   → states = [thatState]
     *   - always                                 → drop codes outside
     *                                              applicable ∩ selected
     *
     * Keeps stored data canonical regardless of UI path (e.g. stale
     * checkboxes left behind by toggling yes → no → yes).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizeAnywhereStatesFields(array $data): array
    {
        $selectedStates = $this->application->selected_states ?? [];

        foreach ($this->definition['base']['core_steps'] ?? [] as $step) {
            foreach ($step['fields'] ?? [] as $fieldKey => $field) {
                if (($field['type'] ?? '') !== 'anywhere_states') {
                    continue;
                }

                $value = $data[$fieldKey] ?? null;
                if (! is_array($value)) {
                    continue;
                }

                $applicable = Applicability::statesFor($field, $selectedStates);
                $anywhere = (string) ($value['anywhere'] ?? '');
                $states = array_values(array_intersect((array) ($value['states'] ?? []), $applicable));

                if ($anywhere !== '1') {
                    $states = [];
                } elseif (count($applicable) === 1) {
                    $states = $applicable;
                }

                $data[$fieldKey] = [
                    'anywhere' => $anywhere === '' ? null : $anywhere,
                    'states' => $states,
                ];
            }
        }

        return $data;
    }

    /**
     * Keep the principal locations[] row mirrored to the Principal
     * Business Address:
     *
     *   - No principal row yet + business address filled → auto-create
     *     row #1 flagged is_principal with the business address.
     *   - Principal row exists → overwrite its address with the current
     *     business address (the modal renders it read-only, so the
     *     business address is the single source of truth).
     *
     * This makes the locations_principal_unique_and_matches_business_
     * address cross-validator unfailable through normal UI flows — it
     * remains only as a guard against tampered payloads.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function syncPrincipalLocation(array $data): array
    {
        // Only applies when the definition actually has a locations repeater.
        $hasLocationsField = collect($this->definition['base']['core_steps'] ?? [])
            ->contains(fn ($step) => ($step['fields']['locations']['type'] ?? null) === 'repeater');

        if (! $hasLocationsField) {
            return $data;
        }

        $businessAddress = $data['business_address'] ?? null;
        if (! is_array($businessAddress) || trim((string) ($businessAddress['line1'] ?? '')) === '') {
            return $data;
        }

        $locations = is_array($data['locations'] ?? null) ? $data['locations'] : [];

        $principalIndex = null;
        foreach ($locations as $index => $row) {
            if (! empty($row['is_principal'])) {
                $principalIndex = $index;
                break;
            }
        }

        if ($principalIndex === null) {
            array_unshift($locations, [
                '_id' => Str::uuid()->toString(),
                'is_principal' => true,
                'address' => $businessAddress,
            ]);
        } else {
            $locations[$principalIndex]['address'] = $businessAddress;
        }

        $data['locations'] = array_values($locations);

        return $data;
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
                    $value = array_values(array_filter(
                        $this->extractPersistableRepeaterData($value, $field['schema'] ?? []),
                        fn (array $row) => ! $this->isEffectivelyEmpty(collect($row)->except('_id')->all())
                    ));
                }

                // Never overwrite profile data with blanks. Empty composites
                // (e.g. a mailing_address of all-empty strings left behind by
                // an untouched toggle) would otherwise poison the profile and
                // get prefilled into every future application as "random"
                // junk data.
                if ($this->isEffectivelyEmpty($value)) {
                    continue;
                }

                $businessData[$fieldKey] = $value;
            }
        }

        if (! empty($businessData)) {
            $this->business->update($businessData);
        }
    }

    /**
     * True when a value carries no real content: null, blank strings, or
     * arrays (nested to any depth) whose leaves are all blank. '0' and
     * false-y but meaningful answers like "0" are NOT considered empty.
     */
    protected function isEffectivelyEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $leaf) {
                if (! $this->isEffectivelyEmpty($leaf)) {
                    return false;
                }
            }

            return true;
        }

        if (is_bool($value)) {
            return $value === false;
        }

        return $value === null || trim((string) $value) === '';
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
