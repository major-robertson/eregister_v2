<?php

namespace App\Domains\Forms\Engine;

class VisibleFieldResolver
{
    public function __construct(
        private ConditionEvaluator $evaluator
    ) {}

    /**
     * Resolve visible fields for a step
     *
     * @return array<string, array> Fields that should be displayed
     */
    public function resolve(array $step, array $context): array
    {
        $visibleFields = [];

        foreach ($step['fields'] ?? [] as $fieldKey => $field) {
            if ($this->isFieldVisible($field, $context)) {
                $visibleFields[$fieldKey] = $field;
            }
        }

        return $visibleFields;
    }

    /**
     * Resolve visible schema fields within a repeater for a specific row
     *
     * @return array<string, array> Schema fields that should be displayed
     */
    public function resolveRepeaterSchema(array $schema, array $rowData, array $context): array
    {
        $visibleFields = [];
        $rowContext = array_merge($context, ['rowData' => $rowData]);

        foreach ($schema as $fieldKey => $field) {
            if ($this->isFieldVisible($field, $rowContext)) {
                $visibleFields[$fieldKey] = $field;
            }
        }

        return $visibleFields;
    }

    private function isFieldVisible(array $field, array $context): bool
    {
        if (! isset($field['when'])) {
            return true;
        }

        return $this->evaluator->evaluate($field['when'], $context);
    }
}
