<?php

use App\Domains\Forms\Engine\Applicability;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Engine\VisibleFieldResolver;

/**
 * Acceptance criteria for the clean rebuild (plan §Acceptance):
 * shared questions are asked exactly once at the core level, per-state
 * values render as matrices, state-only follow-ups appear only for the
 * states picked in the relevant applies_* checklist, and small state
 * selections never see a universal questionnaire.
 *
 * Engine-level tests (no DB): visibility resolved through the same
 * VisibleFieldResolver the runner uses.
 */
function coreFieldsVisibleFor(array $selectedStates, array $coreData = []): array
{
    $base = app(FormRegistry::class)->getBase('sales_tax_permit');
    $resolver = app(VisibleFieldResolver::class);

    $visible = [];
    foreach ($base['core_steps'] as $step) {
        $visible += $resolver->resolve($step, [
            'coreData' => $coreData,
            'stateData' => [],
            'rowData' => [],
            'stateCode' => null,
            'selectedStates' => $selectedStates,
        ]);
    }

    return $visible;
}

function stateFieldsVisibleFor(string $stateCode, array $selectedStates, array $coreData = [], array $stateData = []): array
{
    $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);
    $resolver = app(VisibleFieldResolver::class);

    $visible = [];
    foreach ($merged['state_steps'] as $stepKey => $step) {
        if ($stepKey === 'state_responsible_people') {
            continue;
        }
        $visible += $resolver->resolve($step, [
            'coreData' => $coreData,
            'stateData' => $stateData,
            'rowData' => [],
            'stateCode' => $stateCode,
            'selectedStates' => $selectedStates,
        ]);
    }

    return $visible;
}

describe('shared questions asked exactly once', function () {
    it('asks alcohol once for CA+TX+OH+TN+OK+IL and keys it to states', function () {
        $visible = coreFieldsVisibleFor(['CA', 'TX', 'OH', 'TN', 'OK', 'IL']);

        expect($visible)->toHaveKey('applies_alcohol')
            ->and($visible['applies_alcohol']['type'])->toBe('anywhere_states');

        // No state file re-asks the alcohol yes/no.
        foreach (['CA', 'TX', 'OH', 'TN', 'OK', 'IL'] as $code) {
            $stateVisible = stateFieldsVisibleFor($code, ['CA', 'TX', 'OH', 'TN', 'OK', 'IL']);
            $alcoholGates = collect(array_keys($stateVisible))
                ->filter(fn ($k) => str_contains($k, 'sell_alcohol') || str_contains($k, 'alcoholic_beverages'))
                ->filter(fn ($k) => ! str_contains($k, 'permit'))
                ->all();
            expect($alcoholGates)->toBe([], "{$code} re-asks the alcohol gate");
        }
    });

    it('shows TX alcohol permit follow-up only when alcohol applies to TX', function () {
        $selected = ['CA', 'TX', 'NY'];

        $without = stateFieldsVisibleFor('TX', $selected, [
            'applies_alcohol' => ['anywhere' => '1', 'states' => ['CA']],
        ]);
        expect($without)->not->toHaveKey('tx_alcoholic_beverages_permit');

        $with = stateFieldsVisibleFor('TX', $selected, [
            'applies_alcohol' => ['anywhere' => '1', 'states' => ['CA', 'TX']],
        ]);
        expect($with)->toHaveKey('tx_alcoholic_beverages_permit');
    });

    it('shows OK tobacco agreements only when tobacco applies to OK', function () {
        $selected = ['OK', 'IL'];

        $without = stateFieldsVisibleFor('OK', $selected, [
            'applies_tobacco_vape' => ['anywhere' => '0', 'states' => []],
        ]);
        expect($without)->not->toHaveKey('ok_tobacco_agreement_one');

        $with = stateFieldsVisibleFor('OK', $selected, [
            'applies_tobacco_vape' => ['anywhere' => '1', 'states' => ['OK']],
        ]);
        expect($with)->toHaveKey('ok_tobacco_agreement_one');
    });
});

