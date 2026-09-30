<?php

namespace App\Domains\Lien\Enums;

/**
 * The downloadable pieces of a lien filing's document package. `main` is the
 * instrument or notice itself; the rest are the service set that travels
 * with it (proof of service and cover letter per recipient, the label sheet,
 * and the mail-in filing cover sheet for clerks that want one).
 */
enum LienPackageDocument: string
{
    case Main = 'main';
    case ProofOfService = 'proof-of-service';
    case CoverLetter = 'cover-letter';
    case Labels = 'labels';
    case FilingCoverSheet = 'filing-cover-sheet';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function perRecipient(): bool
    {
        return in_array($this, [self::ProofOfService, self::CoverLetter], true);
    }
}
