<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HawaiiAttorneyReferral extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $businessName,
        public string $userEmail,
        public ?string $phone = null,
    ) {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hawaii Attorney Referral Request — '.$this->firstName.' '.$this->lastName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.hawaii-attorney-referral',
        );
    }
}
