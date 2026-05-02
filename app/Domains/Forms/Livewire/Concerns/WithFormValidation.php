<?php

namespace App\Domains\Forms\Livewire\Concerns;

use App\Domains\Forms\Engine\RulesBuilder;
use App\Domains\Forms\Engine\Validation\CrossFieldValidatorRegistry;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * All validation paths used by the runner: per-step, conditional-min on
 * repeaters, all-steps (final-submit), and cross-field validators.
 *
 * Throws ValidationException; the orchestrators on the component
 * (`nextStep`, `submit`) catch it and dispatch UI events.
 *
 * Depends on (component-owned):
 *   - $coreData, $stateData, $currentPhase, $definition, $application
 *   - getCurrentStepProperty() (from WithStepNavigation)
 *   - currentStateCode()
 */
trait WithFormValidation
{
    protected function validateCurrentStep(): void
    {
        $step = $this->getCurrentStepProperty();
        if (! $step) {
            return;
        }

        $builder = app(RulesBuilder::class);
        $validation = $builder->buildForLivewire(
            $step,
            $this->coreData,
            $this->stateData,
            $this->currentStateCode(),
            $this->currentPhase
        );

        $this->validate($validation['rules'], [], $validation['attributes']);

        $data = $this->currentPhase === 'core' ? $this->coreData : $this->stateData;
        $prefix = $this->currentPhase === 'core' ? 'coreData' : 'stateData';
        $this->validateConditionalMins($step, $data, $prefix);

        $registry = app(CrossFieldValidatorRegistry::class);
        foreach ($step['cross_validations'] ?? [] as $crossValidation) {
            $phase = $crossValidation['phase'] ?? 'core';
            if ($phase === $this->currentPhase || $phase === 'core') {
                $registry->validateWithPrefix(
                    $crossValidation['rule'],
                    $data,
                    $crossValidation['field'],
                    $crossValidation,
                    $prefix
                );
            }
        }
    }

    /**
     * Validate conditional_min constraints on repeater fields.
     *
     * @param  array<string, mixed>  $step
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    protected function validateConditionalMins(array $step, array $data, string $prefix): void
    {
        foreach ($step['fields'] ?? [] as $fieldKey => $field) {
            $conditionalMin = $field['conditional_min'] ?? null;
            if (! $conditionalMin) {
                continue;
            }

            $condField = $conditionalMin['field'];
            $condValues = $conditionalMin['values'] ?? [];
            $currentValue = data_get($data, $condField);

            if ($currentValue === null || ! isset($condValues[$currentValue])) {
                continue;
            }

            $requiredMin = $condValues[$currentValue];
            $items = data_get($data, $fieldKey, []);
            $count = is_array($items) ? count($items) : 0;

            if ($count < $requiredMin) {
                $entityLabel = ucfirst(str_replace('_', ' ', $currentValue));
                $itemLabel = $field['item_label'] ?? $field['label'] ?? $fieldKey;
                $pluralLabel = strtolower(Str::plural($itemLabel, $requiredMin));

                throw ValidationException::withMessages([
                    "{$prefix}.{$fieldKey}" => ["{$entityLabel}s require at least {$requiredMin} {$pluralLabel}."],
                ]);
            }
        }
    }

    protected function validateAllSteps(): void
    {
        $builder = app(RulesBuilder::class);

        // Validate core steps
        foreach ($this->definition['base']['core_steps'] ?? [] as $step) {
            $validation = $builder->buildForArray(
                $step,
                $this->coreData,
                [],
                null
            );

            validator($this->coreData, $validation['rules'], [], $validation['attributes'])->validate();
        }

        // Validate each state
        foreach ($this->application->selected_states as $stateCode) {
            $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
            $stateRecord = $this->application->stateRecord($stateCode);
            $stateData = $stateRecord?->data ?? [];

            foreach ($stateDef['state_steps'] ?? [] as $step) {
                $validation = $builder->buildForArray(
                    $step,
                    $this->coreData,
                    $stateData,
                    $stateCode
                );

                validator($stateData, $validation['rules'], [], $validation['attributes'])->validate();
            }
        }
    }

    protected function runCrossFieldValidators(): void
    {
        $registry = app(CrossFieldValidatorRegistry::class);

        // Run core cross-validators
        foreach ($this->definition['base']['core_steps'] ?? [] as $step) {
            foreach ($step['cross_validations'] ?? [] as $validation) {
                if (($validation['phase'] ?? 'core') === 'core') {
                    $registry->validateWithPrefix(
                        $validation['rule'],
                        $this->coreData,
                        $validation['field'],
                        $validation,
                        'coreData'
                    );
                }
            }

            $this->validateConditionalMins($step, $this->coreData, 'coreData');
        }
    }
}
