<?php

use App\Domains\Business\Models\Business;
use App\Domains\Forms\Engine\AnswerFormatter;
use App\Domains\Forms\Engine\FormRegistry;
use App\Domains\Forms\Livewire\MultiStateFormRunner;
use App\Domains\Forms\Models\FormApplication;
use App\Domains\Forms\Models\FormApplicationState;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Feature\Forms\Support\RunnerTestFactory;

/**
 * Regression for EREG-33: the answer summary printed every stored "1"/"0"
 * as Yes/No, so a January fiscal year end showed "Yes" and a checking
 * account showed "Yes". Values now render by their field definition.
 */
function renderAnswerSummaryFor(array $data, array $fields): string
{
    return view('livewire.forms.partials.answer-summary', ['data' => $data, 'fields' => $fields])->render();
}

/**
 * The text shown next to a label in a rendered answer summary.
 */
function answerSummaryValue(string $html, string $label): ?string
{
    preg_match('/<dt[^>]*>\s*'.preg_quote(e($label), '/').'\s*<\/dt>\s*<dd[^>]*>(.*?)<\/dd>/s', $html, $match);

    return isset($match[1]) ? trim(html_entity_decode(strip_tags($match[1]))) : null;
}

/**
 * @return array<string, array<string, mixed>>
 */
function salesTaxCoreFields(): array
{
    return app(AnswerFormatter::class)->fieldsIn(app(FormRegistry::class)->getBase('sales_tax_permit')['core_steps']);
}

describe('the answer summary partial', function () {
    it('formats answers by their field definition', function () {
        $html = renderAnswerSummaryFor([
            'fiscal_year_end_month' => '1',
            'bank_account_type' => '1',
            'entity_type' => 'llc_single',
            'mailing_address_same' => '1',
            'ever_issued_tax_certificate' => '0',
            'naics_code' => '541512',
            'matrix_employee_count' => ['FL' => '1'],
        ], salesTaxCoreFields());

        expect(answerSummaryValue($html, 'Fiscal Year End Month'))->toBe('January')
            ->and(answerSummaryValue($html, 'Bank Account Type'))->toBe('Checking')
            ->and(answerSummaryValue($html, 'Entity Type'))->toBe('LLC (Single-Member)')
            ->and(answerSummaryValue($html, 'Mailing Address Same'))->toBe('Yes')
            ->and(answerSummaryValue($html, 'Ever Issued Tax Certificate'))->toBe('No')
            ->and(answerSummaryValue($html, 'NAICS Code'))->toBe('541512');

        // A count of one employee is a number, not "Yes".
        expect($html)->toMatch('/Florida:<\/span>\s*<span[^>]*>1<\/span>/');
    });

    it('still guesses Yes/No for keys the definition does not know', function () {
        $html = renderAnswerSummaryFor(['legacy_flag' => '1'], salesTaxCoreFields());

        expect(answerSummaryValue($html, 'Legacy Flag'))->toBe('Yes');
    });

    it('summarises repeater rows in schema order, name first', function () {
        // Keys as the JSON column returns them: sorted by length.
        $html = renderAnswerSummaryFor(['responsible_people' => [[
            '_id' => 'p1',
            'dob' => '1990-01-01',
            'ssn' => '123-45-6789',
            'email' => 'pat@example.com',
            'phone' => '(555) 555-0100',
            'title' => 'Owner',
            'last_name' => 'Doe',
            'first_name' => 'Pat',
        ]]], salesTaxCoreFields());

        expect($html)->toContain('Pat · Doe · Owner · (555) 555-0100')
            ->not->toContain('123-45-6789');
    });

    it('puts checkbox flags after a row\'s identifying fields', function () {
        $html = renderAnswerSummaryFor(['locations' => [[
            '_id' => 'l1',
            'county' => 'Travis County',
            'address' => ['line1' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701'],
            'is_principal' => true,
        ]]], salesTaxCoreFields());

        expect($html)->toContain('1 Main St, Austin, TX, 78701 · Travis County · Yes');
    });
});

it('shows option labels on the customer review page', function () {
    $application = RunnerTestFactory::make()
        ->forStates(['CA'])
        ->inPhase('review')
        ->coreData([
            'entity_type' => 'llc_single',
            'fiscal_year_end_month' => '1',
            'bank_account_type' => '1',
        ])
        ->withStateData('CA', [
            'ca_projected_monthly_sales' => '1',
            'ca_sell_new_tires' => '0',
        ])
        ->boot();

    $html = Livewire::test(MultiStateFormRunner::class, ['application' => $application])
        ->assertOk()
        ->assertSee('Review Your Application')
        ->html();

    expect(answerSummaryValue($html, 'Fiscal Year End Month'))->toBe('January')
        ->and(answerSummaryValue($html, 'Bank Account Type'))->toBe('Checking')
        ->and(answerSummaryValue($html, 'Entity Type'))->toBe('LLC (Single-Member)')
        ->and(answerSummaryValue($html, 'Projected Monthly Sales'))->toBe('1')
        ->and(answerSummaryValue($html, 'Sell New Tires'))->toBe('No');
});

it('shows option labels on the admin sales tax card', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('tax.view');

    $application = FormApplication::create([
        'business_id' => Business::factory()->create()->id,
        'form_type' => 'sales_tax_permit',
        'definition_version' => 3,
        'selected_states' => ['CA'],
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => ['legal_name' => 'Summary Test LLC', 'fiscal_year_end_month' => '1', 'bank_account_type' => '1'],
        'created_by_user_id' => $admin->id,
        'paid_at' => now(),
        'submitted_at' => now(),
    ]);
    $card = FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'CA',
        'status' => 'complete',
        'data' => ['ca_projected_monthly_sales' => '1'],
    ]);

    $html = $this->actingAs($admin)->get(route('admin.sales-tax.states.show', $card))->assertSuccessful()->getContent();

    // Only the summary cards: the All Application Data dump below them
    // formats these values independently.
    $summaries = Str::before($html, 'All Application Data');

    expect(answerSummaryValue($summaries, 'Fiscal Year End Month'))->toBe('January')
        ->and(answerSummaryValue($summaries, 'Bank Account Type'))->toBe('Checking')
        ->and(answerSummaryValue($summaries, 'Projected Monthly Sales'))->toBe('1');
});

it('shows option labels on the admin formations card', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('llc.view');

    $application = FormApplication::create([
        'business_id' => Business::factory()->create()->id,
        'form_type' => 'llc',
        'definition_version' => 1,
        'selected_states' => ['WY'],
        'status' => 'submitted',
        'current_phase' => 'review',
        'core_data' => ['llc_name' => 'Summary Co LLC', 'management_type' => 'member_managed'],
        'created_by_user_id' => $admin->id,
        'paid_at' => now(),
        'submitted_at' => now(),
    ]);
    $card = FormApplicationState::create([
        'form_application_id' => $application->id,
        'state_code' => 'WY',
        'status' => 'complete',
        'data' => [],
    ]);

    $html = $this->actingAs($admin)->get(route('admin.formations.states.show', $card))->assertSuccessful()->getContent();

    expect(answerSummaryValue($html, 'Management Type'))->toBe('Member-Managed (members run the business)');
});
