<?php

namespace App\Domains\Forms\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Type-safe entry point for sales tax permit registrations. Sits on the
 * same `form_applications` table as the base FormApplication and applies
 * a global scope so queries through this class only see sales-tax rows.
 *
 * Note: the morph map keeps `form_application` -> FormApplication::class
 * (one alias -> one class). This child overrides getMorphClass() so any
 * morph_type column written from a SalesTaxRegistration instance still
 * uses the base alias and stays consistent with existing rows.
 */
class SalesTaxRegistration extends FormApplication
{
    public const FORM_TYPE = 'sales_tax_permit';

    // Explicit table name so Eloquent doesn't infer 'sales_tax_registrations'
    // from the class name. Child models share the base `form_applications`
    // table; the global scope below filters by form_type.
    protected $table = 'form_applications';

    /** @var array<string, mixed> */
    protected $attributes = [
        'form_type' => self::FORM_TYPE,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('sales_tax_permit', function (Builder $query) {
            $query->where('form_type', self::FORM_TYPE);
        });

        static::creating(function (FormApplication $application): void {
            if (empty($application->form_type)) {
                $application->form_type = self::FORM_TYPE;
            }
        });
    }

    public function getMorphClass(): string
    {
        return 'form_application';
    }
}
