<?php

namespace App\Domains\Forms\Livewire\Concerns;

/**
 * Phase / step progress computation for the wizard's top progress bar.
 *
 * Each phase fills proportionally as the user advances through its own
 * steps so the bar reflects real progress (Core Info has multiple sub-
 * steps; the State Details phase has multiple steps per selected state).
 * The current step contributes a half-step worth of fill so the bar
 * always reflects "you're partway through this step" rather than sitting
 * at the prior step's boundary until you click Next.
 *
 * Depends on (component-owned):
 *   - $definition, $application, $currentPhase, $currentStepKey, $currentStateIndex
 */
trait WithPhaseProgress
{
    /**
     * @return array{
     *     core: array{fill: float, current: int, total: int},
     *     states: array{fill: float, current: int, total: int},
     *     review: array{fill: float, current: int, total: int}
     * }
     */
    public function getPhaseProgressProperty(): array
    {
        // Count only steps that will actually render for this application
        // (current answers + selected states). A static count both
        // overstates the journey and makes the number jump when the
        // runner auto-skips an inapplicable step.
        $coreSteps = $this->definition['base']['core_steps'] ?? [];
        $coreKeys = $this->visibleStepKeys($coreSteps);
        if ($this->currentPhase === 'core' && ! in_array($this->currentStepKey, $coreKeys, true) && isset($coreSteps[$this->currentStepKey])) {
            // The cursor can sit on a step whose fields just became
            // conditionally hidden; keep it countable until skip logic runs.
            $coreKeys = array_keys($coreSteps);
        }
        $coreTotal = count($coreKeys);

        $stateStepsPerState = [];
        foreach ($this->application->selected_states as $i => $stateCode) {
            $stateDef = $this->definition['states'][$stateCode] ?? $this->definition['base'];
            $stateSteps = $stateDef['state_steps'] ?? [];
            unset($stateSteps['state_responsible_people']);

            // Visibility can only be resolved against the loaded state's
            // data, so filter the current state and use raw counts for
            // the rest (their share of the fill bar is approximate anyway).
            if ($this->currentPhase === 'states' && $i === $this->currentStateIndex) {
                $keys = $this->visibleStepKeys($stateSteps);
                if (! in_array($this->currentStepKey, $keys, true) && isset($stateSteps[$this->currentStepKey])) {
                    $keys = array_keys($stateSteps);
                }
                $stateStepsPerState[$stateCode] = $keys;
            } else {
                $stateStepsPerState[$stateCode] = array_keys($stateSteps);
            }
        }
        $statesTotal = array_sum(array_map('count', $stateStepsPerState));

        $coreCurrent = 0;
        $statesCurrent = 0;
        $coreFill = 0.0;
        $statesFill = 0.0;
        $reviewFill = 0.0;

        if ($this->currentPhase === 'core') {
            $idx = array_search($this->currentStepKey, $coreKeys, true);
            $idx = $idx === false ? 0 : $idx;
            $coreCurrent = $idx + 1;
            $coreFill = $coreTotal > 0 ? (($idx + 0.5) / $coreTotal) * 100 : 0.0;
        } elseif ($this->currentPhase === 'states') {
            $coreFill = 100.0;
            $coreCurrent = $coreTotal;

            $completed = 0;
            foreach ($this->application->selected_states as $i => $stateCode) {
                $stepKeys = $stateStepsPerState[$stateCode] ?? [];
                if ($i < $this->currentStateIndex) {
                    $completed += count($stepKeys);
                } elseif ($i === $this->currentStateIndex) {
                    $idx = array_search($this->currentStepKey, $stepKeys, true);
                    $idx = $idx === false ? 0 : $idx;
                    $completed += $idx;
                    $statesCurrent = $completed + 1;
                    $statesFill = $statesTotal > 0
                        ? (($completed + 0.5) / $statesTotal) * 100
                        : 0.0;
                }
            }
        } elseif ($this->currentPhase === 'review') {
            $coreFill = 100.0;
            $statesFill = 100.0;
            $reviewFill = 100.0;
            $coreCurrent = $coreTotal;
            $statesCurrent = $statesTotal;
        }

        return [
            'core' => [
                'fill' => round($coreFill, 2),
                'current' => $coreCurrent,
                'total' => $coreTotal,
            ],
            'states' => [
                'fill' => round($statesFill, 2),
                'current' => $statesCurrent,
                'total' => $statesTotal,
            ],
            'review' => [
                'fill' => $reviewFill,
                'current' => $this->currentPhase === 'review' ? 1 : 0,
                'total' => 1,
            ],
        ];
    }
}
