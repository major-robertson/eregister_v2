<?php

namespace App\Domains\Esign\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A user's recorded ESIGN consent for a category of records (scope) at a given
 * version. Captured the first time they sign and reused thereafter; a new scope
 * or version requires fresh consent.
 */
class EsignConsent extends Model
{
    protected $fillable = [
        'user_id',
        'consent_scope',
        'version',
        'disclosure_text',
        'disclosure_snapshot_json',
        'hardware_software_ack',
        'consented_ip',
        'consented_user_agent',
        'consented_at',
        'withdrawn_at',
    ];

    protected function casts(): array
    {
        return [
            'disclosure_snapshot_json' => 'array',
            'hardware_software_ack' => 'boolean',
            'consented_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The current (non-withdrawn) consent for a user at a given scope + version,
     * or null if they must consent again.
     */
    public static function currentFor(User $user, string $scope, string $version): ?self
    {
        return static::query()
            ->where('user_id', $user->id)
            ->where('consent_scope', $scope)
            ->where('version', $version)
            ->whereNull('withdrawn_at')
            ->latest('consented_at')
            ->first();
    }

    public function scopeForScope(Builder $query, string $scope): Builder
    {
        return $query->where('consent_scope', $scope);
    }
}
