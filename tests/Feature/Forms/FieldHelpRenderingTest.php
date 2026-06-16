<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

beforeEach(fn () => View::share('errors', new ViewErrorBag));

/**
 * Render the shared text field partial with the given help string and
 * return the resulting HTML, so each test can make precise assertions
 * about how the help text was rendered.
 */
function renderTextFieldWithHelp(string $help): string
{
    // The text partial now reads `$resolvedHelp` (computed by the
    // dispatcher in field.blade.php) rather than $field['help'] so the
    // help_when conditional mechanism can override it. Standalone
    // rendering must match that contract by passing resolvedHelp.
    return view('livewire.forms.partials.fields.text', [
        'field' => ['type' => 'text', 'label' => 'Field', 'help' => $help],
        'wireModel' => 'foo',
        'label' => 'Field',
        'needsLive' => false,
        'inputType' => 'text',
        'resolvedHelp' => $help,
    ])->render();
}

describe('Field help linkification', function () {
    it('wraps http(s) URLs in clickable anchor tags', function () {
        $html = renderTextFieldWithHelp(
            'Apply at https://www.irs.gov/businesses/employer-identification-number for an EIN.'
        );

        expect($html)
            ->toContain('<a href="https://www.irs.gov/businesses/employer-identification-number"')
            ->toContain('target="_blank"')
            ->toContain('rel="noopener noreferrer"');
    });

    it('escapes the non-URL portion of help text to prevent injection', function () {
        // <strong> in the source must NOT pass through as raw HTML — it
        // should be escaped. The escape-then-linkify pipeline is what
        // makes this safe even though the partial uses {!! !!}.
        $html = renderTextFieldWithHelp('Use <strong>caution</strong> at https://example.com');

        expect($html)
            ->toContain('&lt;strong&gt;')
            ->and($html)->not->toContain('<strong>caution')
            ->and($html)->toContain('<a href="https://example.com"');
    });

    it('renders plain help text untouched when there are no URLs', function () {
        $html = renderTextFieldWithHelp('Format: 12-3456789. Whole numbers only.');

        expect($html)->toContain('Format: 12-3456789. Whole numbers only.')
            ->and($html)->not->toContain('<a href="');
    });

    it('handles multiple URLs in the same help string', function () {
        $html = renderTextFieldWithHelp('See https://a.example and https://b.example/docs');

        expect($html)->toContain('<a href="https://a.example"')
            ->and($html)->toContain('<a href="https://b.example/docs"');
    });

    it('does not link bare domains (only http/https)', function () {
        // Conservative scope: avoid false positives like sentence fragments
        // that look like domains. Authors who want a link can include the
        // scheme.
        $html = renderTextFieldWithHelp('Visit irs.gov for more info.');

        expect($html)->not->toContain('<a href="');
    });
});
