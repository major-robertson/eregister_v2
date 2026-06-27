<?php

namespace App\Domains\Formations\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ledger row for a recurring state fee added to an LLC membership renewal
 * invoice. Unique per (stripe_subscription_id, cycle_number, component_key)
 * so the fee is added at most once across the invoice.upcoming /
 * invoice.created webhook pair and any retries.
 */
class FormationRenewalFeeItem extends Model
{
    protected $table = 'llc_renewal_fee_items';

    protected $fillable = [
        'business_id',
        'form_application_id',
        'stripe_subscription_id',
        'state',
        'cycle_number',
        'component_key',
        'amount_cents',
        'currency',
        'stripe_invoice_item_id',
        'stripe_invoice_id',
        'status',
        'charged_at',
    ];

    protected function casts(): array
    {
        return [
            'cycle_number' => 'integer',
            'amount_cents' => 'integer',
            'charged_at' => 'datetime',
        ];
    }

    public function isAdded(): bool
    {
        return $this->stripe_invoice_item_id !== null;
    }
}
