<?php

namespace App\Domains\Forms\Engine;

class RulesBuilder
{
    public function __construct(
        private VisibleFieldResolver $resolver
    ) {}

    /**
     * Build rules with Livewire prefixes (e.g., coreData.legal_name, stateData.field)
     *
     * @param  array<int, string>  $selectedStates
     * @return array{rules: array, messages: array, attributes: array}
     */
    public function buildForLivewire(
        array $step,
        array $coreData,
        array $stateData,
        ?string $stateCode,
        string $phase,
        array $selectedStates = []
    ): array {
        $prefix = $phase === 'core' ? 'coreData' : 'stateData';
        $context = $this->buildContext($coreData, $stateData, $stateCode, $selectedStates);
        $stateName = $stateCode ? config("states.{$stateCode}", $stateCode) : '';

        $visibleFields = $this->resolver->resolve($step, $context);

        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($visibleFields as $fieldKey => $field) {
            $type = $field['type'] ?? 'text';

            if ($type === 'repeater') {
                $this->buildRepeaterRulesLivewire($rules, $attributes, $field, $fieldKey, $coreData, $stateData, $prefix, $context, $stateName);
            } elseif ($type === 'address') {
                $this->buildAddressRulesLivewire($rules, $attributes, $field, $fieldKey, $prefix, $stateName);
            } elseif ($type === 'person_state_extra') {
                $this->buildPersonExtrasRulesLivewire($rules, $attributes, $field, $coreData, $stateCode);
            } elseif ($type === 'matrix') {
                $this->buildMatrixRules($rules, $attributes, $field, $fieldKey, "{$prefix}.", $selectedStates);
            } elseif ($type === 'anywhere_states') {
                $data = $prefix === 'coreData' ? $coreData : $stateData;
                $this->buildAnywhereStatesRules($rules, $attributes, $field, $fieldKey, "{$prefix}.", $data, $selectedStates);
            } else {
                $fieldRules = $this->rewritePrefixTokens($field['rules'] ?? [], "{$prefix}.");
                if (! empty($fieldRules)) {
                    $rules["{$prefix}.{$fieldKey}"] = $fieldRules;
                    $attributes["{$prefix}.{$fieldKey}"] = $this->replaceStateName($field['label'] ?? $fieldKey, $stateName);
                }
            }
        }

        return ['rules' => $rules, 'messages' => $messages, 'attributes' => $attributes];
    }

    /**
     * Replace {state_name} placeholder with actual state name.
     */
    private function replaceStateName(string $label, string $stateName): string
    {
        return str_replace('{state_name}', $stateName, $label);
    }

    /**
     * Rewrite the `{prefix}` token used in cross-field validators
     * (e.g. `required_unless:{prefix}entity_type,sole_prop`) so the same
     * field definition can target the correct path in both the prefixed
     * Livewire context (`coreData.entity_type`) and the unprefixed
     * final-submit context (`entity_type`).
     *
     * Pass `''` for unprefixed contexts and `"{$prefix}."` for Livewire
     * contexts. Non-string rule entries (e.g. Rule objects, closures)
     * pass through untouched.
     *
     * @param  array<int, mixed>  $rules
     * @return array<int, mixed>
     */
    private function rewritePrefixTokens(array $rules, string $replacement): array
    {
        return array_map(
            fn ($rule) => is_string($rule) ? str_replace('{prefix}', $replacement, $rule) : $rule,
            $rules
        );
    }

    /**
     * Build rules for array validation (unprefixed for Validator::make)
     *
     * @param  array<int, string>  $selectedStates
     * @return array{rules: array, messages: array, attributes: array}
     */
    public function buildForArray(
        array $step,
        array $coreData,
        array $stateData,
        ?string $stateCode,
        array $selectedStates = []
    ): array {
        $context = $this->buildContext($coreData, $stateData, $stateCode, $selectedStates);
        $stateName = $stateCode ? config("states.{$stateCode}", $stateCode) : '';
        $visibleFields = $this->resolver->resolve($step, $context);

        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($visibleFields as $fieldKey => $field) {
            $type = $field['type'] ?? 'text';

            if ($type === 'repeater') {
                $this->buildRepeaterRulesArray($rules, $attributes, $field, $fieldKey, $coreData, $context, $stateName);
            } elseif ($type === 'address') {
                $this->buildAddressRulesArray($rules, $attributes, $field, $fieldKey, $stateName);
            } elseif ($type === 'person_state_extra') {
                // Handled separately per state
            } elseif ($type === 'matrix') {
                $this->buildMatrixRules($rules, $attributes, $field, $fieldKey, '', $selectedStates);
            } elseif ($type === 'anywhere_states') {
                $this->buildAnywhereStatesRules($rules, $attributes, $field, $fieldKey, '', $coreData, $selectedStates);
            } else {
                $fieldRules = $this->rewritePrefixTokens($field['rules'] ?? [], '');
                if (! empty($fieldRules)) {
                    $rules[$fieldKey] = $fieldRules;
                    $attributes[$fieldKey] = $this->replaceStateName($field['label'] ?? $fieldKey, $stateName);
                }
            }
        }

        return ['rules' => $rules, 'messages' => $messages, 'attributes' => $attributes];
    }

