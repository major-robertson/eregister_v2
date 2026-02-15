<?php

namespace App\Domains\Business\Models;

use App\Domains\Forms\Models\FormApplication;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Database\Factories\Business\BusinessFactory;
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

    protected static function newFactory(): BusinessFactory
    {
        return BusinessFactory::new();
    }

    protected $fillable = [
        'name',
        'legal_name',
        'dba_name',
        'entity_type',
        'business_address',
        'mailing_address',
        'phone',
        'state_of_incorporation',
        'contractor_license_number',
        'responsible_people',
        'onboarding_completed_at',
        'lien_onboarding_completed_at',
        'timezone',
    ];

    protected function casts(): array
    {
        return [
            'business_address' => 'array',
            'mailing_address' => 'array',
            'responsible_people' => 'array',
            'onboarding_completed_at' => 'datetime',
            'lien_onboarding_completed_at' => 'datetime',
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

    public function lienProjects(): HasMany
    {
        return $this->hasMany(LienProject::class);
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

    public function isLienOnboardingComplete(): bool
    {
        return $this->lien_onboarding_completed_at !== null;
    }

    public function completeLienOnboarding(): void
    {
        $this->update(['lien_onboarding_completed_at' => now()]);
    }

    /**
     * Get the responsible person data for a specific user.
     *
     * @return array{user_id: int, name: string, title: string, can_sign_liens: bool}|null
     */
    public function getResponsiblePersonForUser(int $userId): ?array
    {
        $people = $this->responsible_people ?? [];

        foreach ($people as $person) {
            if (($person['user_id'] ?? null) === $userId) {
                return $person;
            }
        }

        return null;
    }

    /**
     * Update or add a responsible person entry for a user.
     */
    public function setResponsiblePersonForUser(int $userId, string $name, string $title, bool $canSignLiens = true): void
    {
        $people = $this->responsible_people ?? [];
        $found = false;

        foreach ($people as $index => $person) {
            if (($person['user_id'] ?? null) === $userId) {
                $people[$index] = [
                    'user_id' => $userId,
                    'name' => $name,
                    'title' => $title,
                    'can_sign_liens' => $canSignLiens,
                ];
                $found = true;
                break;
            }
        }

        if (! $found) {
            $people[] = [
                'user_id' => $userId,
                'name' => $name,
                'title' => $title,
                'can_sign_liens' => $canSignLiens,
            ];
        }

        $this->update(['responsible_people' => $people]);
    }

    /**
     * Get the name that should be synced to Stripe.
     */
    public function stripeName(): ?string
    {
        return $this->name ?? $this->legal_name;
    }

    /**
     * Get the email address that should be synced to Stripe.
     */
    public function stripeEmail(): ?string
    {
        return $this->users()->first()?->email;
    }

    /**
     * Get the full business address as a single line.
     */
    public function businessAddressLine(): string
    {
        $address = $this->business_address ?? [];

        $parts = array_filter([
            $address['line1'] ?? null,
            $address['line2'] ?? null,
            $address['city'] ?? null,
            $address['state'] ?? null,
            $address['zip'] ?? null,
        ]);

        return implode(', ', $parts);
    }
}
