<?php

namespace App\Mail;

use App\Domains\Forms\Models\FormApplication;
use App\Models\EmailSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "Finish the questions": the registration_unfinished sequence, for a
 * customer who paid for a sales tax registration and has not sent the
 * questions in. Day 1, day 4 and day 11 after payment.
 */
class RegistrationUnfinishedReminder extends Mailable implements ShouldQueue
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
        return new Envelope(subject: match ($this->step) {
            1 => 'Your sales tax registration is waiting for your answers',
            2 => 'About 10 minutes: finish your sales tax registration',
            default => 'We cannot file your sales tax registration until you finish',
        });
    }

    public function content(): Content
    {
        $application = $this->application();

        return new Content(
            markdown: 'mail.registration-unfinished',
            with: [
                'userName' => $this->sequence->user->first_name ?? 'there',
                'step' => $this->step,
                'stateNames' => $this->stateNames($application),
                'rush' => $application?->isRush() ?? false,
                'resumeUrl' => $this->sequence->resume_url
                    ?: ($application ? route('sales-tax.registrations.show', $application) : route('sales-tax.dashboard')),
            ],
        );
    }

    private function application(): ?FormApplication
    {
        $sequenceable = $this->sequence->sequenceable;

        return $sequenceable instanceof FormApplication ? $sequenceable : null;
    }

    private function stateNames(?FormApplication $application): string
    {
        $names = collect($application?->selected_states ?? [])
            ->map(fn (string $code) => config("states.{$code}", $code));

        return match ($names->count()) {
            0 => 'sales tax',
            1 => $names->first(),
            2 => $names->join(' and '),
            default => $names->slice(0, -1)->join(', ').' and '.$names->last(),
        };
    }
}
