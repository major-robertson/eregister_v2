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
        'prelim_delivery_method',
        'prelim_recipients',
        'noc_shortens_deadline',
        'lien_after_noc_days',
        'noc_requires_prior_prelim',
        'noc_eliminates_rights_if_no_prelim',
        'post_lien_notice_required',
        'post_lien_notice_days',
        'post_lien_notice_recipients',
        'enforcement_calc_method',
        'enforcement_deadline_days',
        'enforcement_deadline_months',
        'enforcement_deadline_trigger',
        'owner_occupied_special_rules',
        'owner_occupied_restriction_type',
        'tenant_project_lien_allowed',
        'tenant_project_restrictions',
        'notarization_required',
        'verification_type',
        'efile_allowed',
        'filing_location',
        'wrongful_lien_penalty',
        'penalty_details',
        'lien_anchor_logic',
        'lien_anchor_alt_field',
        'gc_has_lien_rights',
        'sub_has_lien_rights',
        'subsub_has_lien_rights',
        'supplier_owner_has_lien_rights',
        'supplier_gc_has_lien_rights',
        'supplier_sub_has_lien_rights',
        'statute_references',
        'statute_url',
        'notes',
        'data_source',
    ];

    protected function casts(): array
    {
        return [
            'pre_notice_required' => 'boolean',
            'noc_shortens_deadline' => 'boolean',
            'noc_requires_prior_prelim' => 'boolean',
            'noc_eliminates_rights_if_no_prelim' => 'boolean',
            'post_lien_notice_required' => 'boolean',
            'efile_allowed' => 'boolean',
            'notarization_required' => 'boolean',
            'owner_occupied_special_rules' => 'boolean',
            'tenant_project_lien_allowed' => 'boolean',
            'gc_has_lien_rights' => 'boolean',
            'sub_has_lien_rights' => 'boolean',
            'subsub_has_lien_rights' => 'boolean',
            'supplier_owner_has_lien_rights' => 'boolean',
            'supplier_gc_has_lien_rights' => 'boolean',
            'supplier_sub_has_lien_rights' => 'boolean',
            'noi_lead_time_days' => 'integer',
            'lien_after_noc_days' => 'integer',
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
