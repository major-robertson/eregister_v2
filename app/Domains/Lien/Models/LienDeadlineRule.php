<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Enums\CalcMethod;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\DeadlineTrigger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LienDeadlineRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'state',
        'document_type_id',
        'claimant_type',
        'trigger_event',
        'calc_method',
        'offset_days',
        'offset_months',
        'day_of_month',
        'is_required',
        'effective_scope',
        'is_placeholder',
        'conditions_json',
        'notes',
        'data_source',
    ];

    protected function casts(): array
    {
        return [
            // claimant_type is string to allow 'any' for rules that apply to all claimant types
            'trigger_event' => DeadlineTrigger::class,
            'calc_method' => CalcMethod::class,
            'offset_days' => 'integer',
            'offset_months' => 'integer',
            'day_of_month' => 'integer',
            'is_required' => 'boolean',
            'is_placeholder' => 'boolean',
            'conditions_json' => 'array',
        ];
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(LienDocumentType::class, 'document_type_id');
    }

    public function projectDeadlines(): HasMany
    {
        return $this->hasMany(LienProjectDeadline::class, 'deadline_rule_id');
    }

    public function stateRule(): BelongsTo
    {
        return $this->belongsTo(LienStateRule::class, 'state', 'state');
    }

    /**
     * Get all rules applicable to a given state, claimant type, and scope.
     * Selection order: claimant_type exact match first, then 'any'; effective_scope exact match first, then 'both'.
     */
    public static function forStateAndClaimant(
        string $state,
        ?ClaimantType $claimantType = null,
        ?string $effectiveScope = null
    ): \Illuminate\Database\Eloquent\Collection {
        $query = static::where('state', $state)
            ->where(function ($q) use ($claimantType) {
                if ($claimantType) {
                    $q->where('claimant_type', $claimantType->value)
                        ->orWhere('claimant_type', 'any');
                } else {
                    $q->where('claimant_type', 'any');
                }
            });

        if ($effectiveScope) {
            $query->where(function ($q) use ($effectiveScope) {
                $q->where('effective_scope', $effectiveScope)
                    ->orWhere('effective_scope', 'both');
            });
        }

        return $query->with('documentType')->get();
    }
}
