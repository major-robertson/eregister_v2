<?php

use App\Domains\Forms\Engine\FormRegistry;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

// Outside a real request, Laravel doesn't auto-share the $errors view bag.
// Flux components (and our @error directive) both depend on it.
beforeEach(fn () => View::share('errors', new ViewErrorBag));

/**
 * Verifies that:
 *   1. The Sales Tax Permit definitions carry the expected Alpine input
 *      masks on every SSN/FEIN field (so the convention is enforced).
 *   2. The shared text field partial passes the mask through to flux:input
 *      when set, and omits the attribute entirely when not.
 *
 * Rendered HTML is checked via str_contains rather than exact match so the
 * test isn't coupled to Flux's internal markup (it can change between
 * Flux versions without breaking us, as long as the mask attribute survives).
 */
function renderTextPartial(array $field, string $wireModel = 'foo', bool $needsLive = false): string
{
    return view('livewire.forms.partials.fields.text', [
        'field' => $field,
        'wireModel' => $wireModel,
        'label' => $field['label'] ?? 'Test',
        'needsLive' => $needsLive,
        'inputType' => $field['type'] ?? 'text',
        // The text partial reads $resolvedHelp (set by the dispatcher
        // for help_when support); standalone renders must supply it.
        'resolvedHelp' => $field['help'] ?? null,
    ])->render();
}

describe('Input mask definitions', function () {
    it('marks individual_ssn with the SSN mask', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['identity']['fields']['individual_ssn'];

        expect($field['mask'] ?? null)->toBe('999-99-9999');
    });

    it('marks fein with the FEIN mask', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['identity']['fields']['fein'];

        expect($field['mask'] ?? null)->toBe('99-9999999');
    });

    it('marks the responsible_people repeater ssn sub-field with the SSN mask', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $schema = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema'];

        expect($schema['ssn']['mask'] ?? null)->toBe('999-99-9999');
    });

    it('marks the core predecessor_fein with the FEIN mask', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['acquisition_and_history']['fields']['predecessor_fein'];

        expect($field['mask'] ?? null)->toBe('99-9999999');
    });

    it('marks the NJ-specific FEIN fields with the FEIN mask', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'NJ');
        $fields = collect($merged['state_steps'])->flatMap(fn ($s) => $s['fields'] ?? [])->all();

        expect($fields['nj_parent_corporation_fein']['mask'] ?? null)->toBe('99-9999999');
        expect($fields['nj_acquired_ein']['mask'] ?? null)->toBe('99-9999999');
    });

    it('marks NAICS code field with a 6-digit mask', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        expect($base['core_steps']['activity']['fields']['naics_code']['mask'] ?? null)->toBe('999999');
    });

    it('marks every phone field with a US phone mask', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $phoneMask = '(999) 999-9999';

        expect($base['core_steps']['contact_and_address']['fields']['business_phone']['mask'] ?? null)
            ->toBe($phoneMask);

        $personSchema = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema'];
        expect($personSchema['phone']['mask'] ?? null)->toBe($phoneMask);

        // Smoke-check the remaining state-only phone fields.
        $statePhoneFields = [
            'CA' => ['ca_supplier_phone', 'ca_personal_reference_phone'],
            'OH' => ['oh_company_contact_phone', 'oh_company_contact_fax'],
            'PA' => ['pa_phone_for_lottery'],
        ];
        foreach ($statePhoneFields as $stateCode => $keys) {
            $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);
            $fields = collect($merged['state_steps'])->flatMap(fn ($s) => $s['fields'] ?? [])->all();
            foreach ($keys as $key) {
                expect($fields[$key]['mask'] ?? null)
                    ->toBe($phoneMask, "{$stateCode}.{$key} should carry the phone mask");
            }
        }
    });

    it('gives every email field a placeholder hint', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');

        expect($base['core_steps']['contact_and_address']['fields']['business_email']['placeholder'] ?? null)
            ->not->toBeEmpty();

        $personSchema = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema'];
        expect($personSchema['email']['placeholder'] ?? null)->not->toBeEmpty();

        foreach (['TX', 'TN', 'OH', 'OK', 'NY'] as $stateCode) {
            $merged = app(FormRegistry::class)->get('sales_tax_permit', $stateCode);
            $fields = collect($merged['state_steps'])->flatMap(fn ($s) => $s['fields'] ?? [])->all();
            $emailKeys = collect($fields)->filter(fn ($f) => ($f['type'] ?? null) === 'email')->keys();
            foreach ($emailKeys as $key) {
                expect($fields[$key]['placeholder'] ?? null)
                    ->not->toBeEmpty("{$stateCode}.{$key} should carry an email placeholder");
            }
        }
    });

    it('locks the monthly taxable sales matrix to whole-dollar integers via cell mask + integer rule', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['core_steps']['state_dates_and_estimates']['fields']['matrix_estimated_monthly_taxable_sales'];

        expect($field['cell_mask'] ?? null)->toBe('9999999999999')
            ->and($field['cell_rules'])->toContain('integer')
            ->and($field['cell_rules'])->not->toContain('numeric');
    });
});

describe('Repeater modal sub-field rendering', function () {
    it('keeps driver_license_state defined as a select with state options', function () {
        // First half of the regression: the schema itself must declare
        // driver_license_state as a select, not a text field.
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $personSchema = $base['core_steps']['responsible_people']['fields']['responsible_people']['schema'];

        expect($personSchema['driver_license_state']['type'] ?? null)->toBe('select')
            ->and($personSchema['driver_license_state']['options'] ?? [])
            ->toBeArray()
            ->not->toBeEmpty();
    });

    it('handles select sub-fields in the repeater modal switch', function () {
        // Second half of the regression: the modal's @switch on $subType
        // must include a 'select' case. Previously only percent / checkbox
        // / email / date were handled, so any select sub-field (like
        // driver_license_state) fell through to @default and rendered as
        // a free-text input — the user could type "asdf" instead of
        // picking a state. We pin the structural shape of the modal so
        // any future refactor that drops the case fails the test.
        // The @switch was extracted into repeater-subfield.blade.php so
        // both the grouped (`schema_groups`) and flat layouts share one
        // renderer. Pin the select case + flux:select binding there.
        $partial = file_get_contents(
            resource_path('views/livewire/forms/partials/fields/repeater-subfield.blade.php')
        );

        expect($partial)->toContain("@case('select')")
            ->and($partial)->toContain('<flux:select wire:model="repeaterForm.');
    });
});

describe('Text field partial mask rendering', function () {
    it('renders an input element AND the mask attribute when masked', function () {
        $html = renderTextPartial([
            'type' => 'text',
            'label' => 'Owner SSN',
            'mask' => '999-99-9999',
        ]);

        expect($html)->toContain('<input')
            ->and($html)->toContain('mask="999-99-9999"');
    });

    it('renders the mask attribute on the live wire:model branch too', function () {
        $html = renderTextPartial(
            ['type' => 'text', 'label' => 'EIN', 'mask' => '99-9999999'],
            needsLive: true,
        );

        expect($html)->toContain('<input')
            ->and($html)->toContain('mask="99-9999999"');
    });

    it('still renders an input and omits the mask attribute when not configured', function () {
        // Regression guard: an earlier implementation used inline @if directives
        // inside the <flux:input> tag attributes, which silently broke the
        // Blade component compiler and caused the input element to disappear
        // for every text field — masked or not.
        $html = renderTextPartial([
            'type' => 'text',
            'label' => 'Legal Name',
        ]);

        expect($html)->toContain('<input')
            ->and($html)->not->toContain('mask=');
    });
});
