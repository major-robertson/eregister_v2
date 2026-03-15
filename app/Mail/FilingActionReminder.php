<?php

namespace App\Mail;

use App\Domains\Lien\Enums\FilingStatus;
use App\Models\EmailSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FilingActionReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userName;

    public string $headline;

    public string $body;

    public string $ctaLabel;

    public string $ctaUrl;

    public ?string $projectName;

    public function __construct(
        public EmailSequence $sequence,
        public int $step
    ) {
        $this->afterCommit = true;

        $filing = $sequence->sequenceable;
        $triggerStatus = FilingStatus::from($sequence->trigger_status);
        $context = $triggerStatus->reminderContext();

        $this->userName = $filing->createdBy->first_name ?? 'there';
        $this->headline = $context['headline'];
        $this->body = $context['body'];
        $this->ctaLabel = $context['cta_label'];
        $this->ctaUrl = route('lien.filings.show', $filing);
        $this->projectName = $filing->project?->name;
    }

    public function envelope(): Envelope
    {
        $prefix = match (true) {
            $this->step <= 2 => 'Action required',
            $this->step <= 3 => 'Reminder',
            default => 'Urgent',
        };

        return new Envelope(
            subject: "{$prefix}: {$this->headline} — eRegister",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.filing-action-reminder',
            with: [
                'userName' => $this->userName,
                'headline' => $this->headline,
                'body' => $this->body,
                'ctaLabel' => $this->ctaLabel,
                'ctaUrl' => $this->ctaUrl,
                'projectName' => $this->projectName,
                'step' => $this->step,
            ],
        );
    }
}
