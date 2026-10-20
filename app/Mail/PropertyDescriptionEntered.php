<?php

namespace App\Mail;

use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the internal lien ops inbox (config lien.notifications.ops_email)
 * that a customer entered the property's legal description or parcel number
 * on their filing page (EREG-136), so someone can pick the filing back up.
 * What they entered is quoted as written; staff check it against any upload.
 */
class PropertyDescriptionEntered extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $businessName;

    public ?string $projectName;

    public string $documentName;

    public string $customerName;

    public ?string $customerEmail;

    public ?string $legalDescription;

    public ?string $apn;

    public ?string $fileName;

    public string $statusLabel;

    public string $adminUrl;

    public string $filingPublicId;

    public function __construct(LienFiling $filing, LienFilingEvent $entry)
    {
        $this->afterCommit = true;

        $business = $filing->business;
        $customer = $entry->creator;

        $this->businessName = $business?->name ?: ($business?->legal_name ?: 'Unknown business');
        $this->projectName = $filing->project?->name ?: null;
        $this->documentName = $filing->documentType?->name ?: 'filing';
        $this->customerName = $customer?->name ?: 'The customer';
        $this->customerEmail = $customer?->email;
        $this->legalDescription = $entry->payload_json['legal_description'] ?? null;
        $this->apn = $entry->payload_json['apn'] ?? null;
        $this->fileName = $entry->payload_json['file_name'] ?? null;
        $this->statusLabel = $filing->status->label();
        $this->adminUrl = route('admin.liens.show', $filing);
        $this->filingPublicId = $filing->public_id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Customer entered the property: {$this->businessName} — {$this->documentName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.property-description-entered',
        );
    }
}
