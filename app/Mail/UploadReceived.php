<?php

namespace App\Mail;

use App\Domains\Lien\Models\LienFiling;
use App\Mail\Concerns\RepliesToSupport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirms to the customer that the signed document they sent through the
 * upload box on their filing page reached us (FilingShow::uploadSignedDocument).
 * A reply goes to support.
 */
class UploadReceived extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToSupport, SerializesModels;

    public string $userName;

    public string $fileName;

    public string $documentName;

    public ?string $projectName;

    public string $filingUrl;

    public function __construct(LienFiling $filing, string $fileName, ?User $customer)
    {
        $this->afterCommit = true;

        $this->userName = $customer?->first_name ?: 'there';
        $this->fileName = $fileName;
        $this->documentName = $filing->documentType?->name ?: 'filing';
        $this->projectName = $filing->project?->name ?: null;
        $this->filingUrl = route('lien.filings.show', $filing);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->supportReplyTo(),
            subject: 'We have your document — eRegister',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.upload-received',
        );
    }
}