    /**
     * @param  array<int, string>  $selectedStates
     */
    private function buildContext(array $coreData, array $stateData, ?string $stateCode, array $selectedStates = []): array
    {
        return [
            'coreData' => $coreData,
            'stateData' => $stateData,
            'stateCode' => $stateCode,
            'rowData' => [],
            'selectedStates' => $selectedStates,
        ];
    }

    /**
     * Matrix fields validate one cell per applicable state using the
     * field's `cell_rules`. The per-cell attribute substitutes that
     * row's state name into `{state_name}` so validation messages read
     * "Number of Florida employees", not the current wizard state.
     *
     * @param  array<int, string>  $selectedStates
     */
    private function buildMatrixRules(
        array &$rules,
        array &$attributes,
        array $field,
        string $fieldKey,
        string $prefixDot,
        array $selectedStates
    ): void {
        $cellRules = $field['cell_rules'] ?? [];
        if (empty($cellRules)) {
            return;
        }

        foreach (Applicability::statesFor($field, $selectedStates) as $rowState) {
            $rowStateName = config("states.{$rowState}", $rowState);
            $rules["{$prefixDot}{$fieldKey}.{$rowState}"] = $cellRules;
            $attributes["{$prefixDot}{$fieldKey}.{$rowState}"] = $this->replaceStateName(
                $field['label'] ?? $fieldKey,
                $rowStateName
            );
        }
    }

    /**
     * anywhere_states fields validate the yes/no plus, when "yes" with
     * multiple applicable states, a non-empty checklist restricted to
     * the applicable set. Single-applicable-state fields skip the
     * checklist requirement — save-time normalization auto-fills it.
     *
     * @param  array<int, string>  $selectedStates
     */
    private function buildAnywhereStatesRules(
        array &$rules,
        array &$attributes,
        array $field,
        string $fieldKey,
        string $prefixDot,
        array $data,
        array $selectedStates
    ): void {
        $applicable = Applicability::statesFor($field, $selectedStates);
        $label = $field['label'] ?? $fieldKey;

        $rules["{$prefixDot}{$fieldKey}.anywhere"] = ['required', 'in:0,1'];
        $attributes["{$prefixDot}{$fieldKey}.anywhere"] = $label;

        $anywhere = (string) data_get($data, "{$fieldKey}.anywhere", '');

        if ($anywhere === '1' && count($applicable) > 1) {
            $rules["{$prefixDot}{$fieldKey}.states"] = ['required', 'array', 'min:1'];
            $rules["{$prefixDot}{$fieldKey}.states.*"] = ['in:'.implode(',', $applicable)];
            $attributes["{$prefixDot}{$fieldKey}.states"] = "States for: {$label}";
        }
    }

    private function buildRepeaterRulesLivewire(
        array &$rules,
        array &$attributes,
        array $field,
        string $fieldKey,
        array $coreData,
        array $stateData,
        string $prefix,
        array $context,
        string $stateName = ''
    ): void {
        $fieldRules = $field['rules'] ?? [];
        if (! empty($fieldRules)) {
            $rules["{$prefix}.{$fieldKey}"] = $fieldRules;
        }

        $items = data_get($prefix === 'coreData' ? $coreData : $stateData, $fieldKey, []);

        foreach ($items as $index => $item) {
            $rowContext = array_merge($context, ['rowData' => $item]);

            foreach ($field['schema'] ?? [] as $subKey => $subField) {
                $subRules = $subField['rules'] ?? [];
                if (! empty($subRules)) {
                    $rules["{$prefix}.{$fieldKey}.{$index}.{$subKey}"] = $subRules;
                    $attributes["{$prefix}.{$fieldKey}.{$index}.{$subKey}"] = $this->replaceStateName($subField['label'] ?? $subKey, $stateName);
                }
            }
        }
    }

