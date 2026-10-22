<?php

namespace App\Mail;

use App\Domains\Esign\Actions\SendGuestSignerCode;
use App\Domains\Esign\Models\SignatureRequest;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The one-time code a guest signer uses to verify their email before signing.
 * The code itself is intentionally part of the mail only; the database keeps
 * just its hash.
 */
class GuestSignerCode extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SignatureRequest $request,
        public string $code,
    ) {
        $this->afterCommit = true;
        $this->metadata(EmailLog::about($request));
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your verification code: {$this->code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.guest-signer-code',
            with: [
                'code' => $this->code,
                'signerName' => $this->request->signer_name_snapshot,
                'documentTitle' => config("esign.document_types.{$this->request->document_signing_policy_key}.title", 'Documents'),
                'ttlMinutes' => SendGuestSignerCode::TTL_MINUTES,
            ],
        );
    }
}
