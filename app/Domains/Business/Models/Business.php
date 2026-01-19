<?php

namespace App\Domains\Business\Models;

use App\Domains\Forms\Models\FormApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Business extends Model implements HasMedia
{
    use Billable, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'legal_name',
        'dba_name',
        'entity_type',
        'business_address',
        'mailing_address',
        'responsible_people',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'business_address' => 'array',
            'mailing_address' => 'array',
            'responsible_people' => 'array',
            'onboarding_completed_at' => 'datetime',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(BusinessEntitlement::class);
    }

    public function formApplications(): HasMany
    {
        return $this->hasMany(FormApplication::class);
    }

    /**
     * Alias for formApplications - used by route model binding with {application} parameter.
     */
    public function applications(): HasMany
    {
        return $this->formApplications();
    }

    public function hasAccessTo(string $code): bool
    {
        return $this->subscribed('default')
            || $this->entitlements()
                ->where('code', $code)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->exists();
    }

    public function isOnboardingComplete(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    public function completeOnboarding(): void
    {
        $this->update(['onboarding_completed_at' => now()]);
    }
}