describe('matrix per-state values', function () {
    it('renders sales tax start date as one matrix covering every selected state', function () {
        $visible = coreFieldsVisibleFor(['CA', 'TX', 'NY']);

        expect($visible)->toHaveKey('matrix_sales_tax_start_date');
        expect(Applicability::statesFor($visible['matrix_sales_tax_start_date'], ['CA', 'TX', 'NY']))
            ->toBe(['CA', 'TX', 'NY']);
    });

    it('renders monthly taxable sales rows only for states that ask (CA, TX)', function () {
        $visible = coreFieldsVisibleFor(['CA', 'TX', 'NY']);

        expect($visible)->toHaveKey('matrix_estimated_monthly_taxable_sales');
        expect(Applicability::statesFor($visible['matrix_estimated_monthly_taxable_sales'], ['CA', 'TX', 'NY']))
            ->toBe(['CA', 'TX']);
    });

    it('hides the employee-count matrix when no applicable state is selected', function () {
        $visible = coreFieldsVisibleFor(['CA', 'TX', 'NY']);

        expect($visible)->not->toHaveKey('matrix_employee_count');

        $withFl = coreFieldsVisibleFor(['CA', 'FL']);
        expect($withFl)->toHaveKey('matrix_employee_count');
    });
});

describe('no universal questionnaire (§1.5)', function () {
    it('shows no product/industry applies questions for AL+AZ+CO', function () {
        $visible = coreFieldsVisibleFor(['AL', 'AZ', 'CO']);

        $appliesKeys = collect(array_keys($visible))
            ->filter(fn ($k) => str_starts_with($k, 'applies_'))
            ->values()
            ->all();

        // Seasonal is the only activity the legacy standard flow asks
        // every state.
        expect($appliesKeys)->toBe(['applies_seasonal']);
    });

    it('never asks about fireworks or cannabis unless a relevant state is selected', function () {
        expect(coreFieldsVisibleFor(['CA', 'NY']))->not->toHaveKey('applies_fireworks')
            ->and(coreFieldsVisibleFor(['CA', 'NY']))->not->toHaveKey('applies_cannabis')
            ->and(coreFieldsVisibleFor(['TX']))->toHaveKey('applies_fireworks')
            ->and(coreFieldsVisibleFor(['IL']))->toHaveKey('applies_cannabis');
    });

    it('hides the bank section unless a bank-collecting state is selected', function () {
        expect(coreFieldsVisibleFor(['AL', 'CO']))->not->toHaveKey('has_business_bank_account')
            ->and(coreFieldsVisibleFor(['AL', 'NV']))->toHaveKey('has_business_bank_account');
    });
});

describe('per-person extras stay per-state', function () {
    it('declares person extras only for the states whose source asks them', function () {
        $registry = app(FormRegistry::class);

        $statesWithExtras = [];
        foreach (['CA', 'TX', 'NY', 'FL', 'IL', 'PA', 'CT', 'GA', 'MD', 'MI', 'NJ', 'OH', 'OK', 'TN', 'WA', 'WI', 'MO', 'NC'] as $code) {
            $merged = $registry->get('sales_tax_permit', $code);
            $schema = $merged['state_steps']['state_responsible_people']['fields']['responsible_people_extra']['schema'] ?? [];
            if ($schema !== []) {
                $statesWithExtras[] = $code;
            }
        }

        expect($statesWithExtras)->toBe(['NY', 'PA', 'CT', 'GA', 'OH', 'OK', 'TN']);
    });

    it('restores the NY per-person compliance questionnaire', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'NY');
        $schema = $merged['state_steps']['state_responsible_people']['fields']['responsible_people_extra']['schema'];

        expect($schema)->toHaveKeys([
            'ny_profit_distribution_percentage', 'ny_actively_operating', 'ny_check_signing_authority',
            'ny_open_liens', 'ny_bankruptcy', 'ny_felony_business_conduct',
        ])
            ->and(count($schema))->toBeGreaterThanOrEqual(20);
    });
});

describe('multi-substep state splits', function () {
    it('splits the big states into topic substeps and keeps small states on one step', function () {
        $registry = app(FormRegistry::class);

        // getCurrentSteps() excludes state_responsible_people; mirror that.
        $stepCount = fn (string $code) => count(
            collect($registry->get('sales_tax_permit', $code)['state_steps'])
                ->except('state_responsible_people')
                ->all()
        );

        foreach (['PA', 'NY', 'TX', 'FL', 'IL', 'CT', 'MI', 'CA', 'NJ', 'OK'] as $big) {
            expect($stepCount($big))->toBeGreaterThan(2, "{$big} should be multi-substep");
        }

        foreach (['OH', 'GA', 'MD', 'TN', 'WA', 'WI', 'MO', 'NC'] as $small) {
            // One real step (state_details) + the empty base placeholder
            // collapses to the same key, so exactly 1.
            expect($stepCount($small))->toBe(1, "{$small} should be single-step");
        }
    });
});
