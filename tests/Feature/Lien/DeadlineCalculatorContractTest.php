<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Seo\DeadlineRulesExport;
use App\Support\Seo\States;
use Carbon\CarbonImmutable;

/*
 * The contract behind the free deadline calculator (EREG-13). The browser
 * (resources/js/lien-deadline-calculator.js) is a port of
 * DeadlineRulesExport::evaluate(); this test checks evaluate() against the
 * paid product's DeadlineCalculator for every state, so the free tool can
 * never show a date the engine would not compute.
 */

const CONTRACT_PROFILES = [
    'Texas residential subcontractor' => [
        'claimant' => 'subcontractor', 'scope' => 'residential',
        'dates' => ['first_furnish' => '2026-01-10', 'last_furnish' => '2026-03-20', 'completion' => '2026-04-30'],
    ],
    // A notice of completion with no preliminary notice before it: shortens
    // the deadline in California, ends lien rights in Georgia and Utah.
    'California GC' => [
        'claimant' => 'gc', 'scope' => 'commercial',
        'dates' => ['first_furnish' => '2025-11-03', 'last_furnish' => '2026-01-31', 'completion' => '2026-01-31', 'noc' => '2026-02-10'],
    ],
    'Florida supplier to GC' => [
        'claimant' => 'supplier_to_contractor', 'scope' => 'residential',
        'dates' => ['first_furnish' => '2026-02-02', 'last_furnish' => '2026-05-31', 'completion' => '2026-06-15'],
    ],
    'Oregon commercial sub' => [
        'claimant' => 'subcontractor', 'scope' => 'commercial',
        'dates' => ['first_furnish' => '2025-12-15', 'last_furnish' => '2026-08-31', 'completion' => '2026-09-10'],
    ],
    // Month ends (Jan 31 + N months) and a notice of completion that came
    // after the preliminary notice.
    'Kansas sub' => [
        'claimant' => 'subcontractor', 'scope' => 'residential',
        'dates' => ['first_furnish' => '2026-01-31', 'last_furnish' => '2026-01-31', 'completion' => '2026-02-28', 'noc' => '2026-03-05'],
        'prelim_before_noc' => true,
    ],
];

const CONTRACT_CLAIMANTS = ['gc', 'subcontractor', 'sub_sub_contractor', 'supplier_to_owner', 'supplier_to_contractor', 'supplier_to_subcontractor'];

beforeEach(function () {
    $this->business = Business::factory()->create();
    $this->engine = app(DeadlineCalculator::class);
});

/**
 * Run the engine on a project with these facts and return its deadlines by
 * document slug.
 *
 * @return array<string, array{date: ?string, status: DeadlineStatus, reason: ?string}>
 */
function engineDeadlines(object $test, string $state, array $profile): array
{
    $dates = $profile['dates'];
    $noc = $dates['noc'] ?? null;

    $project = LienProject::factory()->forBusiness($test->business)->create([
        'jobsite_state' => $state,
        'claimant_type' => $profile['claimant'],
        'property_class' => $profile['scope'],
        'first_furnish_date' => $dates['first_furnish'] ?? null,
        'last_furnish_date' => $dates['last_furnish'] ?? null,
        'completion_date' => $dates['completion'] ?? null,
        'noc_filed_date' => $noc,
        'prelim_notice_sent_at' => $noc && ($profile['prelim_before_noc'] ?? false)
            ? CarbonImmutable::parse($noc)->subDay()
            : null,
    ]);

    $test->engine->calculateForProject($project);

    $out = [];
    foreach ($project->deadlines()->whereNotNull('deadline_rule_id')->with('documentType')->get() as $deadline) {
        $slug = $deadline->documentType->slug;
        expect($out)->not->toHaveKey($slug, "{$state}: two {$slug} rules match one role and project type");
        $out[$slug] = [
            'date' => $deadline->due_date?->toDateString(),
            'status' => $deadline->status,
            'reason' => $deadline->status_reason,
        ];
    }

    return $out;
}

/** Compare evaluate() with the engine for one state and profile; returns evaluate()'s rows by slug. */
function assertMatchesEngine(object $test, string $state, array $profile, string $label): array
{
    $export = DeadlineRulesExport::forState($state);
    $rows = DeadlineRulesExport::evaluate($export, $profile['claimant'], $profile['scope'], $profile['dates'], $profile['prelim_before_noc'] ?? false);
    $engine = engineDeadlines($test, $state, $profile);
    $context = "{$state} / {$label}";

    expect(array_column($rows, 'doc'))->toEqualCanonicalizing(array_keys($engine), "{$context}: different rules matched");

    $bySlug = [];
    foreach ($rows as $row) {
        $doc = $row['doc'];
        $bySlug[$doc] = $row;
        $expected = $engine[$doc];

        match ($row['status']) {
            'date' => expect($row['date'])->toBe($expected['date'], "{$context}: {$doc} date"),
            'missing' => expect($expected['date'])->toBeNull("{$context}: {$doc} has an engine date but no calculator date"),
            'no_rights' => expect($expected['reason'])->toBe('no_lien_rights_for_claimant', "{$context}: {$doc}"),
            'blocked' => expect($expected['reason'])->toBe('noc_requires_prior_prelim', "{$context}: {$doc}"),
            // Statutory schedules the table states in words: the calculator
            // prints the text, never a date from a zero-day offset.
            'display' => expect($row['display'])->not->toBeEmpty(),
        };

        if ($row['status'] === 'date') {
            expect($expected['status'])->not->toBe(DeadlineStatus::NotApplicable, "{$context}: {$doc} is not applicable in the engine");
        }
    }

    return $bySlug;
}

