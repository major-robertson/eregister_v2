<?php

namespace App\Mail;

use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells the internal lien ops inbox (config lien.notifications.ops_email) that
 * a customer has e-signed their demand letters, so someone can review the
 * filing and mail the letters.
 */
class DemandLetterSigned extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $businessName;

    public string $signerName;

    public ?string $projectName;

    public ?string $jobsiteAddress;

    public int $letterCount;

    public string $signedAt;

    public ?string $adminUrl;

    public ?string $filingPublicId;

    public function __construct(public SignatureRequest $request)
    {
        $this->afterCommit = true;

        $filing = $request->signable;
        $business = $filing?->business;
        $project = $filing?->project;

        $this->businessName = $business?->name ?: ($business?->legal_name ?: 'Unknown business');
        $this->signerName = $request->adopted_name
            ?: ($request->signer?->name ?: ($request->signer_name_snapshot ?: 'Unknown signer'));
        $this->projectName = $project?->name ?: null;
        $this->jobsiteAddress = $project?->jobsiteAddressLine() ?: null;
        $this->letterCount = $request->documents()->count();
        $this->signedAt = ($request->completed_at ?? now())->eastern()->format('M j, Y g:i A T');
        $this->adminUrl = $filing ? route('admin.liens.show', $filing) : null;
        $this->filingPublicId = $filing?->public_id;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Demand letter signed: {$this->businessName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.demand-letter-signed',
        );
    }
}
