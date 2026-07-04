<?php

namespace App\Domains\ResaleCert\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-state resale certificate rules: which uniform forms (MTC/SST) a state
 * accepts, whether out-of-state tax ids are accepted, blanket-certificate
 * text, and default expiration. 'MTC' and 'SST' exist as pseudo-state rows.
 */
class ResaleStateRule extends Model
{
    protected $fillable = [
        'state_code',
        'state_name',
        'accepts_mtc',
        'accepts_sst',
        'accepts_out_of_state',
        'allows_blanket',
        'default_blanket_text',
        'expiration_months',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'accepts_mtc' => 'boolean',
            'accepts_sst' => 'boolean',
            'accepts_out_of_state' => 'boolean',
            'allows_blanket' => 'boolean',
            'metadata' => 'array',
        ];
    }

    /**
     * Real states only — excludes the MTC/SST uniform pseudo-states.
     */
    public function scopeStatesOnly(Builder $query): Builder
    {
        return $query->whereNotIn('state_code', ['MTC', 'SST']);
    }

    /**
     * Display name, with the uniform certificates shortened to their acronym.
     */
    public function getDisplayName(): string
    {
        return match ($this->state_code) {
            'MTC', 'SST' => $this->state_code,
            default => $this->state_name,
        };
    }
}