it('computes the same dates as the engine for every state', function (string $state) {
    foreach (CONTRACT_PROFILES as $label => $profile) {
        assertMatchesEngine($this, $state, $profile, $label);
    }

    // Every role and project type, on the first profile's dates.
    foreach (CONTRACT_CLAIMANTS as $claimant) {
        foreach (['residential', 'commercial'] as $scope) {
            $profile = ['claimant' => $claimant, 'scope' => $scope] + CONTRACT_PROFILES['Texas residential subcontractor'];
            assertMatchesEngine($this, $state, $profile, "{$claimant} {$scope}");
        }
    }
})->with(fn () => array_keys(States::names()));

it('gives the named cases their expected dates in their own states', function () {
    $tx = assertMatchesEngine($this, 'TX', CONTRACT_PROFILES['Texas residential subcontractor'], 'TX');
    expect($tx['mechanics_lien']['date'])->toBe('2026-06-15')
        ->and($tx['prelim_notice']['status'])->toBe('display')
        ->and($tx)->not->toHaveKey('noi');

    $ca = assertMatchesEngine($this, 'CA', CONTRACT_PROFILES['California GC'], 'CA');
    expect($ca['mechanics_lien']['date'])->toBe('2026-03-12')
        ->and($ca['mechanics_lien']['noc_shortened'])->toBeTrue()
        ->and($ca['mechanics_lien']['original_date'])->toBe('2026-05-01')
        ->and($ca)->not->toHaveKey('prelim_notice');

    $fl = assertMatchesEngine($this, 'FL', CONTRACT_PROFILES['Florida supplier to GC'], 'FL');
    expect($fl['prelim_notice']['date'])->toBe('2026-03-19')
        ->and($fl['mechanics_lien']['date'])->toBe('2026-08-29');

    $or = assertMatchesEngine($this, 'OR', CONTRACT_PROFILES['Oregon commercial sub'], 'OR');
    expect($or['prelim_notice']['date'])->toBe('2025-12-23')
        ->and($or['mechanics_lien']['date'])->toBe('2026-11-29');

    $ks = assertMatchesEngine($this, 'KS', CONTRACT_PROFILES['Kansas sub'], 'KS');
    expect($ks['mechanics_lien']['date'])->toBe('2026-04-30')
        ->and($ks['prelim_notice']['status'])->toBe('display');

    // Notice of intent: the lien deadline less the state's lead time.
    $ar = assertMatchesEngine($this, 'AR', CONTRACT_PROFILES['Kansas sub'], 'AR');
    expect($ar['noi']['date'])->toBe(CarbonImmutable::parse($ar['noi']['lien_date'])->subDays(10)->toDateString());

    // A notice of completion before the preliminary notice ends Utah lien rights.
    $ut = assertMatchesEngine($this, 'UT', CONTRACT_PROFILES['California GC'], 'UT');
    expect($ut['mechanics_lien']['status'])->toBe('blocked');
});

it('asks for the dates each rule reads and never leaks raw column names', function () {
    $tx = DeadlineRulesExport::forState('TX');
    $lien = collect($tx['rules'])->firstWhere(fn ($r) => $r['doc'] === 'mechanics_lien' && $r['claimant'] === 'subcontractor');

    expect($lien['inputs'])->toBe(['last_furnish', 'special_fab'])
        ->and($lien['anchor'])->toBe('later_of')
        ->and($lien['when'])->toContain('15th day of the 3rd month');

    $json = json_encode(DeadlineRulesExport::allCached());
    foreach ([...array_keys(DeadlineRulesExport::DATE_KEYS), 'lien_anchor_logic'] as $column) {
        expect($json)->not->toContain($column);
    }
});

it('lets the calculator use a special fabrication delivery date the engine has no field for', function () {
    $export = DeadlineRulesExport::forState('TX');
    $dates = CONTRACT_PROFILES['Texas residential subcontractor']['dates'] + ['special_fab' => '2026-05-02'];

    $rows = collect(DeadlineRulesExport::evaluate($export, 'subcontractor', 'residential', $dates))->keyBy('doc');

    // later_of(last furnishing in March, delivery in May): 15th of the 3rd month after May.
    expect($rows['mechanics_lien']['date'])->toBe('2026-08-15');
});
