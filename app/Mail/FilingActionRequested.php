<?php

namespace App\Mail;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Mail\Concerns\RepliesToSupport;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * The request an admin writes when moving a filing to Awaiting Client /
 * E-Signature / Notary, emailed to the filing's creator the moment it is
 * saved (LienFiling::transitionTo). The filing_action_reminder sequence
 * follows up two days later, quoting the same text.
 */
class FilingActionRequested extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToSupport, SerializesModels;

    public string $filingPublicId;

    public string $userName;

    public string $senderLine;

    public string $requestText;

    public string $subjectLine;

    public string $ctaLabel;

    public string $ctaUrl;

    public ?string $projectName;

    public ?string $attachmentName;

    public ?Media $attachment;

    public function __construct(LienFiling $filing)
    {
        $this->afterCommit = true;
        $this->metadata(EmailLog::about($filing));

        $request = $filing->client_request_json;
        $status = FilingStatus::from($request['status']);
        $isReview = ($request['type'] ?? null) === 'review';
        $sender = $filing->clientRequestSender();
        $cta = self::callToAction($filing, $status);

        $this->filingPublicId = $filing->public_id;
        $this->userName = $filing->createdBy?->first_name ?: 'there';
        $this->senderLine = $sender?->first_name
            ? "{$sender->first_name} at eRegister wrote:"
            : 'The eRegister team wrote:';
        $this->requestText = $request['text'];
        $this->subjectLine = self::subjectFor($status, $filing->documentType?->name, $isReview);
        $this->ctaLabel = $cta['label'];
        $this->ctaUrl = $cta['url'];
        $this->projectName = $filing->project?->name;
        // A draft sent for review travels with the email (EREG-140).
        $this->attachment = $isReview ? $filing->reviewDraft() : $filing->clientRequestAttachment();
        $this->attachmentName = $this->attachment?->file_name;
    }

    /**
     * The subject line, also previewed in the admin status form and the
     * "Send for review" dialog ($review: a draft sent for review).
     */
    public static function subjectFor(FilingStatus $status, ?string $documentName, bool $review = false): string
    {
        $documentName = $documentName ?: 'filing';

        $lead = match (true) {
            $review => 'Please review the draft of your',
            $status === FilingStatus::AwaitingEsign => 'Please sign your',
            $status === FilingStatus::AwaitingNotary => 'Please notarize your',
            default => 'We need information for your',
        };

        return "{$lead} {$documentName} — eRegister";
    }

    /**
     * The one button in the request email and in reminders that quote it:
     * the signing page when an e-signature is waiting, else the filing page,
     * where the customer can answer (or review the draft we sent).
     *
     * @return array{label: string, url: string}
     */
    public static function callToAction(LienFiling $filing, FilingStatus $status): array
    {
        $signingUrl = $status === FilingStatus::AwaitingEsign
            ? FilingActionReminder::signingUrlFor($filing)
            : null;

        if ($signingUrl !== null) {
            return ['label' => 'Sign now', 'url' => $signingUrl];
        }

        return ($filing->client_request_json['type'] ?? null) === 'review'
            ? ['label' => 'Review the draft', 'url' => route('lien.filings.show', $filing)]
            : ['label' => 'Answer here', 'url' => route('lien.filings.show', $filing)];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: $this->supportReplyTo(),
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.filing-action-requested',
        );
    }

    /**
     * @return list<Media>
     */
    public function attachments(): array
    {
        return $this->attachment ? [$this->attachment] : [];
    }
}
