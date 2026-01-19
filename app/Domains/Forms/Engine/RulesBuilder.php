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
     * @return array{rules: array, messages: array, attributes: array}
     */
    public function buildForLivewire(
        array $step,
        array $coreData,
        array $stateData,
        ?string $stateCode,
        string $phase
    ): array {
        $prefix = $phase === 'core' ? 'coreData' : 'stateData';
        $context = $this->buildContext($coreData, $stateData, $stateCode);
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
            } else {
                $fieldRules = $field['rules'] ?? [];
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
     * Build rules for array validation (unprefixed for Validator::make)
     *
     * @return array{rules: array, messages: array, attributes: array}
     */
    public function buildForArray(
        array $step,
        array $coreData,
        array $stateData,
        ?string $stateCode
    ): array {
        $context = $this->buildContext($coreData, $stateData, $stateCode);
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
            } else {
                $fieldRules = $field['rules'] ?? [];
                if (! empty($fieldRules)) {
                    $rules[$fieldKey] = $fieldRules;
                    $attributes[$fieldKey] = $this->replaceStateName($field['label'] ?? $fieldKey, $stateName);
                }
            }
        }

        return ['rules' => $rules, 'messages' => $messages, 'attributes' => $attributes];
    }

    private function buildContext(array $coreData, array $stateData, ?string $stateCode): array
    {
        return [
            'coreData' => $coreData,
            'stateData' => $stateData,
            'stateCode' => $stateCode,
            'rowData' => [],
            'selectedStates' => [],
        ];
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
        $fieldRules = $field['rules'] ?? [];
        $basePath = "{$prefix}.{$fieldKey}";

        // Address is a nested object with line1, line2, city, state, zip
        $addressFields = [
            'line1' => ['label' => 'Street Address', 'rules' => ['required', 'string', 'max:100']],
            'line2' => ['label' => 'Address Line 2', 'rules' => ['nullable', 'string', 'max:100']],
            'city' => ['label' => 'City', 'rules' => ['required', 'string', 'max:50']],
            'state' => ['label' => 'State', 'rules' => ['required', 'string', 'size:2']],
            'zip' => ['label' => 'ZIP Code', 'rules' => ['required', 'string', 'max:10']],
        ];

        foreach ($addressFields as $subKey => $subField) {
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
        $addressFields = [
            'line1' => ['label' => 'Street Address', 'rules' => ['required', 'string', 'max:100']],
            'line2' => ['label' => 'Address Line 2', 'rules' => ['nullable', 'string', 'max:100']],
            'city' => ['label' => 'City', 'rules' => ['required', 'string', 'max:50']],
            'state' => ['label' => 'State', 'rules' => ['required', 'string', 'size:2']],
            'zip' => ['label' => 'ZIP Code', 'rules' => ['required', 'string', 'max:10']],
        ];

        foreach ($addressFields as $subKey => $subField) {
            $rules["{$fieldKey}.{$subKey}"] = $subField['rules'];
            $attributes["{$fieldKey}.{$subKey}"] = $this->replaceStateName($subField['label'], $stateName);
        }
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

            foreach ($field['schema'] ?? [] as $subKey => $subField) {
                $subRules = $subField['rules'] ?? [];
                if (! empty($subRules)) {
                    $path = "stateData.responsible_people_extra.{$personId}.{$subKey}";
                    $rules[$path] = $subRules;
                    $attributes[$path] = ($person['full_name'] ?? 'Person').' - '.($subField['label'] ?? $subKey);
                }
            }
        }
    }
}
