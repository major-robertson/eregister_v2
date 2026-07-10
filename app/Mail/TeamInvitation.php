<?php

namespace App\Mail;

use App\Domains\Business\Models\BusinessInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Emails an invitee a link to join a business. Everything is snapshotted as
 * scalars in the constructor — the invitation row may be revoked (deleted)
 * before the queued job runs, and serializing the model would throw.
 */
class TeamInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $businessName;

    public string $inviterName;

    public string $role;

    public string $ctaUrl;

    public function __construct(BusinessInvitation $invitation)
    {
        $this->afterCommit = true;

        $this->businessName = $invitation->business->name ?? 'a business';
        $this->inviterName = $invitation->inviter?->name ?: 'A teammate';
        $this->role = $invitation->role;
        $this->ctaUrl = $invitation->acceptUrl();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been invited to join {$this->businessName} — eRegister",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.team-invitation',
            with: [
                'businessName' => $this->businessName,
                'inviterName' => $this->inviterName,
                'roleLabel' => $this->role === 'admin' ? 'an admin' : 'a member',
                'ctaUrl' => $this->ctaUrl,
            ],
        );
    }
}
