<?php

namespace App\Mail;

use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Support\PropertyLookup;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * Sent at once when a customer who chose "I need help finding it" pays for a
 * mechanics lien or lien release (EREG-136): how to find the property's
 * legal description or parcel number for their county, and a button to the
 * filing page where they enter it. We never look it up for them.
 *
 * Holds only scalars, so the queued copy needs no business-scoped lookup.
 */
class PropertyDescriptionSteps extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userName;

    public string $documentName;

    public ?string $propertyAddress;

    public string $parcelPhrase;

    public ?string $assessorUrl;

    public string $filingUrl;

    public function __construct(LienFiling $filing)
    {
        $this->afterCommit = true;
        $this->metadata(EmailLog::about($filing));

        $lookup = PropertyLookup::forFiling($filing);

        $this->userName = $filing->createdBy?->first_name ?: 'there';
        $this->documentName = Str::lower($filing->documentType?->name ?: 'filing');
        $this->propertyAddress = $filing->project?->jobsiteAddressLine() ?: null;
        $this->parcelPhrase = $lookup->parcelNumberPhrase();
        $this->assessorUrl = $lookup->assessorUrl;
        $this->filingUrl = route('lien.filings.show', $filing);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            // TODO: use App\Mail\Concerns\RepliesToSupport (EREG-131) once both lien chains merge.
            replyTo: [new Address(config('mail.support_address', 'contact@eregister.com'))],
            subject: "Action required: the legal description for your {$this->documentName} — eRegister",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.property-description-steps',
        );
    }
}
