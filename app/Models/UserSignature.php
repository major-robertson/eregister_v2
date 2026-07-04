<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A user's adopted e-signature — either drawn on a 500x100 canvas or their
 * typed name rendered in a script font to the same canvas. Adopted once and
 * reused across the whole site (stamped on resale certificates, applied to
 * esign-signed documents). One signature per user is current; older ones are
 * kept for the audit trail.
 *
 * The PNG lives on the resale-cert disk (see config resale_cert.disk) — it's
 * private object storage either way; the config key predates the promotion
 * to a site-wide asset. The 5:1 canvas ratio is baked into PDF stamping.
 */
class UserSignature extends Model
{
    public const METHOD_DRAWN = 'drawn';

    public const METHOD_TYPED = 'typed';

    /**
     * Fonts offered for typed signatures. Keys are stored on the row; values
     * are the CSS font-family used when rendering the name to the canvas.
     */
    public const TYPED_FONTS = [
        'dancing-script' => 'Dancing Script',
        'great-vibes' => 'Great Vibes',
        'caveat' => 'Caveat',
    ];

    protected $fillable = [
        'user_id',
        'method',
        'image_path',
        'strokes_json',
        'typed_name',
        'typed_font',
        'created_ip',
        'user_agent',
        'is_current',
        'agreed_to_terms',
        'agreed_at',
    ];

    protected function casts(): array
    {
        return [
            'strokes_json' => 'array',
            'is_current' => 'boolean',
            'agreed_to_terms' => 'boolean',
            'agreed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    /**
     * Make this the user's active signature (and demote any other).
     */
    public function markAsCurrent(): void
    {
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);

        $this->update(['is_current' => true]);
    }

    /**
     * The signature PNG inlined as a data URI — works on every storage disk,
     * embeds directly into DOMPDF documents, and never exposes a public URL
     * for a private object. Null when the file is missing.
     */
    public function imageDataUri(): ?string
    {
        $disk = Storage::disk(config('resale_cert.disk'));

        if (! $this->image_path || ! $disk->exists($this->image_path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode($disk->get($this->image_path));
    }

    /**
     * The esign-domain method string recorded on signature requests.
     */
    public function esignMethod(): string
    {
        return $this->method === self::METHOD_TYPED ? 'typed_name' : 'drawn_signature';
    }
}
