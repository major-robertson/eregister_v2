<?php

namespace App\Mail;

use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Models\LienWaiver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * Sent to both parties when a lien waiver is signed. The signed PDF (with its
 * Certificate of Completion) is attached so each side holds their own copy.
 */
class WaiverCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $formTitle;

    public string $projectName;

    public string $signerName;

    public ?string $amount;

    public function __construct(
        public LienWaiver $waiver,
        public SignatureRequest $request,
    ) {
        $this->afterCommit = true;

        $this->formTitle = $waiver->render_snapshot_json['form']['title'] ?? 'Lien Waiver';
        $this->projectName = $waiver->project?->name ?? 'your project';
        $this->signerName = $request->adopted_name ?: ($request->signer_name_snapshot ?: 'The signer');
        $this->amount = $waiver->formattedAmount();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Signed: {$this->formTitle} / {$this->projectName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.waiver-completed',
            with: [
                'formTitle' => $this->formTitle,
                'projectName' => $this->projectName,
                'signerName' => $this->signerName,
                'amount' => $this->amount,
                'signedAt' => $this->request->completed_at?->eastern()->format('F j, Y g:i A').' ET',
            ],
        );
    }

    /**
     * @return list<Attachment>
     */
    public function attachments(): array
    {
        $document = $this->request->documents()->first();
        $media = $document?->signedMedia();

        if ($media === null) {
            return [];
        }

        return [
            Attachment::fromData(
                fn () => Storage::disk($media->disk)->get($media->getPathRelativeToRoot()),
                'signed-'.str_replace('_', '-', (string) $this->waiver->kind?->value).'-waiver.pdf',
            )->withMime('application/pdf'),
        ];
    }
}
