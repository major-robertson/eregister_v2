<?php

namespace App\Domains\Forms\Models;

use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class FormApplicationState extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_application_id',
        'state_code',
        'status',
        'current_admin_status',
        'current_admin_status_changed_at',
        'current_step_key',
        'data',
        'data_hash',
        'completed_at',
    ];

    /**
     * Default attribute values. Mirrors the migration's column default
     * so freshly-created (non-refreshed) model instances reflect 'new'
     * without requiring a refresh().
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'current_admin_status' => 'new',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'completed_at' => 'datetime',
            'current_admin_status' => FormApplicationStateAdminStatus::class,
            'current_admin_status_changed_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(FormApplication::class, 'form_application_id');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(FormApplicationStateTransition::class)->latest();
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete' || $this->completed_at !== null;
    }

    public function markComplete(): void
    {
        $this->update([
            'status' => 'complete',
            'completed_at' => now(),
        ]);
    }

    /**
     * Move this state to a new admin status. Validates that the
     * transition is allowed by the current status's allowedTransitions(),
     * inserts an audit row, and updates the denormalized fields atomically.
     *
     * @throws \DomainException when the transition is not allowed.
     */
    public function transitionAdminStatusTo(
        FormApplicationStateAdminStatus $next,
        ?User $by = null,
        ?string $comment = null,
    ): void {
        $current = $this->current_admin_status ?? FormApplicationStateAdminStatus::New;

        if ($next === $current) {
            return; // No-op; nothing to log.
        }

        $allowed = $current->allowedTransitions();
        if (! in_array($next, $allowed, true)) {
            throw new \DomainException(
                "Cannot transition admin status from [{$current->value}] to [{$next->value}]."
            );
        }

        DB::transaction(function () use ($current, $next, $by, $comment): void {
            $this->transitions()->create([
                'from_status' => $current,
                'to_status' => $next,
                'changed_by_user_id' => $by?->id,
                'comment' => $comment,
            ]);

            $this->forceFill([
                'current_admin_status' => $next,
                'current_admin_status_changed_at' => now(),
            ])->save();
        });
    }
}
