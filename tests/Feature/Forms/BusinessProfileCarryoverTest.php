<?php

use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * persist_to_business / prefill round-trip hygiene.
 *
 * Bug: step saves wrote effectively-empty values (e.g. a mailing_address
 * of {"zip": ""}) to the business profile, and new applications prefilled
 * them back — pre-checking the "different mailing address" toggle and
 * surfacing blank "random" data in fields and repeater modals.
 */
describe('business profile carryover hygiene', function () {
    it('does not persist empty composites or blanks to the business profile', function () {
        $application = RunnerTestFactory::make()
            ->coreData([
                'dba_name' => '',
                'mailing_address' => ['line1' => '', 'city' => '', 'state' => '', 'zip' => ''],
            ])
            ->onStep('identity')
            ->boot();

        $business = $application->business;
        $business->update(['dba_name' => 'Kept Trade Name']);

        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('nextStep');

        $business->refresh();

        expect($business->mailing_address)->toBeNull()
            // Blank answers must not wipe existing profile values.
            ->and($business->dba_name)->toBe('Kept Trade Name')
            ->and($business->legal_name)->toBe('Form Runner Test LLC');
    });

    it('does not persist responsible-person rows that are only husks', function () {
        $application = RunnerTestFactory::make()
            ->coreData([
                'responsible_people' => [
                    [
                        '_id' => 'real-row',
                        'first_name' => 'Jane',
                        'last_name' => 'Tester',
                        'title' => 'President',
                        'phone' => '5551234567',
                        'email' => 'jane@corp.test',
                        'ownership_percent' => '100',
                    ],
                    ['_id' => 'husk-row', 'first_name' => '', 'last_name' => ''],
                ],
            ])
            ->onStep('responsible_people')
            ->boot();

        // previousStep saves without validating, letting the husk row
        // reach the persistence layer the same way a draft save would.
        Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->call('previousStep');

        $people = $application->business->refresh()->responsible_people;

        expect($people)->toHaveCount(1)
            ->and($people[0]['first_name'])->toBe('Jane');
    });

    it('ignores an empty mailing-address husk on the profile when prefilling', function () {
        $application = RunnerTestFactory::make()->boot();
        $application->business->update(['mailing_address' => ['zip' => '']]);
        $application->update(['core_data' => null]);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application->fresh()]);

        expect($component->get('coreData.mailing_address_same'))->toBe('1')
            ->and($component->get('coreData.mailing_address'))->toBeNull();
    });

    it('still prefills a real mailing address from the profile', function () {
        $application = RunnerTestFactory::make()->boot();
        $application->business->update(['mailing_address' => [
            'line1' => '55 Mail Rd', 'city' => 'Boise', 'state' => 'ID', 'zip' => '83701',
        ]]);
        $application->update(['core_data' => null]);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application->fresh()]);

        expect($component->get('coreData.mailing_address_same'))->toBe('0')
            ->and($component->get('coreData.mailing_address.line1'))->toBe('55 Mail Rd');
    });

    it('skips nameless responsible-person husks when prefilling', function () {
        $application = RunnerTestFactory::make()->boot();
        $application->business->update(['responsible_people' => [
            ['_id' => 'husk', 'first_name' => '', 'last_name' => ''],
            ['_id' => 'real', 'first_name' => 'Sam', 'last_name' => 'Solo'],
        ]]);
        $application->update(['core_data' => null]);

        $component = Livewire::test(MultiStateFormRunner::class, ['application' => $application->fresh()]);

        $people = $component->get('coreData.responsible_people');

        expect($people)->toHaveCount(1)
            ->and($people[0]['first_name'])->toBe('Sam');
    });
});
