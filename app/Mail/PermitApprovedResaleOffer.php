<?php

namespace App\Mail;

use App\Domains\Forms\Models\FormApplication;
use App\Models\Price;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * "Your permit is approved, resale certificates are next": sent once when
 * the first state approves a sales tax registration and the business does
 * not subscribe to the generator yet.
 */
class PermitApprovedResaleOffer extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public FormApplication $application,
        public string $stateCode,
        public User $recipient,
    ) {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Your {$this->stateName()} sales tax registration is approved. Resale certificates are next.");
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.permit-approved-resale-offer',
            with: [
                'userName' => $this->recipient->first_name ?? 'there',
                'stateName' => $this->stateName(),
                'price' => $this->price(),
                'startUrl' => route('resale-cert.dashboard'),
                'preferencesUrl' => URL::signedRoute('email.preferences', ['user' => $this->recipient->id]),
            ],
        );
    }

    private function stateName(): string
    {
        return config("states.{$this->stateCode}", $this->stateCode);
    }

    private function price(): string
    {
        try {
            return '$'.number_format(Price::resolve(
                config('resale_cert.price_family'),
                config('resale_cert.price_key'),
                'default',
                'subscription',
            )->amount_cents / 100);
        } catch (\Throwable) {
            return '$297';
        }
    }
}
