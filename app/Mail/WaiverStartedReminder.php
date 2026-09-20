<?php

namespace App\Mail;

use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Models\EmailSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * "Finish your waiver": the waiver_started sequence, for someone who signed
 * up from a lien waiver page and hasn't made a waiver yet.
 */
class WaiverStartedReminder extends Mailable implements ShouldQueue
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
        $waiver = $this->stateName() !== null ? $this->stateName().' lien waiver' : 'lien waiver';

        return new Envelope(subject: match ($this->step) {
            1 => "Your {$waiver} is about 2 minutes away",
            default => "Still need that {$waiver}?",
        });
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.waiver-started',
            with: [
                'userName' => $this->sequence->user->first_name ?? 'there',
                'step' => $this->step,
                'stateName' => $this->stateName(),
                'resumeUrl' => $this->sequence->resume_url ?: route('lien.waivers.create'),
                'preferencesUrl' => URL::signedRoute('email.preferences', ['user' => $this->sequence->user_id]),
            ],
        );
    }

    /** The state they picked on the landing page rides on the resume link. */
    private function stateName(): ?string
    {
        parse_str((string) parse_url((string) $this->sequence->resume_url, PHP_URL_QUERY), $query);

        $state = strtoupper((string) ($query['state'] ?? ''));

        return WaiverStateRegistry::STATE_NAMES[$state] ?? null;
    }
}
