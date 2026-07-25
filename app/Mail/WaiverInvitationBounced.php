<?php

namespace App\Mail;

use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Models\LienWaiver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the waiver owner their signature invitation couldn't be delivered
 * (the counterparty's address hard-bounced), so they can void the request,
 * correct the email, and re-send. Sent to the owner's own address — which is
 * only used when it isn't the one that bounced.
 */
class WaiverInvitationBounced extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $formTitle;

    public string $projectName;

    public string $signerEmail;

    public function __construct(
        public LienWaiver $waiver,
        public SignatureRequest $request,
    ) {
        $this->afterCommit = true;

        $this->formTitle = $waiver->render_snapshot_json['form']['title'] ?? 'Lien Waiver';
        $this->projectName = $waiver->project?->name ?? 'your project';
        $this->signerEmail = (string) $request->signer_email_snapshot;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Undeliverable: {$this->formTitle} / {$this->projectName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.waiver-invitation-bounced',
            with: [
                'formTitle' => $this->formTitle,
                'projectName' => $this->projectName,
                'signerEmail' => $this->signerEmail,
                'ctaUrl' => route('lien.waivers.show', $this->waiver),
            ],
        );
    }
}
