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
 * pick the filing back up. The filing is in Needs Review by then (Ready to
 * File after a plain draft approval).
 *
 * Modes, picked by the event passed in: a client_response (answered the
 * request), a document_uploaded (sent back the signed document through the
 * upload box, with or without an open request), and for a draft sent for
 * review (EREG-140) a draft_approved or a draft_changes_requested.
 */
class ClientResponded extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** answered, uploaded_document, draft_approved or draft_changes_requested. */
    public string $mode;

    /** True for a document_uploaded event: the "uploaded a document" variant. */
    public bool $uploadedDocument;

    /** The draft version approved or sent back, for the two draft modes. */
    public ?int $draftVersion;

    public ?string $draftFileName;

    /** The approved draft still needs the customer's signature. */
    public bool $needsSignature;

    /** Where the filing is now (Needs Review, Ready to File). */
    public string $statusLabel;

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
     * @param  LienFilingEvent  $response  the client_response, document_uploaded, draft_approved or draft_changes_requested event
     * @param  array<string, mixed>|null  $request  the request they answered (client_request_json)
     */
    public function __construct(LienFiling $filing, LienFilingEvent $response, ?array $request)
    {
        $this->afterCommit = true;

        $business = $filing->business;
        $customer = $response->creator;
        $payload = $response->payload_json ?? [];
        $size = $payload['size'] ?? null;

        $this->mode = match ($response->event_type) {
            'document_uploaded' => 'uploaded_document',
            'draft_approved' => 'draft_approved',
            'draft_changes_requested' => 'draft_changes_requested',
            default => 'answered',
        };
        $this->uploadedDocument = $this->mode === 'uploaded_document';
        $this->draftVersion = isset($payload['version']) ? (int) $payload['version'] : null;
        $this->draftFileName = $this->mode === 'draft_approved'
            ? ($payload['file_name'] ?? null)
            : ($payload['draft_file_name'] ?? null);
        $this->needsSignature = (bool) ($payload['needs_signature'] ?? false);
        $this->statusLabel = $filing->status->label();
        $this->fileSize = is_int($size) ? File::getHumanReadableSize($size) : null;

        $this->businessName = $business?->name ?: ($business?->legal_name ?: 'Unknown business');
        $this->projectName = $filing->project?->name ?: null;
        $this->documentName = $filing->documentType?->name ?: 'filing';
        $this->customerName = $customer?->name ?: 'The customer';
        $this->customerEmail = $customer?->email;
        $this->requestText = $request['text'] ?? null;
        $this->answerText = $payload['text'] ?? null;
        // The customer's own file; an approval has none (file_name is the draft).
        $this->fileName = $this->mode === 'draft_approved'
            ? null
            : ($payload['original_name'] ?? $payload['file_name'] ?? null);
        $this->adminUrl = route('admin.liens.show', $filing);
        $this->filingPublicId = $filing->public_id;
    }

    public function envelope(): Envelope
    {
        $lead = match ($this->mode) {
            'uploaded_document' => 'Customer uploaded a document',
            'draft_approved' => 'Draft approved',
            'draft_changes_requested' => 'Changes requested',
            default => 'Customer answered',
        };

        return new Envelope(
            subject: "{$lead}: {$this->businessName} — {$this->documentName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.client-responded',
        );
    }
}
