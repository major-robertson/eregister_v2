<?php

namespace App\Domains\Marketing\Models;

use App\Domains\Marketing\Enums\DestinationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MarketingLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'public_id',
        'slug',
        'mailing_address',
        'mailing_address_2',
        'mailing_city',
        'mailing_state',
        'mailing_zip',
        'business_name',
        'contact_name',
        'email',
        'phone',
        'permit_or_external_id',
        'record_id',
        'record_date',
        'raw_category',
        'title',
        'description',
        'estimated_cost',
        'property_address',
        'property_address_2',
        'property_city',
        'property_state',
        'property_zip',
        'source_url',
        'role',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'estimated_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (MarketingLead $lead) {
            if (empty($lead->public_id)) {
                $lead->public_id = (string) Str::ulid();
            }
            if (empty($lead->slug)) {
                $lead->slug = static::generateUniqueSlug($lead->business_name ?? $lead->contact_name ?? 'lead');
            }
        });

        static::created(function (MarketingLead $lead) {
            // Create vanity tracking link (token = slug, no campaign yet)
            // This ensures canonical URL always works, even before enrollment
            MarketingTrackingLink::create([
                'token' => $lead->slug,
                'lead_id' => $lead->id,
                'campaign_id' => null,
                'mailing_id' => null,
                'destination_type' => DestinationType::LeadLanding,
                'destination' => $lead->slug,
            ]);
        });
    }

    /**
     * Generate a unique slug with suffix handling for collisions.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);

        if (empty($baseSlug)) {
            $baseSlug = 'lead';
        }

        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the display name for the lead.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->business_name ?: $this->contact_name ?: 'Unknown';
    }

    /**
     * Get the business name with common suffixes stripped.
     *
     * Removes entities like ", LLC", ", Inc.", ", L.L.C.", ", Ltd.", ", Co.", etc.
     */
    public function getCleanedBusinessNameAttribute(): ?string
    {
        if (! $this->business_name) {
            return null;
        }

        $suffixes = [
            ',?\s*L\.?L\.?C\.?',
            ',?\s*Inc\.?',
            ',?\s*Ltd\.?',
            ',?\s*Co\.?',
            ',?\s*L\.L\.C\.?',
            ',?\s*Corp\.?',
            ',?\s*Corporation',
            ',?\s*Incorporated',
            ',?\s*Limited',
            ',?\s*Company',
        ];

        $pattern = '/\s*('.implode('|', $suffixes).')\s*$/i';

        return trim(preg_replace($pattern, '', $this->business_name));
    }

    /**
     * Get the full mailing address.
     */
    public function getFullMailingAddressAttribute(): string
    {
        $parts = array_filter([
            $this->mailing_address,
            $this->mailing_address_2,
            $this->mailing_city,
            $this->mailing_state ? $this->mailing_state.' '.$this->mailing_zip : null,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get the campaigns this lead is enrolled in.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(MarketingLeadCampaign::class, 'lead_id');
    }

    /**
     * Get the visits for this lead.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(MarketingVisit::class, 'lead_id');
    }

    /**
     * Get the events for this lead.
     */
    public function events(): HasMany
    {
        return $this->hasMany(MarketingEvent::class, 'lead_id');
    }

    /**
     * Get the tracking links for this lead.
     */
    public function trackingLinks(): HasMany
    {
        return $this->hasMany(MarketingTrackingLink::class, 'lead_id');
    }
}
