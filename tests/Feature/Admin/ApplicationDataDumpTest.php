<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Admin\Support\ApplicationDataDump;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Engine\SensitiveDataProtector;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

/**
 * A paid CA + NY sales tax application saved the way the form runner
 * saves it: sensitive fields encrypted at rest, per-state person
 * questions on the shared responsible_people rows.
 */
function makeDumpApplication(): FormApplication
{
    $user = User::factory()->create(['first_name' => 'Casey', 'last_name' => 'Filer', 'email' => 'casey@example.com']);
    $business = Business::factory()->create(['name' => 'Dump Test Co']);

    $core = app(SensitiveDataProtector::class)->encryptCoreData([
        'legal_name' => 'Dump Test LLC',
        'entity_type' => 'llc_single',
        'fein' => '12-3456789',
        'bank_account_type' => '1',
        'fiscal_year_end_month' => '1',
        'matrix_sales_tax_start_date' => ['CA' => '2026-10-01', 'NY' => '2026-11-01'],
        'applies_alcohol' => ['anywhere' => '1', 'states' => ['CA']],
        'mailing_address' => ['line1' => '', 'zip' => ''],
        'responsible_people' => [[
            '_id' => 'person-1',
            'first_name' => 'Pat',
            'last_name' => 'Doe',
            'ssn' => '123-45-6789',
            'ownership_percent' => '100',
            'home_address' => [
                'line1' => '1 Main St', 'city' => 'Albany', 'state' => 'NY', 'zip' => '12207',
                'county' => 'Albany County', 'lat' => 42.65, 'place_id' => 'abc',
            ],
            'ny_profit_distribution_percentage' => '100',
        ]],
        'legacy_unmapped_key' => 'kept',
    ], app(FormRegistry::class)->getBase('sales_tax_permit'));

    $application = FormApplication::create([
        'business_id' => $business->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 3,
        'selected_states' => ['CA', 'NY'],
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => $core,
        'created_by_user_id' => $user->id,
        'paid_at' => now(),
        'submitted_at' => now(),
    ]);

    FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'CA',
        'status' => 'complete',
        'completed_at' => now(),
        'data' => [
            'ca_supplier_name' => 'Acme Supply',
            // Ciphertext under a field the definition doesn't mark sensitive.
            'ca_supplier_phone' => Crypt::encryptString('(555) 111-2222'),
            'ca_sell_new_tires' => '0',
        ],
    ]);

    FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'NY',
        'status' => 'complete',
        'completed_at' => now(),
        'data' => [
            'ny_describe_your_business' => 'We sell widgets',
            'responsible_people_extra' => ['person-1' => ['ny_profit_distribution_percentage' => '50']],
        ],
    ]);

    return $application;
}

/**
 * Flatten dump rows into "Label: value" lines (plus ITEM / BLOCK markers)
 * so assertions don't depend on nesting.
 *
 * @return list<string>
 */
function dataDumpLines(array $rows): array
{
    $lines = [];

    foreach ($rows as $row) {
        if (isset($row['items'])) {
            foreach ($row['items'] as $item) {
                $lines[] = 'ITEM '.$item['title'];
                array_push($lines, ...dataDumpBlockLines($item['blocks']));
            }
        } else {
            $lines[] = $row['label'].': '.$row['value'];
        }
    }

    return $lines;
}

/**
 * @return list<string>
 */
function dataDumpBlockLines(array $blocks): array
{
    $lines = [];

    foreach ($blocks as $block) {
        if ($block['title']) {
            $lines[] = 'BLOCK '.$block['title'];
        }
        array_push($lines, ...dataDumpLines($block['rows']));
    }

    return $lines;
}

