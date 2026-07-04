<?php

namespace App\Domains\ResaleCert\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use Database\Factories\ResaleCert\ResaleTaxRegistrationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A state sales-tax registration (permit number) held by a business. Exactly
 * one row per business is the home state — its tax id is the fallback for
 * states without their own registration.
 */
class ResaleTaxRegistration extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'state_code',
        'tax_id',
        'is_home_state',
    ];

    protected static function newFactory(): ResaleTaxRegistrationFactory
    {
        return ResaleTaxRegistrationFactory::new();
    }

    protected function casts(): array
    {
        return [
            // Sensitive PII — encrypted at rest, same treatment as Business.fein.
            'tax_id' => 'encrypted',
            'is_home_state' => 'boolean',
        ];
    }

    public function stateRule()
    {
        return $this->belongsTo(ResaleStateRule::class, 'state_code', 'state_code');
    }
}
