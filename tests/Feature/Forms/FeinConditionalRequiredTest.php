<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * EIN/FEIN is optional for every entity type since EREG-54 (2026-09):
 * 42% of applicants quit on the identity step, most within three
 * minutes, and a new business often has no EIN yet. When one is given
 * it must still be well-formed, and it is still persisted to the
 * business profile.
 *
 * EIN lives in the identity step's Tax Identification group. The
 * factory below seeds every identity field, then lets each test
 * override entity_type / fein to isolate the EIN validation.
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
    it('renders the EIN field on the identity step for sole proprietors', function () {
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

    it('lets corporations and LLCs leave the EIN blank too', function (string $entityType) {
        // Override the factory's default fein so the case explicitly
        // leaves it blank.
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => $entityType, 'fein' => null])
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.fein');
    })->with(['corporation', 'llc_single', 'llc_multi']);

    it('rejects a malformed EIN from a corporation', function () {
        $application = feinRunner('corporation', '12-34');

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
