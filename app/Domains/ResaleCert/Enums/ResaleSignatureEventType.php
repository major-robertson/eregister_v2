<?php

namespace App\Domains\ResaleCert\Enums;

enum ResaleSignatureEventType: string
{
    case ConsentAccepted = 'consent_accepted';
    case SignatureAdopted = 'signature_adopted';
    case CertificateGenerated = 'certificate_generated';
    case CertificateDownloaded = 'certificate_downloaded';
    case CertificateDeleted = 'certificate_deleted';

    public function label(): string
    {
        return match ($this) {
            self::ConsentAccepted => 'ESIGN consent accepted',
            self::SignatureAdopted => 'Signature adopted',
            self::CertificateGenerated => 'Certificate generated & signed',
            self::CertificateDownloaded => 'Certificate downloaded',
            self::CertificateDeleted => 'Certificate deleted',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ConsentAccepted => 'shield-check',
            self::SignatureAdopted => 'pencil',
            self::CertificateGenerated => 'document-check',
            self::CertificateDownloaded => 'arrow-down-tray',
            self::CertificateDeleted => 'trash',
        };
    }
}
