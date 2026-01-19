<?php

namespace App\Domains\Forms\Engine;

class ConditionEvaluator
{
    /**
     * Evaluate a condition - STATELESS, all context passed as parameters
     *
     * Context should contain:
     * - coreData: array
     * - stateData: array
     * - rowData: array (for repeater context)
     * - stateCode: ?string
     * - stateIndex: ?int
     * - selectedStates: array
     */
    public function evaluate(array $condition, array $context): bool
    {
        if (empty($condition)) {
            return true;
        }

        $operator = array_key_first($condition);
        $operands = $condition[$operator];

        return match ($operator) {
            '==' => $this->resolve($operands[0], $context) == $this->resolve($operands[1], $context),
            '===' => $this->resolve($operands[0], $context) === $this->resolve($operands[1], $context),
            '!=' => $this->resolve($operands[0], $context) != $this->resolve($operands[1], $context),
            '!==' => $this->resolve($operands[0], $context) !== $this->resolve($operands[1], $context),
            '>' => $this->resolve($operands[0], $context) > $this->resolve($operands[1], $context),
            '>=' => $this->resolve($operands[0], $context) >= $this->resolve($operands[1], $context),
            '<' => $this->resolve($operands[0], $context) < $this->resolve($operands[1], $context),
            '<=' => $this->resolve($operands[0], $context) <= $this->resolve($operands[1], $context),
            'in' => in_array(
                $this->resolve($operands[0], $context),
                (array) $this->resolve($operands[1], $context)
            ),
            'contains' => in_array(
                $this->resolve($operands[1], $context),
                (array) $this->resolve($operands[0], $context)
            ),
            'and' => collect($operands)->every(fn ($op) => $this->evaluate($op, $context)),
            'or' => collect($operands)->contains(fn ($op) => $this->evaluate($op, $context)),
            'not' => ! $this->evaluate($operands[0], $context),
            default => true,
        };
    }

    private function resolve(mixed $operand, array $context): mixed
    {
        if (! is_array($operand) || ! isset($operand['var'])) {
            return $operand;
        }

        $path = $operand['var'];
        $coreData = $context['coreData'] ?? [];
        $stateData = $context['stateData'] ?? [];
        $rowData = $context['rowData'] ?? [];
        $stateCode = $context['stateCode'] ?? null;
        $stateIndex = $context['stateIndex'] ?? null;
        $selectedStates = $context['selectedStates'] ?? [];

        // CRITICAL: Check specific paths BEFORE prefix matching

        if ($path === '$state.code') {
            return $stateCode;
        }

        if ($path === '$state.index') {
            return $stateIndex;
        }

        if ($path === '$root.selected_states') {
            return $selectedStates;
        }

        if (str_starts_with($path, '$row.')) {
            return data_get($rowData, substr($path, 5));
        }

        if (str_starts_with($path, '$state.')) {
            return data_get($stateData, substr($path, 7));
        }

        if (str_starts_with($path, '$root.')) {
            return data_get($coreData, substr($path, 6));
        }

        // Unprefixed: check row first, then state, then core
        return data_get($rowData, $path)
            ?? data_get($stateData, $path)
            ?? data_get($coreData, $path);
    }
}
