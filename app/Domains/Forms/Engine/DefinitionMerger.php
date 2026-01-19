<?php

namespace App\Domains\Forms\Engine;

class DefinitionMerger
{
    /**
     * Merge base definition with state-specific overrides
     */
    public function merge(array $base, array $override): array
    {
        $result = $base;

        foreach (['core_steps', 'state_steps'] as $stepType) {
            foreach ($override[$stepType] ?? [] as $stepKey => $stepOverride) {
                if (! isset($result[$stepType][$stepKey])) {
                    $result[$stepType][$stepKey] = $stepOverride;

                    continue;
                }

                if (isset($stepOverride['fields'])) {
                    $result[$stepType][$stepKey]['fields'] = $this->mergeFields(
                        $result[$stepType][$stepKey]['fields'] ?? [],
                        $stepOverride['fields']
                    );
                }

                // Merge other step properties (title, description, cross_validations)
                foreach (['title', 'description', 'cross_validations'] as $prop) {
                    if (isset($stepOverride[$prop])) {
                        $result[$stepType][$stepKey][$prop] = $stepOverride[$prop];
                    }
                }
            }
        }

        return $result;
    }

    private function mergeFields(array $base, array $override): array
    {
        // Handle 'append' - add fields at end
        if (isset($override['append'])) {
            foreach ($override['append'] as $key => $field) {
                $base[$key] = $field;
            }
            unset($override['append']);
        }

        // Handle 'prepend' - add fields at beginning
        if (isset($override['prepend'])) {
            $base = array_merge($override['prepend'], $base);
            unset($override['prepend']);
        }

        // Handle 'remove' - remove fields by key
        if (isset($override['remove'])) {
            foreach ((array) $override['remove'] as $key) {
                unset($base[$key]);
            }
            unset($override['remove']);
        }

        // Handle 'replace' - completely replace fields
        if (isset($override['replace'])) {
            foreach ($override['replace'] as $key => $field) {
                $base[$key] = $field;
            }
            unset($override['replace']);
        }

        // Remaining keys: deep merge (for schema overrides, etc.)
        foreach ($override as $fieldKey => $fieldDef) {
            if (! isset($base[$fieldKey])) {
                $base[$fieldKey] = $fieldDef;

                continue;
            }

            // Deep merge schema if present
            if (isset($fieldDef['schema'])) {
                $base[$fieldKey]['schema'] = $this->mergeFields(
                    $base[$fieldKey]['schema'] ?? [],
                    $fieldDef['schema']
                );
                unset($fieldDef['schema']);
            }

            // Merge remaining field properties
            $base[$fieldKey] = array_merge($base[$fieldKey], $fieldDef);
        }

        return $base;
    }
}
