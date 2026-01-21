<?php

namespace App\Domains\Lien\Models;

use App\Domains\Lien\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienNotificationLog extends Model
{
    use BelongsToBusiness;

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'project_deadline_id',
        'interval_days',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'interval_days' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function projectDeadline(): BelongsTo
    {
        return $this->belongsTo(LienProjectDeadline::class, 'project_deadline_id');
    }
}
