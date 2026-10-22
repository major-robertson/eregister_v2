<?php

namespace App\Mail;

use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentPayload;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Enums\RecordingMethod;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
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
 * Tells the customer their full-service filing reached a milestone:
 * Submitted for Recording (how, where, how long it usually takes), Recorded
 * (the recording reference and the recorded copy), Mailed to Parties (who,
 * the tracking number, the proof of service) or Complete (everything
 * attached). Sent to the filing's creator from LienFiling::transitionTo the
 * moment the status changes; nothing for any other status. A reply goes to
 * support.
 */
class FilingMilestone extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToSupport, SerializesModels;

    /** The statuses that send this email. */
    public const STATUSES = [
        FilingStatus::SubmittedForRecording,
        FilingStatus::Recorded,
        FilingStatus::Mailed,
        FilingStatus::Complete,
    ];

    /**
     * Attachments stop here. Postmark takes 10 MB per message, and the
     * encoding makes a file about a third bigger; a file that does not fit is
     * left on the filing page and the email says so.
     */
    public const MAX_ATTACHMENT_BYTES = 7 * 1024 * 1024;

    /** The FilingStatus value this email is about. */
    public string $status;

    public string $userName;

    public string $documentName;

    /** "Los Angeles County", or null when the filing has no county. */
    public ?string $countyName;

    public ?string $projectName;

    public string $filingUrl;

    public string $subjectLine;

    /** Submitted for Recording: how and where it went, in words. */
    public ?string $recordingSentence = null;

    /** Submitted for Recording: "1 to 5 business days", or null. */
    public ?string $turnaround = null;

    /** Recorded: the instrument number or book and page. */
    public ?string $recordingReference = null;

    /**
     * Mailed: who we mailed it to.
     *
     * @var list<array{name: string, address: ?string, delivery: ?string, tracking: ?string, tracking_url: ?string}>
     */
    public array $recipients = [];

    public ?string $trackingNumber = null;

    public ?string $trackingUrl = null;

    /** Whether the filing has a recorded copy / proof of service at all. */
    public bool $hasRecordedCopy = false;

    public bool $hasProofOfService = false;

    /** The files attached (null when absent or too large to email). */
    public ?Media $recordedCopy = null;

    public ?Media $proofOfService = null;

    public function __construct(LienFiling $filing, FilingStatus $status)
    {
        $this->afterCommit = true;
        $this->metadata(EmailLog::about($filing));

        $this->status = $status->value;
        $this->userName = $filing->createdBy?->first_name ?: 'there';
        $this->documentName = $filing->documentType?->name ?: 'filing';
        $this->countyName = self::countyName($filing);
        $this->projectName = $filing->project?->name ?: null;
        $this->filingUrl = route('lien.filings.show', $filing);
        $this->subjectLine = self::subjectFor($status, $this->documentName, $this->countyName);

        if ($status === FilingStatus::SubmittedForRecording) {
            $this->recordingSentence = self::recordingSentence($filing->recording_method, $filing->recording_provider, $this->documentName, $this->countyName);
            $this->turnaround = self::turnaroundFor($filing, $filing->recording_method, $filing->recording_provider);
        }

        if ($status === FilingStatus::Recorded) {
            $this->recordingReference = $filing->recording_reference ?: null;
        }

        if ($status === FilingStatus::Mailed) {
            $this->recipients = $filing->recipients()
                ->withoutGlobalScope('business')
                ->orderBy('id')
                ->get()
                ->map(fn (LienFilingRecipient $recipient) => [
                    'name' => $recipient->snapshotName(),
                    'address' => $recipient->snapshotAddressLine() ?: null,
                    'delivery' => LienDocumentPayload::deliveryLabel($recipient->delivery_method),
                    'tracking' => $recipient->tracking_number ?: null,
                    'tracking_url' => $recipient->tracking_number ? self::trackingUrl($recipient->tracking_number) : null,
                ])
                ->all();
            $this->trackingNumber = $filing->mailing_tracking_number ?: null;
            $this->trackingUrl = $this->trackingNumber ? self::trackingUrl($this->trackingNumber) : null;
        }

        $budget = self::MAX_ATTACHMENT_BYTES;

        if (in_array($status, [FilingStatus::Recorded, FilingStatus::Complete], true)) {
            $copy = $filing->latestMilestoneFile('recorded');
            $this->hasRecordedCopy = $copy !== null;

            if ($copy !== null && (int) $copy->size <= $budget) {
                $this->recordedCopy = $copy;
                $budget -= (int) $copy->size;
            }
        }

        if (in_array($status, [FilingStatus::Mailed, FilingStatus::Complete], true)) {
            $proof = $filing->latestMilestoneFile('proof_of_service');
            $this->hasProofOfService = $proof !== null;

            if ($proof !== null && (int) $proof->size <= $budget) {
                $this->proofOfService = $proof;
            }
        }
    }

    /**
     * Whether moving this filing to $status emails the customer: one of the
     * four milestones, on a full-service filing. A self-serve customer files
     * and mails the document themselves, so there is nothing to tell them.
     */
    public static function sendsFor(LienFiling $filing, FilingStatus $status): bool
    {
        return in_array($status, self::STATUSES, true) && $filing->isFullService();
    }

    /**
     * The subject line, also previewed on the admin status form.
     */
    public static function subjectFor(FilingStatus $status, ?string $documentName, ?string $countyName): string
    {
        $documentName = $documentName ?: 'filing';

        $what = match ($status) {
            FilingStatus::SubmittedForRecording => $countyName ? "was submitted to {$countyName}" : 'was submitted for recording',
            FilingStatus::Recorded => 'was recorded',
            FilingStatus::Mailed => 'was mailed',
            default => 'is complete',
        };

        return "Your {$documentName} {$what} — eRegister";
    }

    /**
     * The filing's county as people say it ("Los Angeles County", "Orleans
     * Parish"), or null when it has none.
     */
    public static function countyName(LienFiling $filing): ?string
    {
        $project = $filing->project;
        $county = trim((string) preg_replace('/\s+/', ' ', (string) ($filing->jurisdiction_county
            ?: $project?->jobsite_county
            ?: $project?->jobsite_county_google)));

        if ($county === '') {
            return null;
        }

        // Already says what it is: "Los Angeles County", "St. Louis City".
        if (preg_match('/\b(county|parish|borough|city)$/i', $county)) {
            return $county;
        }

        $state = strtoupper((string) ($filing->jurisdiction_state ?: $project?->jobsite_state));

        return $county.($state === 'LA' ? ' Parish' : ' County');
    }

    /**
     * How the document went to the county, in words: e-recording, mailed to
     * the county, or taken in person (a provider such as "In person").
     */
    public static function recordingSentence(?RecordingMethod $method, ?string $provider, string $documentName, ?string $countyName): string
    {
        $county = $countyName ?: 'the county';

        return match (true) {
            self::isInPerson($provider) => "We took your {$documentName} to {$county} in person for recording.",
            $method === RecordingMethod::Erecord => "We submitted your {$documentName} to {$county} for recording by e-recording.",
            $method === RecordingMethod::Mail => "We mailed your {$documentName} to {$county} for recording.",
            default => "We submitted your {$documentName} to {$county} for recording.",
        };
    }

    /**
     * How long recording usually takes ("1 to 5 business days"): the county
     * data file's recording.turnaround when it has one, else
     * config('lien.recording_turnaround') for the method. Null when neither
     * has words for it.
     */
    public static function turnaroundFor(LienFiling $filing, ?RecordingMethod $method, ?string $provider): ?string
    {
        $key = self::isInPerson($provider) ? 'in_person' : $method?->value;

        if ($key === null) {
            return null;
        }

        $state = strtoupper((string) ($filing->jurisdiction_state ?: $filing->project?->jobsite_state));
        $county = $state !== '' ? LienDocumentRegistry::county($state, LienCountyKey::forFiling($filing)) : null;
        $override = $county['recording']['turnaround'] ?? null;

        $words = match (true) {
            is_string($override) => $override,
            is_array($override) => $override[$key] ?? null,
            default => null,
        } ?: config("lien.recording_turnaround.{$key}");

        return is_string($words) && trim($words) !== '' ? trim($words) : null;
    }

    /**
     * The USPS tracking page for a tracking number.
     */
    public static function trackingUrl(string $number): string
    {
        return 'https://tools.usps.com/go/TrackConfirmAction?tLabels='.rawurlencode((string) preg_replace('/\s+/', '', $number));
    }

    /**
     * Whether the recording provider says the document was taken to the
     * county in person ("In person", "Walk-in", "Hand delivered").
     */
    public static function isInPerson(?string $provider): bool
    {
        return $provider !== null && preg_match('/\b(in[\s-]?person|walk[\s-]?in|hand[\s-]?deliver(ed|y)?)\b/i', $provider) === 1;
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
            markdown: 'mail.filing-milestone',
        );
    }

    /**
     * @return list<Media>
     */
    public function attachments(): array
    {
        return array_values(array_filter([$this->recordedCopy, $this->proofOfService]));
    }
}
