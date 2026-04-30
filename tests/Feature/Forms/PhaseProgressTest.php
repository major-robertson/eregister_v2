<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

/**
 * Regression coverage for the wizard's top progress bar.
 *
 * The bar previously toggled each segment fully on/off based on phase, so
 * Core Info read as "100% complete" while the user was on its very first
 * sub-step. The phaseProgress computation must now reflect step-within-
 * phase progress for every multi-step phase.
 */
function makeRunnerApplication(string $currentPhase, ?string $stepKey, int $stateIndex = 0): array
{
    $user = User::factory()->create();

    $business = Business::create([
        'name' => 'Progress Test',
        'legal_name' => 'Progress Test LLC',
        'entity_type' => 'corporation',
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA', 'NJ'],
        'status' => 'draft',
        'current_phase' => $currentPhase,
        'current_step_key' => $stepKey,
        'current_state_index' => $stateIndex,
        'core_data' => ['legal_name' => 'Progress Test LLC', 'entity_type' => 'corporation'],
        'created_by_user_id' => $user->id,
        'paid_at' => now(),
    ]);

    foreach (['CA', 'NJ'] as $code) {
        FormApplicationState::create([
            'form_application_id' => $application->id,
            'state_code' => $code,
            'status' => 'pending',
            'data' => [],
        ]);
    }

    test()->actingAs($user)->withSession(['current_business_id' => $business->id]);

    return [$application, $user];
}

describe('MultiStateFormRunner phase progress', function () {
    it('starts the core segment partially filled on the first core step', function () {
        [$application] = makeRunnerApplication('core', 'identity');

        $progress = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->get('phaseProgress');

        // First of 4 core steps with the "+0.5" step weighting => 12.5%.
        // We assert the qualitative invariants rather than the exact float
        // so the test doesn't break if a core step is added or removed.
        expect($progress['core']['fill'])->toBeGreaterThan(0.0)
            ->and($progress['core']['fill'])->toBeLessThan(50.0)
            ->and($progress['core']['current'])->toBe(1)
            ->and($progress['core']['total'])->toBeGreaterThanOrEqual(2)
            ->and($progress['states']['fill'])->toBe(0.0)
            ->and($progress['review']['fill'])->toBe(0.0);
    });

    it('shows the core segment more filled on a later core step', function () {
        [$application] = makeRunnerApplication('core', 'identity');

        $first = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->get('phaseProgress');

        $application->update(['current_step_key' => 'responsible_people']);

        $later = Livewire::test(MultiStateFormRunner::class, ['application' => $application->fresh()])
            ->get('phaseProgress');

        expect($later['core']['fill'])->toBeGreaterThan($first['core']['fill'])
            ->and($later['core']['current'])->toBeGreaterThan($first['core']['current']);
    });

    it('completes the core segment and partially fills states once in the states phase', function () {
        [$application] = makeRunnerApplication('states', 'state_details', stateIndex: 0);

        $progress = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->get('phaseProgress');

        expect($progress['core']['fill'])->toBe(100.0)
            ->and($progress['states']['fill'])->toBeGreaterThan(0.0)
            ->and($progress['states']['fill'])->toBeLessThan(100.0)
            ->and($progress['review']['fill'])->toBe(0.0);
    });

    it('fills states further as the user moves to the second selected state', function () {
        [$application] = makeRunnerApplication('states', 'state_details', stateIndex: 0);

        $first = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->get('phaseProgress');

        $application->update(['current_state_index' => 1]);

        $second = Livewire::test(MultiStateFormRunner::class, ['application' => $application->fresh()])
            ->get('phaseProgress');

        expect($second['states']['fill'])->toBeGreaterThan($first['states']['fill']);
    });

    it('fills every segment at the review phase', function () {
        [$application] = makeRunnerApplication('review', null, stateIndex: 1);

        $progress = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->get('phaseProgress');

        expect($progress['core']['fill'])->toBe(100.0)
            ->and($progress['states']['fill'])->toBe(100.0)
            ->and($progress['review']['fill'])->toBe(100.0);
    });
});
