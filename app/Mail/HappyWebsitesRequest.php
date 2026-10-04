<?php

namespace App\Mail;

use App\Domains\Business\Models\Business;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * A customer pressed "Make my free mockup" on the portal's Websites page.
 * Goes to the Happy Websites inbox with the customer as Reply-To, so the team
 * there answers them directly.
 *
 * Not queued: the page tells the customer the request went through, so a
 * failed send has to surface on that click instead of sitting in the queue.
 */
class HappyWebsitesRequest extends Mailable
{
    public function __construct(public User $customer, public Business $business) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->customer->email, $this->customer->name)],
            subject: "Free mockup request: {$this->businessName()} (eRegister customer)",
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.happy-websites-request',
            with: [
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'businessName' => $this->businessName(),
                'location' => $this->location(),
            ],
        );
    }

    private function businessName(): string
    {
        return $this->business->name ?? $this->business->legal_name ?? 'No business name';
    }

    /** "Louisville, KY", or whichever part of it the business address has. */
    private function location(): ?string
    {
        $address = $this->business->business_address ?? [];

        return implode(', ', array_filter([$address['city'] ?? null, $address['state'] ?? null])) ?: null;
    }
}
