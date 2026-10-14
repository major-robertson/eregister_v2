<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Models\LienDocumentType;
use App\Domains\Lien\Models\LienProject;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;

function runDeadlineSync(array $arguments): array
{
    // Own buffer: on the first DB touch LazilyRefreshDatabase runs migrate:fresh,
    // which would replace Artisan::output().
    $output = new BufferedOutput;
    $code = Artisan::call('lien:sync-deadline-rules', $arguments, $output);

    return [$code, $output->fetch()];
}

/** Put Virginia back the way production has it before EREG-113. */
function seedOldVirginiaRules(): void
{
    DB::table('lien_state_rules')->where('state', 'VA')->update([
        'lien_anchor_logic' => 'later_of',
        'lien_anchor_alt_field' => 'contract_terminated_date',
        'notes' => 'Deadline is the later of (i) 90 days from the last day of the month of last furnishing, or (ii) 90 days from completion.',
    ]);
    DB::table('lien_deadline_rules')->where('state', 'VA')->update([
        'conditions_json' => '{"anchor":"later_of","dates":["last_furnish_date","contract_terminated_date"]}',
    ]);
}

function vaLienDue(LienProject $project): string
{
    return $project->deadlines()
        ->whereHas('documentType', fn ($q) => $q->where('slug', 'mechanics_lien'))
        ->first()->due_date->toDateString();
}

beforeEach(function () {
    seedOldVirginiaRules();

    $this->project = LienProject::factory()->forBusiness(Business::factory()->create())->create([
        'jobsite_state' => 'VA',
        'claimant_type' => 'gc',
        'property_class' => 'residential',
        'first_furnish_date' => '2026-07-16',
        'last_furnish_date' => '2026-07-20',
        'completion_date' => '2026-07-16',
    ]);
    app(DeadlineCalculator::class)->calculateForProject($this->project);

    $this->travelTo('2026-10-15 09:00');
});

it('prints the Virginia rule changes and moved deadlines without saving them', function () {
    expect(vaLienDue($this->project))->toBe('2026-10-29');

    [$code, $output] = runDeadlineSync(['states' => ['va']]);

    expect($code)->toBe(0)
        ->and($output)->toContain('state row: lien_anchor_logic, lien_anchor_alt_field, notes')
        ->and($output)->toContain('12 deadline rows changed.')
        ->and($output)->toContain("project {$this->project->id} (")
        ->and($output)->toContain('mechanics_lien 2026-10-29 -> 2026-10-14  PASSED')
        ->and($output)->toContain('Dry run: 1 deadlines would move, 0 problems. Nothing was saved.');

    expect(vaLienDue($this->project))->toBe('2026-10-29')
        ->and(DB::table('lien_state_rules')->where('state', 'VA')->value('lien_anchor_logic'))->toBe('later_of');
});

it('writes the rules and the new deadlines with --apply, then reports everything in sync', function () {
    [$code, $output] = runDeadlineSync(['states' => ['VA'], '--apply' => true]);

    expect($code)->toBe(0)
        ->and($output)->toContain('Done: 1 deadlines moved.');

    expect(vaLienDue($this->project))->toBe('2026-10-14')
        ->and(DB::table('lien_state_rules')->where('state', 'VA')->value('lien_anchor_logic'))->toBe('single')
        ->and(json_decode(DB::table('lien_deadline_rules')->where('state', 'VA')->value('conditions_json'), true))
        ->toEqual(['cap' => ['trigger_event' => 'completion_date', 'calc_method' => 'days_after_date', 'offset_days' => 90]]);

    [$code, $output] = runDeadlineSync(['states' => ['VA']]);

    expect($code)->toBe(0)
        ->and($output)->toContain('state row: in sync')
        ->and($output)->toContain('0 deadline rows changed.')
        ->and($output)->toContain('Dry run: 0 deadlines would move, 0 problems.');
});

it('keeps each deadline on its rule row and saves nothing when a row is missing', function () {
    $lienType = LienDocumentType::where('slug', 'mechanics_lien')->value('id');
    $ruleId = $this->project->deadlines()->where('document_type_id', $lienType)->value('deadline_rule_id');

    DB::table('lien_deadline_rules')->where('state', 'VA')->where('claimant_type', 'subcontractor')->where('effective_scope', 'commercial')->delete();

    [$code, $output] = runDeadlineSync(['states' => ['VA'], '--apply' => true]);

    expect($code)->toBe(2)
        ->and($output)->toContain('MISSING ROW (0 matches) mechanics_lien subcontractor [commercial]')
        ->and($output)->toContain('Nothing was saved: 1 problems above.')
        ->and(vaLienDue($this->project))->toBe('2026-10-29')
        ->and($this->project->deadlines()->where('document_type_id', $lienType)->value('deadline_rule_id'))->toBe($ruleId);
});
