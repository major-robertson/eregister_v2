<?php

namespace App\Mail;

use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * An admin-sent nudge for a signer who hasn't signed yet. Re-issues the
 * signing link, valid to the request's (just renewed) expiry; as with the
 * invitation, the signer logs into their existing account to sign.
 */
class SignerReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $signerName;

    public string $title;

    public string $ctaUrl;

    public int $documentCount;

    public ?string $expiresOn;

    public function __construct(public SignatureRequest $request)
    {
        $this->afterCommit = true;

        $this->signerName = $request->signer?->first_name ?: ($request->signer_name_snapshot ?: 'there');
        $this->title = config("esign.document_types.{$request->document_signing_policy_key}.title", 'your documents');
        $this->documentCount = $request->documents()->count();
        $this->ctaUrl = $request->signingUrl();
        $this->expiresOn = $request->expires_at?->eastern()->format('F j, Y');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: please sign your {$this->title} — eRegister",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.signer-reminder',
            with: [
                'signerName' => $this->signerName,
                'title' => $this->title,
                'ctaUrl' => $this->ctaUrl,
                'documentCount' => $this->documentCount,
                'expiresOn' => $this->expiresOn,
            ],
        );
    }
}
