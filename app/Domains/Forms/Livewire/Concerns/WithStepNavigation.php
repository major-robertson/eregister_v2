<?php

namespace App\Domains\Forms\Livewire\Concerns;

use App\Domains\Forms\Engine\VisibleFieldResolver;

/**
 * Step-query and cursor helpers for the form runner.
 *
 * Pure read/skip operations on the step list — no validation, no
 * persistence, no phase mutation beyond what `skipEmpty*` needs to do
 * indirectly through the runner's `advance*` / `goBack*` orchestrators
 * (which still live on the component as the conductor).
 *
 * Depends on (component-owned):
 *   - $definition, $currentPhase, $currentStepKey, $currentStateIndex
 *   - currentStateCode(), buildContext()
 *   - advancePhaseOrStateInternal(), goBackToPreviousPhaseOrStateInternal()
 */
trait WithStepNavigation
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function getCurrentSteps(): array
    {
        if ($this->currentPhase === 'core') {
            return $this->definition['base']['core_steps'] ?? [];
        }

        if ($this->currentPhase === 'states') {
            $stateCode = $this->currentStateCode();
            $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
            $steps = $stateDef['state_steps'] ?? [];

            // Skip state_responsible_people step - those fields are now in the core responsible_people repeater
            unset($steps['state_responsible_people']);

            return $steps;
        }

        // Review phase - return empty steps
        return [];
    }

    public function getCurrentStepProperty(): ?array
    {
        if ($this->currentPhase === 'review') {
            return null;
        }

        $steps = $this->getCurrentSteps();

        return $steps[$this->currentStepKey] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getVisibleFieldsProperty(): array
    {
        $step = $this->getCurrentStepProperty();
        if (! $step) {
            return [];
        }

        $resolver = app(VisibleFieldResolver::class);

        return $resolver->resolve($step, $this->buildContext());
    }

    protected function getFirstStepKey(): ?string
    {
        $steps = $this->getCurrentSteps();

        return array_key_first($steps) ?? null;
    }

    /**
     * Keys of the steps that will actually render for this application,
     * given current answers and selected states. Used by the progress
     * counters so "(3/16)" never advertises steps the user will skip.
     *
     * @param  array<string, array<string, mixed>>  $steps
     * @return list<string>
     */
    public function visibleStepKeys(array $steps): array
    {
        return array_keys(array_filter(
            $steps,
            fn (array $step) => $this->stepHasVisibleFields($step)
        ));
    }

    /**
     * @param  array<string, mixed>  $step
     */
    protected function stepHasVisibleFields(array $step): bool
    {
        $resolver = app(VisibleFieldResolver::class);
        $visibleFields = $resolver->resolve($step, $this->buildContext());

        return count($visibleFields) > 0;
    }

    protected function currentStepHasVisibleFields(): bool
    {
        $step = $this->getCurrentStepProperty();
        if (! $step) {
            return false;
        }

        return $this->stepHasVisibleFields($step);
    }

    /**
     * Skip forward through empty steps until we find one with fields or reach the end.
     */
    protected function skipEmptyStepsForward(): void
    {
        $maxIterations = 50; // Prevent infinite loops
        $iterations = 0;

        while ($iterations < $maxIterations) {
            $iterations++;

            // If we're in review phase, we're done
            if ($this->currentPhase === 'review') {
                return;
            }

            // If current step has visible fields, we're done
            if ($this->currentStepHasVisibleFields()) {
                return;
            }

            // Otherwise, advance to next step/phase
            $stepKeys = array_keys($this->getCurrentSteps());
            $currentIndex = array_search($this->currentStepKey, $stepKeys);

            if ($currentIndex !== false && $currentIndex < count($stepKeys) - 1) {
                // Move to next step in current phase
                $this->currentStepKey = $stepKeys[$currentIndex + 1];
            } else {
                // Move to next phase or state
                $this->advancePhaseOrStateInternal();
            }
        }
    }

    /**
     * Skip backward through empty steps until we find one with fields or reach the beginning.
     */
    protected function skipEmptyStepsBackward(): void
    {
        $maxIterations = 50; // Prevent infinite loops
        $iterations = 0;

        while ($iterations < $maxIterations) {
            $iterations++;

            // If current step has visible fields, we're done
            if ($this->currentStepHasVisibleFields()) {
                return;
            }

            // Otherwise, go back to previous step/phase
            $stepKeys = array_keys($this->getCurrentSteps());
            $currentIndex = array_search($this->currentStepKey, $stepKeys);

            if ($currentIndex !== false && $currentIndex > 0) {
                // Move to previous step in current phase
                $this->currentStepKey = $stepKeys[$currentIndex - 1];
            } else {
                // Move to previous phase or state
                $this->goBackToPreviousPhaseOrStateInternal();

                // If we couldn't go back any further (still at core phase, first step), stop
                if ($this->currentPhase === 'core') {
                    $coreSteps = array_keys($this->definition['base']['core_steps'] ?? []);
                    if ($this->currentStepKey === ($coreSteps[0] ?? null)) {
                        return;
                    }
                }
            }
        }
    }
}
