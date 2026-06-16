<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

beforeEach(fn () => View::share('errors', new ViewErrorBag));

/**
 * Modals are explicit save points: clicking Save inside the
 * responsible_people modal must persist the row immediately, not at the
 * next step navigation. These tests pin that contract so a regression
 * doesn't silently revert to the in-memory-only behavior that lost rows
 * on hard refresh.
 *
 * The remove flow gets the same coverage — once add/edit auto-persist,
 * leaving remove draft-until-Next would be a confusing inconsistency.
 *
 * @return array<string, mixed>
 */
function validResponsiblePerson(array $overrides = []): array
{
    return array_merge([
        '_id' => 'person-1',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'title' => 'Owner',
        'phone' => '(555) 555-1212',
        'email' => 'jane@example.com',
        'dob' => '1985-04-12',
        'ssn' => '123-45-6789',
        'driver_license_state' => 'CA',
        'driver_license_number' => 'D1234567',
        // 5 years out so the after:today rule passes regardless of when
        // the test runs.
        'driver_license_expiration' => now()->addYears(5)->toDateString(),
        'home_address' => [
            'line1' => '1 Test St',
            'line2' => '',
            'city' => 'Testville',
            'state' => 'CA',
            'zip' => '90000',
        ],
        'ownership_percent' => 100,
        'is_authorized_signer' => true,
    ], $overrides);
}

describe('Repeater modal persistence', function () {
    it('persists a new person to the database when the modal is saved', function () {
        $application = RunnerTestFactory::make()
            ->onStep('responsible_people')
            ->coreData(['responsible_people' => []])
            ->boot();

        $person = validResponsiblePerson(['_id' => 'person-new']);

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('openRepeaterModal', 'responsible_people')
            ->set('repeaterForm', $person)
            ->call('saveRepeaterItem')
            ->assertHasNoErrors();

        $stored = $application->fresh()->core_data['responsible_people'] ?? [];

        expect($stored)->toHaveCount(1)
            ->and($stored[0]['first_name'] ?? null)->toBe('Jane')
            ->and($stored[0]['last_name'] ?? null)->toBe('Doe')
            ->and($stored[0]['_id'] ?? null)->toBe('person-new');
    });

    it('persists an edited person to the database when the modal is saved', function () {
        $existing = validResponsiblePerson(['_id' => 'person-edit', 'first_name' => 'Jane']);

        $application = RunnerTestFactory::make()
            ->onStep('responsible_people')
            ->coreData(['responsible_people' => [$existing]])
            ->boot();

        $edited = array_merge($existing, ['first_name' => 'Janet']);

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('openRepeaterModal', 'responsible_people', 0)
            ->set('repeaterForm', $edited)
            ->call('saveRepeaterItem')
            ->assertHasNoErrors();

        $stored = $application->fresh()->core_data['responsible_people'] ?? [];

        expect($stored)->toHaveCount(1)
            ->and($stored[0]['first_name'] ?? null)->toBe('Janet')
            ->and($stored[0]['_id'] ?? null)->toBe('person-edit');
    });

    it('persists a removal to the database immediately', function () {
        $existing = validResponsiblePerson(['_id' => 'person-remove']);

        $application = RunnerTestFactory::make()
            ->onStep('responsible_people')
            ->coreData(['responsible_people' => [$existing]])
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('removeRepeaterItem', 'responsible_people', 'person-remove');

        $stored = $application->fresh()->core_data['responsible_people'] ?? [];

        expect($stored)->toBeArray()->toBeEmpty();
    });

    it('no-ops cleanly when saveRepeaterItem fires after the phase advanced', function () {
        // Race we saw in production: Livewire batched `nextStep` and
        // `saveRepeaterItem` in one request. nextStep advanced the
        // phase to state_details, then saveRepeaterItem ran against a
        // step that has no `responsible_people` field, building empty
        // rules and tripping Livewire's MissingRulesException. The
        // guard in saveRepeaterItem should now close the stale modal
        // and return without throwing.
        $application = RunnerTestFactory::make()
            ->onStep('responsible_people')
            ->coreData(['responsible_people' => [validResponsiblePerson()]])
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            // Force the stale-modal scenario directly: editingRepeater-
            // Field still points at responsible_people, but the runner
            // is now on a step that doesn't define it.
            ->set('currentPhase', 'states')
            ->set('currentStepKey', 'state_details')
            ->set('showRepeaterModal', true)
            ->set('editingRepeaterField', 'responsible_people')
            ->set('repeaterForm', ['_id' => 'stale-id', 'first_name' => 'Stale'])
            ->call('saveRepeaterItem')
            ->assertHasNoErrors()
            ->assertSet('showRepeaterModal', false);
    });
});
