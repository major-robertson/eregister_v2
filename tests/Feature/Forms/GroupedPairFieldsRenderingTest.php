<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * Regression: step-level `groups` may use the inline-row pair syntax
 * (['majority_shareholder_first_name', 'majority_shareholder_last_name']).
 * The runner blade's mailing-address group check used to intersect the
 * raw group fields without flattening, throwing "Array to string
 * conversion" the moment a step with paired fields rendered.
 */
describe('steps with inline-row pair groups', function () {
    it('renders the contact step without the removed additional-contacts fields', function () {
        $application = RunnerTestFactory::make()
            ->onStep('contact_and_address')
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->assertOk()
            ->assertSee('Principal Business Address')
            ->assertDontSee('Business Records Contact')
            ->assertDontSee('Business Fax Number');
    });

    it('renders the entity_extras step (paired shareholder name row) without errors', function () {
        $application = RunnerTestFactory::make()
            ->coreData(['entity_type' => 'corporation'])
            ->onStep('entity_extras')
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->assertOk()
            ->assertSee('Role in Supply Chain');
    });

    it('advances past a step whose visible fields carry no validation rules', function () {
        // Regression: a sole proprietor on entity_extras sees only the
        // optional supply-chain checkboxes (every other field is gated
        // to corp/LLC types). Zero built rules used to make Livewire's
        // validate([]) throw MissingRulesException.
        $application = RunnerTestFactory::make()
            ->forStates(['AL'])
            ->coreData([
                'entity_type' => 'sole_prop',
                'individual_ssn' => '123-45-6789',
            ])
            ->onStep('entity_extras')
            ->boot();

        // entity_extras is the last core step and AL has no
        // state-specific questions, so Next lands directly on review.
        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertOk()
            ->assertSet('currentPhase', 'review');
    });

    it('marks every skipped state complete and hides the State Details segment', function () {
        // Regression: skip-through used the model's stale
        // current_state_index, marking the first state complete
        // repeatedly while leaving the rest pending — which disabled
        // the review screen's payment button.
        $application = RunnerTestFactory::make()
            ->forStates(['AL', 'CO', 'ID'])
            ->coreData([
                'entity_type' => 'sole_prop',
                'individual_ssn' => '123-45-6789',
            ])
            ->onStep('entity_extras')
            ->boot();

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertOk()
            ->assertSet('currentPhase', 'review')
            // No selected state has state questions → no states segment.
            ->assertDontSee('State Details');

        $statuses = $application->fresh()->states()->pluck('status', 'state_code')->all();

        expect($statuses)->toBe(['AL' => 'complete', 'CO' => 'complete', 'ID' => 'complete']);
    });

    it('advances from acquisition_and_history into the next visible step without crashing', function () {
        // Mirrors the user-reported crash: Next from acquisition_and_history
        // lands on a paired-group step (bank and cards are skipped for
        // non-bank/non-card states, contacts folded into the contact step).
        $application = RunnerTestFactory::make()
            ->forStates(['AL'])
            ->coreData([
                'ever_issued_tax_certificate' => '0',
                'was_known_by_another_name' => '0',
                'entity_involved_in_merger' => '0',
                'entity_legal_structure_change' => '0',
                'entity_underwent_restructuring' => '0',
                'entity_currently_forming_or_acquiring' => '0',
                'entity_currently_incorporating_existing' => '0',
            ])
            ->onStep('acquisition_and_history')
            ->boot();

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep')
            ->assertOk()
            ->assertSet('currentStepKey', 'entity_extras');
    });
});
