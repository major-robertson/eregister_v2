<?php

namespace App\Domains\Esign\Enums;

/**
 * The append-only audit-trail event types. Each is recorded via
 * AppendSignatureEvent and chained with the previous event's hash.
 */
enum SignatureEventType: string
{
    case DocumentCreated = 'document_created';
    case DocumentLocked = 'document_locked';
    case SignerInvited = 'signer_invited';
    case SignerOpened = 'signer_opened';
    // Guest signers only: control of the invited email proven via one-time code.
    case SignerIdentityVerified = 'signer_identity_verified';
    // Guest signers only: a one-time code email was issued (send-cap audit).
    case GuestCodeSent = 'guest_code_sent';
    case ConsentAccepted = 'consent_accepted';
    case DocumentViewed = 'document_viewed';
    case SignatureStarted = 'signature_started';
    case SignatureCompleted = 'signature_completed';
    case FinalPdfGenerated = 'final_pdf_generated';
    // Logged for both signer and admin downloads (metadata.actor_role).
    case DocumentDownloaded = 'document_downloaded';
    // Session-level terminal events.
    case SignatureVoided = 'signature_voided';

    public function label(): string
    {
        return match ($this) {
            self::DocumentCreated => 'Document created',
            self::DocumentLocked => 'Document locked',
            self::SignerInvited => 'Signer invited',
            self::SignerOpened => 'Signer opened',
            self::SignerIdentityVerified => 'Signer identity verified',
            self::GuestCodeSent => 'Verification code sent',
            self::ConsentAccepted => 'Consent accepted',
            self::DocumentViewed => 'Document viewed',
            self::SignatureStarted => 'Signature started',
            self::SignatureCompleted => 'Signature completed',
            self::FinalPdfGenerated => 'Final PDF generated',
            self::DocumentDownloaded => 'Document downloaded',
            self::SignatureVoided => 'Signature voided',
        };
    }
}
