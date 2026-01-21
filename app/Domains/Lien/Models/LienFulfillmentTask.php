<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use App\Domains\Lien\Enums\FulfillmentStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienFulfillmentTask extends Model
{
    use BelongsToBusiness, HasFactory;

    protected $fillable = [
        'business_id',
        'filing_id',
        'status',
        'assigned_to_user_id',
        'notes',
        'due_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => FulfillmentStatus::class,
            'due_at' => 'datetime',
        ];
    }

    public function filing(): BelongsTo
    {
        return $this->belongsTo(LienFiling::class, 'filing_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        if (! $this->due_at || $this->status === FulfillmentStatus::Done) {
            return false;
        }

        return $this->due_at->isPast();
    }

    /**
     * Assign the task to a user.
     */
    public function assignTo(User $user): void
    {
        $this->update([
            'assigned_to_user_id' => $user->id,
            'status' => FulfillmentStatus::InProgress,
        ]);
    }

    /**
     * Mark the task as done.
     */
    public function markDone(): void
    {
        $this->update(['status' => FulfillmentStatus::Done]);
    }
}
