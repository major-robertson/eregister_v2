<?php

namespace App\Domains\ResaleCert\Services;

use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleStateRule;

/**
 * The core multi-state optimization: given the states a user wants to cover,
 * compute the fewest forms that do it. Preference order SST > MTC >
 * individual state forms; states already covered by a uniform form are also
 * offered as optional individual extras.
 */
class MinimumFormsService
{
    /**
     * @param  list<string>  $selectedStates
     * @return array{minimum: list<array<string, mixed>>, optional: list<array<string, mixed>>}
     */
    public function calculateMinimumForms(array $selectedStates, ?ResaleProfile $profile): array
    {
        $minimum = [];
        $optional = [];
        $coveredStates = [];

        $rules = ResaleStateRule::query()->get()->keyBy('state_code');

        $sstStates = $rules->where('accepts_sst', true)->keys()->all();
        $mtcStates = $rules->where('accepts_mtc', true)->keys()->all();

        $sstCoveredStates = array_values(array_intersect($selectedStates, $sstStates));

        if ($sstCoveredStates !== []) {
            $minimum[] = [
                'type' => 'uniform',
                'state_code' => 'SST',
                'name' => 'SST Uniform Certificate',
                'covers_states' => $sstCoveredStates,
                'is_required' => true,
            ];
            $coveredStates = [...$coveredStates, ...$sstCoveredStates];
        }

        $remainingStates = array_values(array_diff($selectedStates, $coveredStates));

        $mtcCoveredStates = [];

        if ($profile?->mtc_enabled) {
            $mtcCoveredStates = array_values(array_intersect($remainingStates, $mtcStates));

            if ($mtcCoveredStates !== []) {
                $minimum[] = [
                    'type' => 'uniform',
                    'state_code' => 'MTC',
                    'name' => 'MTC Uniform Certificate',
                    'covers_states' => $mtcCoveredStates,
                    'is_required' => true,
                ];
                $coveredStates = [...$coveredStates, ...$mtcCoveredStates];
            }
        }

        $remainingStates = array_diff($selectedStates, $coveredStates);

        foreach ($remainingStates as $stateCode) {
            if ($rule = $rules->get($stateCode)) {
                $minimum[] = [
                    'type' => 'individual',
                    'state_code' => $stateCode,
                    'name' => $rule->state_name,
                    'covers_states' => [$stateCode],
                    'is_required' => true,
                ];
            }
        }

        // States covered by a uniform form can still be issued their own
        // individual state form for vendors that insist on it.
        foreach ($selectedStates as $stateCode) {
            if (in_array($stateCode, $remainingStates, true)) {
                continue;
            }

            if ($rule = $rules->get($stateCode)) {
                $optional[] = [
                    'type' => 'individual',
                    'state_code' => $stateCode,
                    'name' => $rule->state_name,
                    'covers_states' => [$stateCode],
                    'is_required' => false,
                    'covered_by' => in_array($stateCode, $sstCoveredStates, true) ? 'SST'
                        : (in_array($stateCode, $mtcCoveredStates, true) ? 'MTC' : null),
                ];
            }
        }

        return ['minimum' => $minimum, 'optional' => $optional];
    }
}
