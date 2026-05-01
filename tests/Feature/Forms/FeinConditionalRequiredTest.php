<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Livewire\Livewire;

/**
 * EIN/FEIN required-ness pivots on entity_type:
 *   - Sole proprietors may leave it blank (but it's still encouraged
 *     when they have one, because the engine persists it to the
 *     business profile).
 *   - Every other entity type must provide a valid EIN before
 *     advancing past the tax_identification step.
 *
 * EIN now lives in its own `tax_identification` step, after identity
 * and activity. The runner only validates the visible fields of the
 * current step, so the bootstrap below seeds prior-step data and
 * positions the runner directly on tax_identification.
 */
function bootTaxIdentificationRunner(string $entityType, ?string $fein): array
{
    $user = User::factory()->create();
    $business = Business::create([
        'name' => 'EIN Test',
        'legal_name' => 'EIN Test LLC',
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    // Pre-fill every prior-step field so step navigation and any
    // cross-step lookups succeed; the test only exercises the
    // tax_identification step's validation.
    $coreData = [
        'legal_name' => 'EIN Test LLC',
        'entity_type' => $entityType,
        'formation_state' => 'CA',
        'naics_code' => '541512',
        'business_description' => 'Software development services',
        'reason_for_applying' => 'new_business',
        'business_start_date' => '2020-01-01',
    ];
    if ($fein !== null) {
        $coreData['fein'] = $fein;
    }
    if ($entityType === 'sole_prop') {
        // tax_identification step requires SSN for sole props; populate
        // it so we isolate the test to FEIN-only validation behavior.
        $coreData['individual_ssn'] = '123-45-6789';
    }

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 1,
        'selected_states' => ['CA'],
        'status' => 'draft',
        'current_phase' => 'core',
        'current_step_key' => 'tax_identification',
        'core_data' => $coreData,
        'created_by_user_id' => $user->id,
        'paid_at' => now(),
    ]);

    FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'CA',
        'status' => 'pending',
        'data' => [],
    ]);

    test()->actingAs($user)->withSession(['current_business_id' => $business->id]);

    return [$application, $user];
}

describe('FEIN conditional required behavior', function () {
    it('renders the EIN field on the tax_identification step for sole proprietors', function () {
        [$application] = bootTaxIdentificationRunner('sole_prop', null);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application]);

        $visibleFields = $component->instance()->getVisibleFieldsProperty();

        expect($visibleFields)->toHaveKey('fein');
    });

    it('lets sole proprietors leave EIN blank without a validation error', function () {
        [$application] = bootTaxIdentificationRunner('sole_prop', null);

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.fein');
    });

    it('accepts a valid EIN from a sole proprietor when they choose to provide one', function () {
        [$application] = bootTaxIdentificationRunner('sole_prop', '12-3456789');

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.fein');
    });

    it('rejects a malformed EIN even when the sole-prop opt-out is in play', function () {
        [$application] = bootTaxIdentificationRunner('sole_prop', 'nope');

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasErrors(['coreData.fein']);
    });

    it('requires an EIN from corporations', function () {
        [$application] = bootTaxIdentificationRunner('corporation', null);

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasErrors(['coreData.fein']);
    });

    it('accepts a valid EIN from corporations', function () {
        [$application] = bootTaxIdentificationRunner('corporation', '12-3456789');

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.fein');
    });
});