it('labels shared answers from the definition and resolves option values', function () {
    $dump = app(ApplicationDataDump::class)->build(makeDumpApplication());
    $lines = dataDumpBlockLines($dump['core']);

    expect($lines)
        ->toContain('Legal Business Name: Dump Test LLC')
        ->toContain('Type of Entity: LLC (Single-Member)')
        ->toContain('Federal Employer Identification Number (FEIN/EIN): 12-3456789')
        ->toContain('Type of Account: Checking')
        ->toContain('Fiscal Year Ending Month: January')
        ->toContain('Date you will start collecting sales tax in California: 2026-10-01')
        ->toContain('Date you will start collecting sales tax in New York: 2026-11-01')
        ->toContain('BLOCK Other Saved Fields')
        ->toContain('Legacy Unmapped Key: kept');

    expect(collect($lines)->first(fn (string $line): bool => str_contains($line, 'alcoholic beverages')))
        ->toEndWith(': Yes — California');

    // Blank answers (an untouched mailing address) are left out.
    expect(collect($lines)->filter(fn (string $line): bool => str_starts_with($line, 'Mailing Address')))
        ->toBeEmpty();
});

it('lists every field of each responsible person, including per-state questions', function () {
    $dump = app(ApplicationDataDump::class)->build(makeDumpApplication());
    $lines = dataDumpBlockLines($dump['core']);

    expect($lines)
        ->toContain('ITEM Responsible Person 1 — Pat Doe')
        ->toContain('Social Security Number: 123-45-6789')
        ->toContain('Ownership %: 100%')
        ->toContain("Home Address: 1 Main St, Albany, NY 12207\nCounty: Albany County")
        ->toContain('BLOCK New York Requirements')
        ->toContain('Profit Distribution % (NY): 100%');
});

it('dumps the answers for every selected state', function () {
    $dump = app(ApplicationDataDump::class)->build(makeDumpApplication());

    expect(collect($dump['states'])->pluck('code')->all())->toBe(['CA', 'NY']);

    [$ca, $ny] = $dump['states'];

    expect(dataDumpLines($ca['meta']))
        ->toContain('Admin Status: New')
        ->toContain('Wizard Status: Complete');

    expect(dataDumpBlockLines($ca['blocks']))
        ->toContain('Primary Supplier Name: Acme Supply')
        ->toContain('Supplier Phone: (555) 111-2222')
        ->toContain('Will you sell new tires?: No');

    expect(dataDumpBlockLines($ny['blocks']))
        ->toContain('Describe Your Business (NY-specific narrative): We sell widgets')
        ->toContain('ITEM Pat Doe')
        ->toContain('Profit Distribution % (NY): 50%');
});

it('shows every state on the sales tax detail page in a section collapsed by default', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('tax.view');

    $caCard = makeDumpApplication()->states()->where('state_code', 'CA')->firstOrFail();

    $response = $this->actingAs($admin)
        ->get(route('admin.sales-tax.states.show', $caCard))
        ->assertSuccessful()
        ->assertSee('All Application Data')
        ->assertSee('We sell widgets') // NY's answer, shown on the CA card
        ->assertSee('Profit Distribution % (NY)')
        ->assertSee('123-45-6789')
        ->assertSee('Open NY card');

    preg_match('/<details[^>]*data-application-data-dump[^>]*>/', $response->getContent(), $tag);

    expect($tag)->not->toBeEmpty()
        ->and($tag[0])->not->toContain(' open');
});

it('lists application details and payments', function () {
    $application = makeDumpApplication();
    $payment = Payment::factory()->succeeded()->forPurchasable($application)->create([
        'amount_cents' => 39800,
        'stripe_payment_intent_id' => 'pi_test_123',
    ]);

    $lines = dataDumpLines(app(ApplicationDataDump::class)->build($application)['application']);

    expect($lines)
        ->toContain("Application ID: #{$application->id}")
        ->toContain('Business: Dump Test Co (#'.$application->business_id.')')
        ->toContain('Created By: Casey Filer · casey@example.com')
        ->toContain('Selected States: California (CA), New York (NY)')
        ->toContain('Application Status: Submitted');

    expect(collect($lines)->first(fn (string $line): bool => str_starts_with($line, "Payment #{$payment->id}: ")))
        ->toStartWith("Payment #{$payment->id}: \$398.00 USD · Succeeded · paid ")
        ->toContain('pi_test_123');
});
