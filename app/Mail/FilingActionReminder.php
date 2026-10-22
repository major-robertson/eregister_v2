<?php

namespace App\Mail;

use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Mail\Concerns\RepliesToSupport;
use App\Models\EmailLog;
use App\Models\EmailSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class FilingActionReminder extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToSupport, SerializesModels;

    public string $userName;

    public string $headline;

    public string $body;

    public string $ctaLabel;

    public string $ctaUrl;

    public ?string $projectName;

    /**
     * The open request's text (FilingActionRequested), quoted in place of the
     * generic status copy. Null for filings with no stored request.
     */
    public ?string $requestText = null;

    public function __construct(
        public EmailSequence $sequence,
        public int $step
    ) {
        $this->afterCommit = true;
        $this->metadata(EmailLog::about($sequence));

        $filing = $sequence->sequenceable;
        $triggerStatus = FilingStatus::from($sequence->trigger_status);
        $context = $this->reminderContext($filing, $triggerStatus);

        $this->userName = $filing->createdBy->first_name ?? 'there';
        $this->headline = $context['headline'];
        $this->body = $context['body'];
        $this->ctaLabel = $context['cta_label'];
        $this->ctaUrl = $this->resolveCtaUrl($filing, $triggerStatus);
        $this->projectName = $filing->project?->name;

        // Quote what we actually asked for, with the request email's button.
        // The day-13 "link expires" warning keeps its own copy.
        $request = $filing->openClientRequest();

        if ($request !== null && $context === $triggerStatus->reminderContext()) {
            $cta = FilingActionRequested::callToAction($filing, $triggerStatus);

            $this->body = 'Your filing is on hold until we hear back from you. Here is what we asked for:';
            $this->requestText = $request['text'];
            $this->ctaLabel = $cta['label'];
            $this->ctaUrl = $cta['url'];
        }
    }

    /**
     * The status copy, except for the e-sign reminder sent in the signing link's
     * last day (day 13 of 14), which warns that the link is about to expire.
     * Judged from the link itself (with slack for scheduler drift), so a renewed
     * link isn't mislabeled.
     *
     * @return array{headline: string, body: string, cta_label: string}
     */
    private function reminderContext($filing, FilingStatus $status): array
    {
        $expiresAt = $status === FilingStatus::AwaitingEsign
            ? $filing->activeSignatureRequest()?->expires_at
            : null;

        if ($expiresAt === null || $expiresAt->gt(now()->addHours(36))) {
            return $status->reminderContext();
        }

        return [
            'headline' => 'Your signing link expires in 24 hours',
            'body' => 'Your e-signature request is still waiting for you, and your signing link expires in 24 hours. Please sign today so we can continue processing your filing.',
            'cta_label' => 'Sign Now',
        ];
    }

    /**
     * For an awaiting-e-signature reminder, deep-link to the signing page (a
     * temporary signed URL); otherwise link to the filing detail page.
     */
    private function resolveCtaUrl($filing, FilingStatus $status): string
    {
        if ($status === FilingStatus::AwaitingEsign && $signingUrl = self::signingUrlFor($filing)) {
            return $signingUrl;
        }

        return route('lien.filings.show', $filing);
    }

    /**
     * A temporary signed link to the filing's active e-signature session, or
     * null when none is waiting. Shared with FilingActionRequested.
     */
    public static function signingUrlFor(LienFiling $filing): ?string
    {
        $active = $filing->activeSignatureRequest();

        if ($active === null) {
            return null;
        }

        $ttlDays = (int) config('esign.signing.invitation_link_ttl_days', 14);

        return URL::temporarySignedRoute('esign.sign', now()->addDays($ttlDays), ['request' => $active->public_id]);
    }

    public function envelope(): Envelope
    {
        $prefix = match (true) {
            $this->step <= 2 => 'Action required',
            $this->step <= 3 => 'Reminder',
            default => 'Urgent',
        };

        return new Envelope(
            replyTo: $this->supportReplyTo(),
            subject: "{$prefix}: {$this->headline} — eRegister",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.filing-action-reminder',
            with: [
                'userName' => $this->userName,
                'headline' => $this->headline,
                'body' => $this->body,
                'ctaLabel' => $this->ctaLabel,
                'ctaUrl' => $this->ctaUrl,
                'projectName' => $this->projectName,
                'requestText' => $this->requestText,
                'step' => $this->step,
            ],
        );
    }
}
