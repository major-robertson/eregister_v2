<?php

use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

beforeEach(fn () => View::share('errors', new ViewErrorBag));

describe('responsible_people schema_groups definition', function () {
    it('declares the expected six visual sections in order', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['responsible_people']['fields']['responsible_people'];

        expect($field['schema_groups'] ?? null)->toBeArray()->toHaveCount(6);

        $titles = array_map(fn ($g) => $g['title'], $field['schema_groups']);
        expect($titles)->toBe([
            'Identity',
            'Contact',
            'Personal',
            'Driver License',
            'Home Address',
            'Authorization',
        ]);
    });

    it('places first_name and last_name in a side-by-side row inside Identity', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $identity = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema_groups'][0];

        // The row syntax: an array nested inside `fields` means "render
        // these together in one grid row". Single strings are full-width.
        expect($identity['fields'][0])->toBe(['first_name', 'last_name'])
            ->and($identity['fields'][1])->toBe('title');
    });
});

describe('Driver License is now base + required', function () {
    it('promotes all three DL fields to required in the base schema', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $schema = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema'];

        expect($schema['driver_license_state']['rules'])->toContain('required')
            ->and($schema['driver_license_number']['rules'])->toContain('required')
            ->and($schema['driver_license_expiration']['rules'])->toContain('required')
            ->and($schema['driver_license_expiration']['rules'])->toContain('after:today');
    });

    it('drops the old per-state DL appendices in CA and TX', function () {
        foreach (['CA', 'TX'] as $stateCode) {
            $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);
            $stateExtras = $merged['state_steps']['state_responsible_people']['fields']['responsible_people_extra']['schema'] ?? [];

            $extraKeys = array_keys($stateExtras);
            $dlKeys = array_filter($extraKeys, fn ($k) => str_contains(strtolower($k), 'driver_license'));

            // Same reason as above — Pest's toBeEmpty doesn't take a
            // failure message arg in this version, so on regression the
            // assertion failure will print the array contents and the
            // surrounding state code from the stack trace.
            expect($dlKeys)->toBeEmpty();
        }
    });
});

describe('Repeater modal grouped rendering', function () {
    it('renders each schema group title and the side-by-side row in the modal markup', function () {
        $application = RunnerTestFactory::make()
            ->onStep('responsible_people')
            ->boot();

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        // Section titles: every schema_groups entry should appear in the
        // rendered markup. We assert on the visible text only (not Flux's
        // internal classes) so the test isn't coupled to Flux versions.
        // (Pest's toContain accepts multiple needles, all required.)
        expect($html)->toContain(
            'Identity',
            'Contact',
            'Personal',
            'Driver License',
            'Home Address',
            'Authorization',
        );

        // Row layout: first_name + last_name should share a 2-col grid
        // container. Asserting on the Tailwind class is the most precise
        // hook — if it changes, this test will catch the regression.
        expect($html)->toContain('grid-cols-2');
    });

    it('no longer renders the California Requirements per-state section', function () {
        $application = RunnerTestFactory::make()
            ->onStep('responsible_people')
            ->boot();

        $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
            ->html();

        expect($html)->not->toContain('California Requirements')
            ->and($html)->not->toContain('California Driver License Number');
    });
});
