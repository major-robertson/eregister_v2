<?php

use App\Domains\ResaleCert\Models\ResaleStateRule;
use Database\Seeders\ResaleStateRuleSeeder;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

function runResaleRuleSync(array $options = []): array
{
    // Own buffer: on the first DB touch LazilyRefreshDatabase runs migrate:fresh,
    // which would replace Artisan::output().
    $output = new BufferedOutput;
    $code = Artisan::call('resale:sync-state-rules', $options, $output);

    return [$code, $output->fetch()];
}

/** Every rule row as the seeder would write it, keyed by state code. */
function resaleRulesInDatabase(): array
{
    return ResaleStateRule::query()->orderBy('state_code')->get()
        ->mapWithKeys(fn (ResaleStateRule $rule) => [$rule->state_code => [
            'state_code' => $rule->state_code,
            'state_name' => $rule->state_name,
            'accepts_mtc' => $rule->accepts_mtc,
            'accepts_sst' => $rule->accepts_sst,
            'accepts_out_of_state' => $rule->accepts_out_of_state,
            'allows_blanket' => $rule->allows_blanket,
            'default_blanket_text' => $rule->default_blanket_text,
            'expiration_months' => $rule->expiration_months === null ? null : (int) $rule->expiration_months,
            'metadata' => $rule->metadata,
        ]])
        ->all();
}

function resaleRulesInSeeder(): array
{
    return collect(ResaleStateRuleSeeder::rows())->keyBy('state_code')->sortKeys()->all();
}

it('reports nothing to do on the seeded database', function () {
    [$code, $output] = runResaleRuleSync();

    expect($code)->toBe(0)
        ->and($output)->toContain('resale_state_rules match the seeder. Nothing to do.')
        ->and(resaleRulesInDatabase())->toEqual(resaleRulesInSeeder());
});

it('lists drift in a dry run, fixes it with --apply, and is idempotent', function () {
    ResaleStateRule::where('state_code', 'WA')->update(['accepts_sst' => false, 'expiration_months' => 24]);
    ResaleStateRule::where('state_code', 'ME')->update(['metadata' => json_encode(['expiration_type' => 'end_of_year'])]);
    ResaleStateRule::where('state_code', 'VT')->delete();

    $before = resaleRulesInDatabase();

    // Dry run: lists every difference, writes nothing.
    [$code, $output] = runResaleRuleSync();

    expect($code)->toBe(0)
        ->and($output)->toMatch('/\|\s*WA\s*\|\s*accepts_sst\s*\|\s*false\s*\|\s*true\s*\|/')
        ->and($output)->toMatch('/\|\s*WA\s*\|\s*expiration_months\s*\|\s*24\s*\|\s*48\s*\|/')
        ->and($output)->toMatch('/\|\s*ME\s*\|\s*metadata\s*\|\s*\{"expiration_type":"end_of_year"\}\s*\|\s*\{"note":"MTC certificate accepted from nonresidents only."\}\s*\|/')
        ->and($output)->toMatch('/\|\s*VT\s*\|\s*\(row\)\s*\|\s*missing\s*\|\s*create\s*\|/')
        ->and($output)->toContain('Differences: 3 fields in 2 states, 1 missing row.')
        ->and($output)->toContain('Dry run: nothing written. Re-run with --apply to write.')
        ->and(resaleRulesInDatabase())->toEqual($before);

    // Apply: the database matches the seeder.
    [$code, $output] = runResaleRuleSync(['--apply' => true]);

    expect($code)->toBe(0)
        ->and($output)->toContain('Updated WA')
        ->and($output)->toContain('Updated ME')
        ->and($output)->toContain('Created VT')
        ->and($output)->toContain('Wrote 3 fields in 2 states, 1 missing row. Nothing was deleted.')
        ->and(resaleRulesInDatabase())->toEqual(resaleRulesInSeeder());

    // A second dry run finds nothing.
    [$code, $output] = runResaleRuleSync();

    expect($code)->toBe(0)
        ->and($output)->toContain('Nothing to do.')
        ->and($output)->not->toContain('Dry run');
});

it('never deletes rows the seeder does not have', function () {
    ResaleStateRule::create(['state_code' => 'ZZ', 'state_name' => 'Nowhere']);

    [$code] = runResaleRuleSync(['--apply' => true]);

    expect($code)->toBe(0)
        ->and(ResaleStateRule::where('state_code', 'ZZ')->exists())->toBeTrue();
});
