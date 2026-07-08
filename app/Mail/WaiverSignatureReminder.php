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
 * Nudges a signer about a pending lien waiver. Re-issues a fresh signed link
 * (bounded by the request's own expiry) so a reminder never points at a dead
 * URL while the session is still live.
 */
class WaiverSignatureReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $signerName;

    public string $formTitle;

    public string $requesterName;

    public string $projectName;

    public string $ctaUrl;

    public int $daysWaiting;

    public function __construct(public SignatureRequest $request, LienWaiver $waiver, int $daysWaiting)
    {
        $this->afterCommit = true;

        $this->signerName = $request->signer_name_snapshot ?: 'there';
        $this->formTitle = $waiver->render_snapshot_json['form']['title'] ?? 'Lien Waiver';
        $this->requesterName = $waiver->project?->business?->name ?? 'A business you work with';
        $this->projectName = $waiver->project?->name ?? 'a construction project';
        $this->daysWaiting = $daysWaiting;

        $expiresAt = $request->expires_at ?? now()->addDays(2);
        $this->ctaUrl = URL::temporarySignedRoute('esign.sign', $expiresAt, ['request' => $request->public_id]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: {$this->formTitle} awaiting your signature",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.waiver-signature-reminder',
            with: [
                'signerName' => $this->signerName,
                'formTitle' => $this->formTitle,
                'requesterName' => $this->requesterName,
                'projectName' => $this->projectName,
                'ctaUrl' => $this->ctaUrl,
                'daysWaiting' => $this->daysWaiting,
            ],
        );
    }
}
