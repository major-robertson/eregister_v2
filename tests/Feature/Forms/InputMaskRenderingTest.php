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

    it('marks previous_owner_fein with the FEIN mask', function () {
        $base = app(FormRegistry::class)->getBase('sales_tax_permit');
        $field = $base['state_steps']['state_details']['fields']['previous_owner_fein'];

        expect($field['mask'] ?? null)->toBe('99-9999999');
    });

    it('marks the NJ-specific FEIN fields with the FEIN mask', function () {
        $merged = app(FormRegistry::class)->get('sales_tax_permit', 'NJ');
        $fields = $merged['state_steps']['state_details']['fields'];

        expect($fields['nj_parent_corporation_fein']['mask'] ?? null)->toBe('99-9999999');
        expect($fields['nj_acquired_ein']['mask'] ?? null)->toBe('99-9999999');
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
