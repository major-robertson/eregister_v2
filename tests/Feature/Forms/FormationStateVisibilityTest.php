<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * Sole proprietors aren't "formed" in a state, so the
 * State of Formation / Registration question is hidden for them
 * (and must not block the business_identity step's validation).
 */
describe('formation_state visibility by entity type', function () {
    it('hides formation_state before an entity type is chosen', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => null, 'formation_state' => null])
            ->onStep('identity')
            ->boot();

        $visibleFields = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->instance()
            ->getVisibleFieldsProperty();

        expect($visibleFields)->not->toHaveKey('formation_state');
    });

    it('hides formation_state for sole proprietors', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'sole_prop', 'formation_state' => null])
            ->onStep('identity')
            ->boot();

        $visibleFields = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->instance()
            ->getVisibleFieldsProperty();

        expect($visibleFields)->not->toHaveKey('formation_state')
            ->and($visibleFields)->toHaveKey('entity_type');
    });

    it('lets sole proprietors advance without a formation state', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'sole_prop', 'formation_state' => null])
            ->onStep('identity')
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertHasNoErrors('coreData.formation_state');
    });

    it('shows formation_state for corporations and requires it', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'corporation', 'formation_state' => null])
            ->onStep('identity')
            ->boot();

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application]);

        expect($component->instance()->getVisibleFieldsProperty())->toHaveKey('formation_state');

        $component->call('nextStep')->assertHasErrors(['coreData.formation_state']);
    });
});
