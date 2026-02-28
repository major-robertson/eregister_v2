<?php

namespace App\Mail;

use App\Models\EmailSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class AbandonedCheckoutReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailSequence $sequence,
        public int $step
    ) {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->step) {
            1 => 'Need help finishing your order?',
            2 => 'Your order is still waiting',
            3 => 'Last chance — complete your order',
            default => 'Complete your order — eRegister',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.abandoned-checkout',
            with: [
                'userName' => $this->sequence->user->first_name ?? 'there',
                'step' => $this->step,
                'resumeUrl' => $this->sequence->resume_url,
                'projectName' => $this->resolveProjectName(),
                'preferencesUrl' => $this->generatePreferencesUrl(),
            ],
        );
    }

    protected function resolveProjectName(): ?string
    {
        $sequenceable = $this->sequence->sequenceable;

        if (! $sequenceable) {
            return null;
        }

        if (method_exists($sequenceable, 'project') && $sequenceable->project) {
            return $sequenceable->project->name ?? null;
        }

        if (property_exists($sequenceable, 'name') || isset($sequenceable->name)) {
            return $sequenceable->name;
        }

        return null;
    }

    protected function generatePreferencesUrl(): string
    {
        return URL::signedRoute('email.preferences', [
            'user' => $this->sequence->user_id,
        ]);
    }
}
