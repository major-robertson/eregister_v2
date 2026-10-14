<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * The owner asking someone who reached the payment step of a sales tax
 * registration, and stopped, why they didn't pay. Sent by
 * email:unpaid-registration-check-in. Plain text with no layout or footer,
 * so it reads like a note he typed himself; replies go to the contact inbox.
 */
class UnpaidRegistrationCheckIn extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  list<string>  $stateCodes  The states on the unpaid registration.
     */
    public function __construct(public User $recipient, public array $stateCodes)
    {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        $states = $this->stateNames();
        $about = count($states) === 1 ? "your {$states[0]} registration" : 'your sales tax registration';

        return new Envelope(
            from: new Address(config('mail.from.address'), 'Major from eRegister'),
            replyTo: [new Address(config('mail.from.address'), 'Major from eRegister')],
            subject: "Quick question about {$about}",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.unpaid-registration-check-in',
            with: [
                'firstName' => $this->firstName(),
                'registration' => $this->registrationPhrase(),
            ],
        );
    }

    /** "your Texas sales tax registration", "your Texas and Florida …", "your sales tax registration for 4 states". */
    private function registrationPhrase(): string
    {
        $states = $this->stateNames();

        return match (true) {
            $states === [] => 'your sales tax registration',
            count($states) <= 2 => 'your '.implode(' and ', $states).' sales tax registration',
            default => 'your sales tax registration for '.count($states).' states',
        };
    }

    /** @return list<string> */
    private function stateNames(): array
    {
        return array_values(array_filter(array_map(
            fn (string $code) => config('states')[strtoupper($code)] ?? null,
            $this->stateCodes,
        )));
    }

    private function firstName(): string
    {
        $name = trim((string) $this->recipient->first_name);

        return $name === '' ? 'there' : Str::ucfirst($name);
    }
}
