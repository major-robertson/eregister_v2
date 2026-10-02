<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Engine\RuleDateMath;
use App\Domains\Lien\Enums\CalcMethod;
use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use Carbon\CarbonImmutable;

function mathRule(string $method, array $overrides = []): array
{
    return $overrides + [
        'calc_method' => $method,
        'trigger_event' => 'last_furnish_date',
        'offset_days' => null,
        'offset_months' => null,
        'day_of_month' => null,
        'conditions' => null,
    ];
}

describe('days_before_date', function () {
    it('reads notice of intent rows as a calc method instead of failing on the enum cast', function () {
        $noi = LienDeadlineRule::where('state', 'AR')->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))->first();

        expect($noi->calc_method)->toBe(CalcMethod::DaysBeforeDate)
            ->and($noi->calc_method->label())->toBe('Days Before Date');
    });

    it('counts back from the lien filing deadline and needs one to compute', function () {
        $rule = mathRule('days_before_date', ['trigger_event' => 'lien_filing_date', 'offset_days' => 10]);

        $due = RuleDateMath::dueDate($rule, null, [RuleDateMath::LIEN_DEADLINE => '2026-03-05']);
        expect($due['date']->toDateString())->toBe('2026-02-23');

        $none = RuleDateMath::dueDate($rule, null, ['last_furnish_date' => '2026-01-01']);
        expect($none['date'])->toBeNull()
            ->and($none['reason'])->toBe('missing_lien_deadline')
            ->and(RuleDateMath::inputs($rule))->toBe([]);
    });

    it('treats a negative lead time as zero', function () {
        $anchor = CarbonImmutable::parse('2026-03-05');

        expect(RuleDateMath::apply($anchor, CalcMethod::DaysBeforeDate, -5, null, null)->toDateString())->toBe('2026-03-05');
    });

    it('matches the engine notice of intent, which uses the state lead time', function () {
        $business = Business::factory()->create();
        $project = LienProject::factory()->forBusiness($business)->create([
            'jobsite_state' => 'AR',
            'claimant_type' => 'subcontractor',
            'property_class' => 'residential',
            'first_furnish_date' => '2026-01-05',
            'last_furnish_date' => '2026-02-20',
            'completion_date' => null,
        ]);

        app(DeadlineCalculator::class)->calculateForProject($project);

        $deadline = fn (string $slug) => $project->deadlines()->whereHas('documentType', fn ($q) => $q->where('slug', $slug))->first();
        $lien = $deadline('mechanics_lien');
        $noi = $deadline('noi');
        $noiRule = RuleDateMath::fromModel(LienDeadlineRule::findOrFail($noi->deadline_rule_id));

        expect(LienStateRule::find('AR')->noi_lead_time_days)->toBe(10)
            ->and($noi->due_date->toDateString())->toBe($lien->due_date->copy()->subDays(10)->toDateString())
            ->and(RuleDateMath::dueDate($noiRule, null, [RuleDateMath::LIEN_DEADLINE => $lien->due_date])['date']->toDateString())
            ->toBe($noi->due_date->toDateString());
    });

    it('carries the state lead time on every notice of intent row', function () {
        $rows = LienDeadlineRule::query()
            ->whereHas('documentType', fn ($q) => $q->where('slug', 'noi'))
            ->with('stateRule')
            ->get();

        expect($rows)->not->toBeEmpty();
        foreach ($rows as $row) {
            expect($row->calc_method)->toBe(CalcMethod::DaysBeforeDate)
                ->and((int) $row->offset_days)->toBe((int) $row->stateRule->noi_lead_time_days, "{$row->state} NOI lead time");
        }
    });
});

describe('calendar math', function () {
    it('clamps months to the end of the month like Carbon addMonthsNoOverflow', function () {
        $jan31 = CarbonImmutable::parse('2026-01-31');

        expect(RuleDateMath::apply($jan31, CalcMethod::MonthsAfterDate, null, 1, null)->toDateString())->toBe('2026-02-28')
            ->and(RuleDateMath::apply(CarbonImmutable::parse('2024-01-31'), CalcMethod::MonthsAfterDate, null, 1, null)->toDateString())->toBe('2024-02-29')
            ->and(RuleDateMath::apply(CarbonImmutable::parse('2026-08-31'), CalcMethod::MonthsAfterDate, null, 6, null)->toDateString())->toBe('2027-02-28');
    });

    it('counts "the 15th day of the Nth month after the month of" from the month, not the day', function () {
        foreach (['2026-03-01', '2026-03-20', '2026-03-31'] as $date) {
            expect(RuleDateMath::apply(CarbonImmutable::parse($date), CalcMethod::MonthDayAfterMonthOfDate, null, 3, 15)->toDateString())->toBe('2026-06-15');
        }
    });

    it('counts days from the end of the anchor month', function () {
        expect(RuleDateMath::apply(CarbonImmutable::parse('2026-02-03'), CalcMethod::DaysAfterEndOfMonthOfDate, 90, null, null)->toDateString())->toBe('2026-05-29');
    });

    it('takes the later of the listed dates, and the first furnishing date for a contract date', function () {
        $rule = mathRule('days_after_date', ['offset_days' => 10, 'conditions' => ['anchor' => 'later_of', 'dates' => ['last_furnish_date', 'special_fab_delivery_date']]]);

        expect(RuleDateMath::dueDate($rule, null, ['last_furnish_date' => '2026-03-01'])['date']->toDateString())->toBe('2026-03-11')
            ->and(RuleDateMath::dueDate($rule, null, ['last_furnish_date' => '2026-03-01', 'special_fab_delivery_date' => '2026-04-01'])['date']->toDateString())->toBe('2026-04-11')
            ->and(RuleDateMath::inputs($rule))->toBe(['last_furnish_date', 'special_fab_delivery_date']);

        $contract = mathRule('days_after_date', ['trigger_event' => 'contract_date', 'offset_days' => 5]);
        expect(RuleDateMath::dueDate($contract, null, ['first_furnish_date' => '2026-01-01'])['date']->toDateString())->toBe('2026-01-06');
    });

    it('falls back to the state anchor logic, which with no dates listed never resolves', function () {
        $rule = mathRule('days_after_date', ['trigger_event' => 'first_furnish_date', 'offset_days' => 0]);

        $due = RuleDateMath::dueDate($rule, 'later_of', ['first_furnish_date' => '2026-01-01']);
        expect($due['date'])->toBeNull()
            ->and($due['reason'])->toBe('missing_anchor')
            ->and($due['missing'])->toBe(['first_furnish_date']);
    });
});
