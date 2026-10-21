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
use Spatie\MediaLibrary\Support\File;

/**
 * Tells the internal lien ops inbox (config lien.notifications.ops_email)
 * that a customer answered our request on their filing page, so someone can
 * pick the filing back up. The filing is in Needs Review by then.
 *
 * Two modes, picked by the event passed in: a client_response (answered the
 * request) or a document_uploaded (sent back the signed document through
 * the upload box, with or without an open request).
 */
class ClientResponded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** True for a document_uploaded event: the "uploaded a document" variant. */
    public bool $uploadedDocument;

    public ?string $fileSize;

    public string $businessName;

    public ?string $projectName;

    public string $documentName;

    public string $customerName;

    public ?string $customerEmail;

    public ?string $requestText;

    public ?string $answerText;

    public ?string $fileName;

    public string $adminUrl;

    public string $filingPublicId;

    /**
     * @param  LienFilingEvent  $response  the client_response or document_uploaded event
     * @param  array<string, mixed>|null  $request  the request they answered (client_request_json)
     */
    public function __construct(LienFiling $filing, LienFilingEvent $response, ?array $request)
    {
        $this->afterCommit = true;

        $business = $filing->business;
        $customer = $response->creator;
        $size = $response->payload_json['size'] ?? null;

        $this->uploadedDocument = $response->event_type === 'document_uploaded';
        $this->fileSize = is_int($size) ? File::getHumanReadableSize($size) : null;

        $this->businessName = $business?->name ?: ($business?->legal_name ?: 'Unknown business');
        $this->projectName = $filing->project?->name ?: null;
        $this->documentName = $filing->documentType?->name ?: 'filing';
        $this->customerName = $customer?->name ?: 'The customer';
        $this->customerEmail = $customer?->email;
        $this->requestText = $request['text'] ?? null;
        $this->answerText = $response->payload_json['text'] ?? null;
        $this->fileName = $response->payload_json['original_name'] ?? $response->payload_json['file_name'] ?? null;
        $this->adminUrl = route('admin.liens.show', $filing);
        $this->filingPublicId = $filing->public_id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->uploadedDocument
                ? "Customer uploaded a document: {$this->businessName} — {$this->documentName}"
                : "Customer answered: {$this->businessName} — {$this->documentName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.client-responded',
        );
    }
}
