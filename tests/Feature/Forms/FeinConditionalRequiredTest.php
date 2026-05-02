<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * EIN/FEIN required-ness pivots on entity_type:
 *   - Sole proprietors may leave it blank (but it's still encouraged
 *     when they have one, because the engine persists it to the
 *     business profile).
 *   - Every other entity type must provide a valid EIN before
 *     advancing past the tax_identification step.
 *
 * EIN now lives in its own `tax_identification` step, after identity
 * and activity. The factory below seeds every prior-step field, then
 * lets each test override entity_type / fein to isolate the
 * tax_identification step's validation.
 */
function feinRunner(string $entityType, ?string $fein)
{
    $core = ['entity_type' => $entityType];
    if ($fein !== null) {
        $core['fein'] = $fein;
    }

    return RunnerTestFactory::make()
        ->coreData($core)
        ->boot();
}

describe('FEIN conditional required behavior', function () {
    it('renders the EIN field on the tax_identification step for sole proprietors', function () {
        $application = feinRunner('sole_prop', null);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application]);

        $visibleFields = $component->instance()->getVisibleFieldsProperty();

        expect($visibleFields)->toHaveKey('fein');
    });

    it('lets sole proprietors leave EIN blank without a validation error', function () {
        $application = feinRunner('sole_prop', null);

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.fein');
    });

    it('accepts a valid EIN from a sole proprietor when they choose to provide one', function () {
        $application = feinRunner('sole_prop', '12-3456789');

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.fein');
    });

    it('rejects a malformed EIN even when the sole-prop opt-out is in play', function () {
        $application = feinRunner('sole_prop', 'nope');

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasErrors(['coreData.fein']);
    });

    it('requires an EIN from corporations', function () {
        // Override the factory's default fein so the corporation case
        // explicitly leaves it blank.
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'corporation', 'fein' => null])
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasErrors(['coreData.fein']);
    });

    it('accepts a valid EIN from corporations', function () {
        $application = feinRunner('corporation', '12-3456789');

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.fein');
    });
});
