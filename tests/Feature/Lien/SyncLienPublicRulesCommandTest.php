<?php

use App\Domains\Lien\Models\LienDocumentType;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\BufferedOutput;

uses()->group('slow');

function runLienSync(array $options = []): array
{
    // Own buffer: on the first DB touch LazilyRefreshDatabase runs migrate:fresh,
    // which would replace Artisan::output().
    $output = new BufferedOutput;
    $code = Artisan::call('lien:sync-public-rules', $options, $output);

    return [$code, $output->fetch()];
}

/** The AR prelim notice row for GCs: one of the zero-offset rows with display text. */
function arZeroOffsetRow(): object
{
    return DB::table('lien_deadline_rules')
        ->where('state', 'AR')
        ->where('document_type_id', LienDocumentType::where('slug', 'prelim_notice')->value('id'))
        ->where('claimant_type', 'gc')
        ->where('trigger_event', 'contract_date')
        ->where('effective_scope', 'both')
        ->where('calc_method', 'days_after_date')
        ->where('offset_days', 0)
        ->sole();
}

it('reports everything in sync on the seeded database', function () {
    [$code, $output] = runLienSync();

    expect($code)->toBe(0)
        ->and($output)->toContain('50 states in sync, 0 differ.')
        ->and(substr_count($output, '  ok        #'))->toBe(18)
        ->and($output)->toContain('18 rows in the JSON with display text; 0 would change, 0 not found.')
        ->and($output)->not->toContain('would set')
        ->and($output)->not->toContain('MISSING');
});

it('lists drift in a dry run, fixes it with --apply, and is idempotent', function () {
    DB::table('lien_state_rules')->where('state', 'TX')->update(['filing_location' => 'county_recorder']);

    $row = arZeroOffsetRow();
    DB::table('lien_deadline_rules')->where('id', $row->id)->update(['conditions_json' => json_encode(['anchor' => 'kept'])]);

    $ruleCount = DB::table('lien_deadline_rules')->count();
    $ruleIds = DB::table('lien_deadline_rules')->orderBy('id')->pluck('id')->all();

    // Dry run: lists both, writes nothing.
    [$code, $output] = runLienSync();

    expect($code)->toBe(0)
        ->and($output)->toContain('TX: filing_location: county_recorder -> county_clerk')
        ->and($output)->toContain('49 states in sync, 1 differ.')
        ->and($output)->toContain("would set #{$row->id} AR prelim_notice gc contract_date [both]")
        ->and($output)->toContain('0 not found')
        ->and(DB::table('lien_state_rules')->where('state', 'TX')->value('filing_location'))->toBe('county_recorder')
        ->and(json_decode(DB::table('lien_deadline_rules')->where('id', $row->id)->value('conditions_json'), true))
        ->toBe(['anchor' => 'kept']);

    Cache::put('seo.lien-state.v4.TX', 'stale page');

    // Apply: fixes both, keeps every deadline row and id, flushes the cache.
    [$code, $output] = runLienSync(['--apply' => true]);

    expect($code)->toBe(0)
        ->and($output)->toContain('Reseeded lien_state_rules from the JSON (1 states changed).')
        ->and($output)->toContain("UPDATE    #{$row->id} AR prelim_notice gc contract_date [both]")
        ->and($output)->toContain('Done: 1 states reseeded, 1 deadline rows updated, 0 not found.')
        ->and(DB::table('lien_state_rules')->count())->toBe(50)
        ->and(DB::table('lien_state_rules')->where('state', 'TX')->value('filing_location'))->toBe('county_clerk')
        ->and(DB::table('lien_deadline_rules')->count())->toBe($ruleCount)
        ->and(DB::table('lien_deadline_rules')->orderBy('id')->pluck('id')->all())->toBe($ruleIds)
        ->and(Cache::has('seo.lien-state.v4.TX'))->toBeFalse();

    $conditions = json_decode(DB::table('lien_deadline_rules')->where('id', $row->id)->value('conditions_json'), true);
    expect($conditions['anchor'])->toBe('kept')
        ->and($conditions['display'])->toStartWith('Before work begins on a residential project');

    // Second apply: nothing left to do.
    [$code, $output] = runLienSync(['--apply' => true]);

    expect($code)->toBe(0)
        ->and($output)->toContain('lien_state_rules already match the JSON; not reseeded.')
        ->and($output)->toContain('Done: 0 states reseeded, 0 deadline rows updated, 0 not found.')
        ->and(substr_count($output, '  ok        #'))->toBe(18);
});

it('exits 2 when a JSON row has no matching deadline row', function () {
    $row = arZeroOffsetRow();
    DB::table('lien_deadline_rules')->where('id', $row->id)->update(['offset_days' => 1]);

    [$code, $output] = runLienSync();

    expect($code)->toBe(2)
        ->and($output)->toContain('MISSING ROW (0 matches) AR prelim_notice gc contract_date [both]')
        ->and($output)->toContain('1 not found');
});
