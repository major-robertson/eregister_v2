<?php

namespace App\Domains\Forms\Models;

use App\Domains\Business\Models\Business;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'form_type',
        'definition_version',
        'definition_snapshot',
        'selected_states',
        'status',
        'current_phase',
        'current_step_key',
        'current_state_index',
        'core_data',
        'core_data_hash',
        'created_by_user_id',
        'paid_at',
        'submitted_at',
        'locked_at',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'selected_states' => 'array',
            'core_data' => 'array',
            'definition_snapshot' => 'array',
            'current_state_index' => 'integer',
            'definition_version' => 'integer',
            'paid_at' => 'datetime',
            'submitted_at' => 'datetime',
            'locked_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function states(): HasMany
    {
        return $this->hasMany(FormApplicationState::class);
    }

    public function stateRecord(string $stateCode): ?FormApplicationState
    {
        return $this->states()->where('state_code', $stateCode)->first();
    }

    public function currentStateRecord(): ?FormApplicationState
    {
        $stateCode = $this->currentStateCode();

        return $stateCode ? $this->stateRecord($stateCode) : null;
    }

    public function currentStateCode(): ?string
    {
        return $this->selected_states[$this->current_state_index] ?? null;
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null || $this->status === 'submitted';
    }

    public function isInCorePhase(): bool
    {
        return $this->current_phase === 'core';
    }

    public function isInStatesPhase(): bool
    {
        return $this->current_phase === 'states';
    }

    public function isInReviewPhase(): bool
    {
        return $this->current_phase === 'review';
    }

    public function stateCount(): int
    {
        return count($this->selected_states ?? []);
    }

    public function completedStateCount(): int
    {
        return $this->states()->where('status', 'complete')->count();
    }

    public function allStatesComplete(): bool
    {
        return $this->completedStateCount() === $this->stateCount();
    }

    /**
     * User-facing label for the application's state. Composes the existing
     * `status` string and `paid_at` timestamp; does not introduce any new
     * fields or enums.
     */
    public function getDisplayStatusAttribute(): string
    {
        if ($this->status === 'submitted') {
            return 'Submitted';
        }

        if ($this->paid_at !== null) {
            return 'Paid';
        }

        if ($this->status === 'draft') {
            return 'Draft';
        }

        return ucfirst((string) $this->status);
    }

    /**
     * Whether the application can still be edited by the customer. Reuses
     * the existing `isLocked()` semantics (locked_at OR submitted) so this
     * accessor never drifts from the form runner's lock rules.
     */
    public function getIsEditableAttribute(): bool
    {
        return $this->status === 'draft' && ! $this->isLocked();
    }

    /**
     * CTA label shown next to a registration on workspace dashboards.
     */
    public function getDashboardActionLabelAttribute(): string
    {
        return $this->is_editable ? 'Continue' : 'View';
    }

    /**
     * Workspace-aware URL to the application's detail/wizard page.
     * Returns null when no workspace claims this form_type, in which
     * case Blade should hide the action button rather than 500 on a
     * missing route.
     */
    public function getDashboardActionUrlAttribute(): ?string
    {
        $workspace = app(\App\Support\Workspaces\WorkspaceRegistry::class)
            ->findByFormType($this->form_type);

        return $workspace?->applicationRouteFor($this);
    }
}
