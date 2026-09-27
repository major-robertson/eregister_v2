<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * The owner asking a customer for a Google review, sent once per person by
 * email:send-review-requests. Plain text on purpose: no layout, logo, button
 * or footer, so it reads like a note he typed himself.
 */
class ReviewRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $recipient)
    {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Hey {$this->firstName()}");
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.review-request',
            with: [
                'firstName' => $this->firstName(),
                'reviewUrl' => config('review_requests.url'),
            ],
        );
    }

    private function firstName(): string
    {
        $name = trim((string) $this->recipient->first_name);

        return $name === '' ? 'there' : Str::ucfirst($name);
    }
}
