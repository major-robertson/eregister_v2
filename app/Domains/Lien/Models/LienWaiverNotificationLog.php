<?php

namespace App\Domains\Lien\Models;

use App\Domains\Business\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LienWaiverNotificationLog extends Model
{
    use BelongsToBusiness;

    public $timestamps = false;

    protected $fillable = [
        'business_id',
        'lien_waiver_id',
        'type',
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

    public function waiver(): BelongsTo
    {
        return $this->belongsTo(LienWaiver::class, 'lien_waiver_id');
    }
}
