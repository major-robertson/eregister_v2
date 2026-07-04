<?php

namespace App\Domains\ResaleCert\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use Database\Factories\ResaleCert\ResaleProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Resale-cert-specific extension of a Business: what it sells, contact info
 * printed on certificates, the MTC opt-in, and certificate expiration rules.
 * Core identity (legal name, DBA, EIN, address) lives on Business itself.
 */
class ResaleProfile extends Model
{
    use BelongsToBusiness, HasFactory;

    public const EXPIRATION_RULES = [
        'end_of_current_year' => 'End of current calendar year',
        'end_of_next_year' => 'End of next calendar year',
        '1_year_from_issue' => '1 year from effective date',
        '2_years_from_issue' => '2 years from effective date',
        '3_years_from_issue' => '3 years from effective date',
        '4_years_from_issue' => '4 years from effective date',
        '5_years_from_issue' => '5 years from effective date',
        'never' => 'Never',
    ];

    protected $fillable = [
        'business_id',
        'products_description',
        'contact_email',
        'contact_phone',
        'mtc_enabled',
        'default_expiration_rule',
        'state_expiration_rules',
        'completed_at',
    ];

    protected static function newFactory(): ResaleProfileFactory
    {
        return ResaleProfileFactory::new();
    }

    protected function casts(): array
    {
        return [
            'mtc_enabled' => 'boolean',
            'state_expiration_rules' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    public function expirationRuleForState(string $stateCode): string
    {
        return ($this->state_expiration_rules ?? [])[$stateCode]
            ?? $this->default_expiration_rule
            ?? 'end_of_current_year';
    }

    public function setStateExpirationRule(string $stateCode, ?string $rule): void
    {
        $rules = $this->state_expiration_rules ?? [];

        if ($rule === null || $rule === '') {
            unset($rules[$stateCode]);
        } else {
            $rules[$stateCode] = $rule;
        }

        $this->state_expiration_rules = $rules ?: null;
    }

    /**
     * Compute an expiration date from an issue date and a rule key, or null
     * for certificates that never expire.
     */
    public static function calculateExpirationDate(\DateTimeInterface $issueDate, string $rule): ?string
    {
        if ($rule === 'never') {
            return null;
        }

        $date = \Carbon\CarbonImmutable::instance($issueDate);

        return match ($rule) {
            'end_of_next_year' => $date->addYear()->endOfYear()->format('Y-m-d'),
            '1_year_from_issue' => $date->addYear()->format('Y-m-d'),
            '2_years_from_issue' => $date->addYears(2)->format('Y-m-d'),
            '3_years_from_issue' => $date->addYears(3)->format('Y-m-d'),
            '4_years_from_issue' => $date->addYears(4)->format('Y-m-d'),
            '5_years_from_issue' => $date->addYears(5)->format('Y-m-d'),
            default => $date->endOfYear()->format('Y-m-d'), // end_of_current_year
        };
    }

    /**
     * Format a raw phone value as (123) 456-7890 for display and snapshots.
     */
    public function formattedPhone(): string
    {
        $digits = preg_replace('/\D/', '', (string) $this->contact_phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) !== 10) {
            return (string) $this->contact_phone;
        }

        return sprintf('(%s) %s-%s', substr($digits, 0, 3), substr($digits, 3, 3), substr($digits, 6));
    }
}
