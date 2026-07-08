<?php

namespace App\Domains\Lien\Enums;

enum WaiverStatus: string
{
    /** Details entered but no PDF generated yet. */
    case Draft = 'draft';

    /** PDF generated and stored; not yet sent anywhere. */
    case Generated = 'generated';

    /** An e-signature request is out to the signer. */
    case AwaitingSignature = 'awaiting_signature';

    /** Signed: via e-sign, or a signed copy was uploaded / marked signed. */
    case Signed = 'signed';

    /** Cancelled by the user; kept for the audit trail. */
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Generated => 'Generated',
            self::AwaitingSignature => 'Awaiting Signature',
            self::Signed => 'Signed',
            self::Voided => 'Voided',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::Generated => 'blue',
            self::AwaitingSignature => 'amber',
            self::Signed => 'green',
            self::Voided => 'red',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Draft, self::Generated, self::AwaitingSignature], true);
    }
}
