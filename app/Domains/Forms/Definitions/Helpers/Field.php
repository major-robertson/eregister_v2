<?php

/**
 * Field-definition helpers.
 *
 * Globally autoloaded via composer.json's `autoload.files`. Used inside
 * the SalesTaxPermit (and friends) definition arrays to collapse the
 * repeated `'options' => ['1' => 'Yes', '0' => 'No'], 'rules' => [...]`
 * boilerplate into a single call. Eliminates the `in:1,0` vs `in:0,1`
 * footgun and makes per-state files dramatically more scannable.
 *
 * Usage in a state file:
 *   'tx_sell_fireworks' => yesNoField('Do you sell fireworks?', 'sellFireworks'),
 *   'tx_optional_thing' => nullableYesNoField('Optional thing?', 'optionalThing'),
 */
if (! function_exists('yesNoField')) {
    /**
     * Required Yes/No radio field.
     *
     * @param  string  $label  The question text shown next to the radios.
     * @param  string  $sourceName  Optional PDF/source mapping key.
     * @param  array<string, mixed>  $extra  Override or extend the generated config (e.g. ['drives_conditional' => true]).
     * @return array<string, mixed>
     */
    function yesNoField(string $label, string $sourceName = '', array $extra = []): array
    {
        $base = [
            'type' => 'radio',
            'label' => $label,
            'options' => ['1' => 'Yes', '0' => 'No'],
            'rules' => ['required', 'in:0,1'],
        ];

        if ($sourceName !== '') {
            $base['source_name'] = $sourceName;
        }

        return array_merge($base, $extra);
    }
}

if (! function_exists('nullableYesNoField')) {
    /**
     * Nullable Yes/No radio field. Same shape as yesNoField() but the
     * rules accept null/empty so the question can be skipped.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    function nullableYesNoField(string $label, string $sourceName = '', array $extra = []): array
    {
        $base = [
            'type' => 'radio',
            'label' => $label,
            'options' => ['1' => 'Yes', '0' => 'No'],
            'rules' => ['nullable', 'in:0,1'],
        ];

        if ($sourceName !== '') {
            $base['source_name'] = $sourceName;
        }

        return array_merge($base, $extra);
    }
}
