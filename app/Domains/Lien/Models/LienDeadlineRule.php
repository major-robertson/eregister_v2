<?php

namespace App\Domains\Lien\Models;

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
        'offset_days',
        'is_required',
        'is_placeholder',
        'conditions_json',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'claimant_type' => ClaimantType::class,
            'trigger_event' => DeadlineTrigger::class,
            'offset_days' => 'integer',
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

    /**
     * Get all rules applicable to a given state and claimant type.
     */
    public static function forStateAndClaimant(string $state, ?ClaimantType $claimantType = null): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('state', $state)
            ->where(function ($query) use ($claimantType) {
                $query->whereNull('claimant_type');
                if ($claimantType) {
                    $query->orWhere('claimant_type', $claimantType);
                }
            })
            ->with('documentType')
            ->get();
    }
}
