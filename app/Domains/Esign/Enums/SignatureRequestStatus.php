<?php

namespace App\Domains\Esign\Enums;

/**
 * Lifecycle of a signing session. Slow PDF rendering + S3 uploads are NOT done
 * inside one DB transaction; this status drives an idempotent, resumable flow
 * so a partial failure can be retried without duplicating locked/signed files.
 */
enum SignatureRequestStatus: string
{
    case Pending = 'pending';                 // created, before any letters locked
    case LockingDocuments = 'locking_documents'; // rendering + storing locked PDFs
    case AwaitingSignature = 'awaiting_signature'; // locked + invited, waiting on signer
    case Signing = 'signing';                 // signer clicked Sign All, generating signed PDFs
    case Completed = 'completed';             // every letter signed + stored
    case Failed = 'failed';                   // a lock/sign step errored (resumable or voidable)
    case Voided = 'voided';                   // superseded / canceled
    case Expired = 'expired';                 // link/session expired (future scheduled job)

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::LockingDocuments => 'Preparing Documents',
            self::AwaitingSignature => 'Awaiting Signature',
            self::Signing => 'Signing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Voided => 'Voided',
            self::Expired => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending, self::LockingDocuments => 'zinc',
            self::AwaitingSignature => 'purple',
            self::Signing => 'blue',
            self::Completed => 'green',
            self::Failed => 'red',
            self::Voided => 'zinc',
            self::Expired => 'amber',
        };
    }

    /**
     * Whether this session is still live (one active session is allowed per
     * signable at a time).
     */
    public function isActive(): bool
    {
        return match ($this) {
            self::Pending, self::LockingDocuments, self::AwaitingSignature, self::Signing => true,
            default => false,
        };
    }

    /**
     * The active status values, for query scopes.
     *
     * @return list<string>
     */
    public static function activeValues(): array
    {
        return array_values(array_map(
            fn (self $s): string => $s->value,
            array_filter(self::cases(), fn (self $s): bool => $s->isActive()),
        ));
    }
}