    private function buildRepeaterRulesArray(
        array &$rules,
        array &$attributes,
        array $field,
        string $fieldKey,
        array $data,
        array $context,
        string $stateName = ''
    ): void {
        $fieldRules = $field['rules'] ?? [];
        if (! empty($fieldRules)) {
            $rules[$fieldKey] = $fieldRules;
        }

        // Use wildcard rules for array validation
        foreach ($field['schema'] ?? [] as $subKey => $subField) {
            $subRules = $subField['rules'] ?? [];
            if (! empty($subRules)) {
                $rules["{$fieldKey}.*.{$subKey}"] = $subRules;
                $attributes["{$fieldKey}.*.{$subKey}"] = $this->replaceStateName($subField['label'] ?? $subKey, $stateName);
            }
        }
    }

    private function buildAddressRulesLivewire(
        array &$rules,
        array &$attributes,
        array $field,
        string $fieldKey,
        string $prefix,
        string $stateName = ''
    ): void {
        $basePath = "{$prefix}.{$fieldKey}";

        foreach ($this->addressSubfieldRules($field) as $subKey => $subField) {
            $rules["{$basePath}.{$subKey}"] = $subField['rules'];
            $attributes["{$basePath}.{$subKey}"] = $this->replaceStateName($subField['label'], $stateName);
        }
    }

    private function buildAddressRulesArray(
        array &$rules,
        array &$attributes,
        array $field,
        string $fieldKey,
        string $stateName = ''
    ): void {
        foreach ($this->addressSubfieldRules($field) as $subKey => $subField) {
            $rules["{$fieldKey}.{$subKey}"] = $subField['rules'];
            $attributes["{$fieldKey}.{$subKey}"] = $this->replaceStateName($subField['label'], $stateName);
        }
    }

    /**
     * Build address sub-field rules that respect the parent field's
     * disposition. When the parent address has `nullable` in its rules,
     * the inner line1/city/state/zip become nullable too — so an
     * optional address (e.g. tx_landlord_address) can be left blank
     * without tripping required-on-line1 etc. Format constraints
     * (digits:5 zip, size:2 state, max:N) still apply when the user
     * actually enters something, because Laravel skips other rules
     * when the value is null AND `nullable` is present.
     *
     * @return array<string, array{label: string, rules: array<int, string>}>
     */
    private function addressSubfieldRules(array $field): array
    {
        $parentRules = $field['rules'] ?? [];
        $parentIsNullable = in_array('nullable', $parentRules, true)
            && ! in_array('required', $parentRules, true);
        $requiredOrNullable = $parentIsNullable ? 'nullable' : 'required';

        return [
            'line1' => ['label' => 'Street Address', 'rules' => [$requiredOrNullable, 'string', 'max:100']],
            'line2' => ['label' => 'Address Line 2', 'rules' => ['nullable', 'string', 'max:100']],
            'city' => ['label' => 'City', 'rules' => [$requiredOrNullable, 'string', 'max:50']],
            'state' => ['label' => 'State', 'rules' => [$requiredOrNullable, 'string', 'size:2']],
            'zip' => ['label' => 'ZIP Code', 'rules' => [$requiredOrNullable, 'digits:5']],
        ];
    }

    private function buildPersonExtrasRulesLivewire(
        array &$rules,
        array &$attributes,
        array $field,
        array $coreData,
        ?string $stateCode
    ): void {
        $people = $coreData['responsible_people'] ?? [];

        foreach ($people as $person) {
            $personId = $person['_id'] ?? null;
            if (! $personId) {
                continue;
            }

            $personLabel = trim(($person['first_name'] ?? '').' '.($person['last_name'] ?? ''));
            $personLabel = $personLabel !== '' ? $personLabel : 'Person';

            foreach ($field['schema'] ?? [] as $subKey => $subField) {
                $subRules = $subField['rules'] ?? [];
                if (! empty($subRules)) {
                    $path = "stateData.responsible_people_extra.{$personId}.{$subKey}";
                    $rules[$path] = $subRules;
                    $attributes[$path] = $personLabel.' - '.($subField['label'] ?? $subKey);
                }
            }
        }
    }
}
