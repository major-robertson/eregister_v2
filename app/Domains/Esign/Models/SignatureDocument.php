<?php

namespace App\Domains\Esign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One letter within a signing session. Each letter is locked and hashed
 * separately, and gets its own final signed PDF + hash. The stored PDFs are
 * immutable legal records: written exactly once and guarded against
 * overwrite/delete (no singleFile()).
 */
class SignatureDocument extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const COLLECTION_LOCKED = 'locked_document';

    public const COLLECTION_SIGNED = 'signed_document';

    protected $fillable = [
        'public_id',
        'signature_request_id',
        'document_identifier',
        'label',
        'recipient_ref',
        'document_snapshot_json',
        'sort_order',
        'locked_document_hash',
        'locked_at',
        'signed_document_hash',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'document_snapshot_json' => 'array',
            'sort_order' => 'integer',
            'locked_at' => 'datetime',
            'signed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $document): void {
            if (! $document->public_id) {
                $document->public_id = (string) Str::ulid();
            }
        });

        // Stored PDFs are immutable legal records — never delete the row (and
        // therefore never its media) through Eloquent.
        static::deleting(function (self $document): void {
            throw new RuntimeException('Signature documents are immutable and cannot be deleted.');
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function registerMediaCollections(): void
    {
        // Deliberately NOT singleFile() — immutable records must not auto-replace.
        $this->addMediaCollection(self::COLLECTION_LOCKED)
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('s3');

        $this->addMediaCollection(self::COLLECTION_SIGNED)
            ->acceptsMimeTypes(['application/pdf'])
            ->useDisk('s3');
    }

    public function signatureRequest(): BelongsTo
    {
        return $this->belongsTo(SignatureRequest::class);
    }

    public function lockedMedia(): ?Media
    {
        return $this->getFirstMedia(self::COLLECTION_LOCKED);
    }

    public function signedMedia(): ?Media
    {
        return $this->getFirstMedia(self::COLLECTION_SIGNED);
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    public function isSigned(): bool
    {
        return $this->signed_at !== null;
    }

    /**
     * Store the locked (unsigned) PDF exactly once and stamp its hash. Refuses
     * to overwrite an already-locked document.
     */
    public function storeLocked(string $bytes, string $sha256): void
    {
        if ($this->isLocked() || $this->lockedMedia() !== null) {
            throw new RuntimeException("Document {$this->document_identifier} is already locked.");
        }

        $this->addMediaFromString($bytes)
            ->usingFileName("{$this->document_identifier}-locked.pdf")
            ->usingName($this->label)
            ->withCustomProperties(['sha256' => $sha256])
            ->toMediaCollection(self::COLLECTION_LOCKED);

        $this->forceFill([
            'locked_document_hash' => $sha256,
            'locked_at' => Carbon::now(),
        ])->save();
    }

    /**
     * Store the final signed PDF exactly once and stamp its hash. Refuses to
     * overwrite an already-signed document.
     */
    public function storeSigned(string $bytes, string $sha256): void
    {
        if ($this->isSigned() || $this->signedMedia() !== null) {
            throw new RuntimeException("Document {$this->document_identifier} is already signed.");
        }

        $this->addMediaFromString($bytes)
            ->usingFileName("{$this->document_identifier}-signed.pdf")
            ->usingName($this->label)
            ->withCustomProperties(['sha256' => $sha256])
            ->toMediaCollection(self::COLLECTION_SIGNED);

        $this->forceFill([
            'signed_document_hash' => $sha256,
            'signed_at' => Carbon::now(),
        ])->save();
    }
}
