<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\VisitSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingVisit extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_link_id',
        'lead_id',
        'mailing_id',
        'source',
        'ip_address',
        'user_agent',
        'referrer',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'source' => VisitSource::class,
            'visited_at' => 'datetime',
        ];
    }

    /**
     * Get the tracking link.
     */
    public function trackingLink(): BelongsTo
    {
        return $this->belongsTo(MarketingTrackingLink::class, 'tracking_link_id');
    }

    /**
     * Get the lead.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class, 'lead_id');
    }

    /**
     * Get the mailing.
     */
    public function mailing(): BelongsTo
    {
        return $this->belongsTo(MarketingMailing::class, 'mailing_id');
    }

    /**
     * Get the events from this visit.
     */
    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'visit_id');
    }

    /**
     * Create a visit from a tracking link.
     *
     * @param  VisitSource  $source  Explicit source from route (no guessing)
     */
    public static function recordFromTrackingLink(
        MarketingTrackingLink $link,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $referrer,
        VisitSource $source
    ): self {
        return static::create([
            'tracking_link_id' => $link->id,
            'lead_id' => $link->lead_id,
            'mailing_id' => $link->mailing_id,
            'source' => $source,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referrer' => $referrer,
            'visited_at' => now(),
        ]);
    }
}
