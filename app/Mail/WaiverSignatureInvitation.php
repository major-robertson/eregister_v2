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
use Illuminate\Support\Facades\URL;

/**
 * Invites a signer to review and e-sign a lien waiver. The link is a temporary
 * signed URL to the e-sign landing page; guest signers verify with a one-time
 * email code (no account needed).
 */
class WaiverSignatureInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $signerName;

    public string $formTitle;

    public string $requesterName;

    public string $projectName;

    public ?string $amount;

    public string $ctaUrl;

    public bool $isGuest;

    public function __construct(public SignatureRequest $request, LienWaiver $waiver)
    {
        $this->afterCommit = true;

        $this->signerName = $request->signer_name_snapshot ?: 'there';
        $this->formTitle = $waiver->render_snapshot_json['form']['title'] ?? 'Lien Waiver';
        $this->requesterName = $waiver->project?->business?->name ?? 'A business you work with';
        $this->projectName = $waiver->project?->name ?? 'a construction project';
        $this->amount = $waiver->formattedAmount();
        $this->isGuest = $request->isGuest();

        $expiresAt = $request->expires_at ?? now()->addDays((int) config('esign.signing.invitation_link_ttl_days', 14));
        $this->ctaUrl = URL::temporarySignedRoute('esign.sign', $expiresAt, ['request' => $request->public_id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Signature requested: {$this->formTitle}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.waiver-signature-invitation',
            with: [
                'signerName' => $this->signerName,
                'formTitle' => $this->formTitle,
                'requesterName' => $this->requesterName,
                'projectName' => $this->projectName,
                'amount' => $this->amount,
                'ctaUrl' => $this->ctaUrl,
                'isGuest' => $this->isGuest,
            ],
        );
    }
}
