<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Initiated = 'initiated';
    case RequiresPaymentMethod = 'requires_payment_method';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';       // Terminal - abandoned or flagged
    case Canceled = 'canceled';   // Terminal - explicitly canceled

    public function label(): string
    {
        return match ($this) {
            self::Initiated => 'Initiated',
            self::RequiresPaymentMethod => 'Requires Payment Method',
            self::Processing => 'Processing',
            self::Succeeded => 'Succeeded',
            self::Failed => 'Failed',
            self::Canceled => 'Canceled',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Succeeded, self::Failed, self::Canceled], true);
    }

    public function isRetryable(): bool
    {
        return in_array($this, [self::Initiated, self::RequiresPaymentMethod], true);
    }
}
