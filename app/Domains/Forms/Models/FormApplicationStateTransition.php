<?php

namespace App\Domains\Forms\Models;

use App\Domains\Forms\Enums\FormApplicationStateAdminStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log row for an admin status change on a FormApplicationState.
 * Inserted via FormApplicationState::transitionAdminStatusTo() inside a
 * single DB transaction with the denormalized fields update on the
 * parent state row.
 */
class FormApplicationStateTransition extends Model
{
    protected $fillable = [
        'form_application_state_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => FormApplicationStateAdminStatus::class,
            'to_status' => FormApplicationStateAdminStatus::class,
        ];
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(FormApplicationState::class, 'form_application_state_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
