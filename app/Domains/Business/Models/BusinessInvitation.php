<?php

namespace App\Domains\Business\Models;

use App\Models\User;
use Database\Factories\Business\BusinessInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

/**
 * A pending invitation for an email address to join a business. Rows are
 * deleted on acceptance (the business_user pivot is the membership record)
 * and on revocation, which 404s the emailed signed link via implicit binding.
 */
class BusinessInvitation extends Model
{
    use HasFactory;

    protected static function newFactory(): BusinessInvitationFactory
    {
        return BusinessInvitationFactory::new();
    }

    protected $fillable = [
        'business_id',
        'email',
        'role',
        'invited_by_user_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * The emailed accept link — a temporary signed URL whose signature
     * expires together with the invitation itself.
     */
    public function acceptUrl(): string
    {
        return URL::temporarySignedRoute(
            'invitations.accept',
            $this->expires_at,
            ['invitation' => $this->id],
        );
    }
}
