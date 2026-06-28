<?php

namespace App\Mail;

use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Emails the signer a link to review and sign their documents. The link is a
 * temporary signed URL to the e-sign landing page; the signer logs into their
 * existing account (the auth middleware preserves the intended URL).
 */
class SignerInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $signerName;

    public string $title;

    public string $ctaUrl;

    public int $documentCount;

    public function __construct(public SignatureRequest $request)
    {
        $this->afterCommit = true;

        $this->signerName = $request->signer?->first_name ?: ($request->signer_name_snapshot ?: 'there');
        $this->title = config("esign.document_types.{$request->document_signing_policy_key}.title", 'your documents');
        $this->documentCount = $request->documents()->count();

        $ttlDays = (int) config('esign.signing.invitation_link_ttl_days', 14);
        $this->ctaUrl = URL::temporarySignedRoute(
            'esign.sign',
            now()->addDays($ttlDays),
            ['request' => $request->public_id],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Please sign your {$this->title} — eRegister",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.signer-invitation',
            with: [
                'signerName' => $this->signerName,
                'title' => $this->title,
                'ctaUrl' => $this->ctaUrl,
                'documentCount' => $this->documentCount,
            ],
        );
    }
}
