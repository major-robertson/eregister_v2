<?php

namespace App\Mail;

use App\Domains\Lien\Models\LienWaiver;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->isFinishingAWaiver()
                ? 'Welcome to eRegister. Here is your lien waiver link'
                : 'Welcome to eRegister',
        );
    }

    public function content(): Content
    {
        if ($this->isFinishingAWaiver()) {
            return new Content(
                markdown: 'mail.welcome-waiver',
                with: ['resumeUrl' => $this->waiverResumeUrl()],
            );
        }

        return new Content(
            markdown: 'mail.welcome',
        );
    }

    /**
     * Signed up from a lien waiver page and, by the time this sends (it is
     * queued a few minutes after signup), hasn't made the waiver yet.
     */
    private function isFinishingAWaiver(): bool
    {
        if (! $this->user->signedUpFromWaivers()) {
            return false;
        }

        return ! LienWaiver::query()
            ->withoutGlobalScope('business')
            ->withTrashed()
            ->where('created_by_user_id', $this->user->id)
            ->exists();
    }

    /** Same link as the "finish your waiver" follow-ups (WaiverNurture::onSignup). */
    private function waiverResumeUrl(): string
    {
        return EmailSequence::query()
            ->where('sequence_type', 'waiver_started')
            ->where('user_id', $this->user->id)
            ->value('resume_url') ?: route('lien.waivers.create');
    }
}
