<?php

namespace App\Domains\Lien\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LienStateRule extends Model
{
    protected $primaryKey = 'state';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'state',
        'pre_notice_required',
        'pre_notice_required_for',
        'noi_lead_time_days',
        'post_lien_notice_required',
        'post_lien_notice_days',
        'efile_allowed',
        'notarization_required',
        'wrongful_lien_penalty',
        'owner_occupied_special_rules',
        'enforcement_deadline_days',
        'enforcement_deadline_months',
        'enforcement_deadline_trigger',
        'statute_references',
        'data_source',
    ];

    protected function casts(): array
    {
        return [
            'pre_notice_required' => 'boolean',
            'post_lien_notice_required' => 'boolean',
            'efile_allowed' => 'boolean',
            'notarization_required' => 'boolean',
            'owner_occupied_special_rules' => 'boolean',
            'noi_lead_time_days' => 'integer',
            'post_lien_notice_days' => 'integer',
            'enforcement_deadline_days' => 'integer',
            'enforcement_deadline_months' => 'decimal:3',
            'statute_references' => 'array',
        ];
    }

    /**
     * Get deadline rules for this state.
     */
    public function deadlineRules(): HasMany
    {
        return $this->hasMany(LienDeadlineRule::class, 'state', 'state');
    }

    /**
     * Check if this state requires NOI before filing a lien.
     */
    public function requiresNoi(): bool
    {
        return $this->noi_lead_time_days > 0;
    }

    /**
     * Get the NOI deadline date based on the lien deadline.
     */
    public function getNoiDeadline(\Carbon\Carbon $lienDeadline): ?\Carbon\Carbon
    {
        if (! $this->requiresNoi()) {
            return null;
        }

        return $lienDeadline->copy()->subDays($this->noi_lead_time_days);
    }
}
