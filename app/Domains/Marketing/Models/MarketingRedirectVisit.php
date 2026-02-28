<?php

namespace App\Domains\Marketing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingRedirectVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'marketing_redirect_id',
        'ip_address',
        'user_agent',
        'referrer',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function redirect(): BelongsTo
    {
        return $this->belongsTo(MarketingRedirect::class, 'marketing_redirect_id');
    }
}
