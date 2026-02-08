<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Domains\Business\Models\Business;
use App\Domains\Marketing\Models\MarketingLead;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'signup_landing_path',
        'signup_landing_url',
        'signup_referrer',
        'signup_utm_source',
        'signup_utm_medium',
        'signup_utm_campaign',
        'signup_utm_term',
        'signup_utm_content',
        'signup_ip',
        'signup_user_agent',
        'attributed_marketing_lead_id',
        'attributed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'attributed_at' => 'datetime',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name.' '.$this->last_name),
        );
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return Str::upper(
            Str::substr($this->first_name, 0, 1).Str::substr($this->last_name, 0, 1)
        );
    }

    /**
     * The marketing lead attributed to this user's signup (first-touch, permanent).
     */
    public function attributedMarketingLead(): BelongsTo
    {
        return $this->belongsTo(MarketingLead::class, 'attributed_marketing_lead_id');
    }

    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function currentBusiness(): ?Business
    {
        $id = session('current_business_id');

        return $id ? $this->businesses()->find($id) : null;
    }

    public function belongsToBusiness(Business $business): bool
    {
        return $this->businesses()->where('businesses.id', $business->id)->exists();
    }
}
