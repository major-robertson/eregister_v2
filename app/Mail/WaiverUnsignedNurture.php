<?php

namespace App\Mail;

use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Models\EmailSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

/**
 * The waiver_unsigned sequence: a free-plan user saved a waiver.
 *   1. (2 hours) it's ready; sign it here instead of print-sign-scan
 *   2. (day 2)   does an e-signed waiver hold up
 *   3. (day 7)   the job's notice and lien deadlines (the filing cross-sell)
 *   4. (day 21)  next draw
 * Every step reads the waiver as it is when the mail is sent, so a waiver
 * that has since been signed on paper never gets "sign your waiver".
 */
class WaiverUnsignedNurture extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** Document types worth naming in the day-7 email, in the order a job meets them. */
    private const DEADLINE_TYPES = ['prelim_notice', 'noi', 'mechanics_lien'];

    public function __construct(
        public EmailSequence $sequence,
        public int $step
    ) {
        $this->afterCommit = true;
    }

    public function envelope(): Envelope
    {
        $waiver = $this->waiver();
        $state = $this->stateName($waiver);
        $nextDeadline = $this->step === 3 ? $this->upcomingDeadlines($waiver)->first() : null;

        return new Envelope(subject: match ($this->step) {
            1 => $waiver?->direction === WaiverDirection::Collect
                ? "Your {$state} waiver is ready to send"
                : "Your {$state} waiver is ready",
            2 => 'Is an e-signed lien waiver legal?',
            3 => $nextDeadline !== null
                ? "Your {$nextDeadline->documentType->name} is due ".$nextDeadline->due_date->format('M j')
                : 'What if you don\'t get paid on this job?',
            default => 'Need another lien waiver?',
        });
    }

    public function content(): Content
    {
        $waiver = $this->waiver();
        $project = $waiver?->project;

        return new Content(
            markdown: 'mail.waiver-unsigned',
            with: [
                'userName' => $this->sequence->user->first_name ?? 'there',
                'step' => $this->step,
                'stateName' => $this->stateName($waiver),
                'projectName' => $project?->name,
                'counterparty' => $waiver?->counterpartyDisplayName(),
                // The signed copy is only emailed when the other party has an address.
                'emailsCounterparty' => filled($waiver?->counterparty_email),
                'isCollect' => $waiver?->direction === WaiverDirection::Collect,
                // Still waiting for a signature (not signed on paper, not sent).
                'isUnsigned' => $waiver?->status === WaiverStatus::Generated,
                'deadlines' => $this->step === 3
                    ? $this->upcomingDeadlines($waiver)->map(fn (LienProjectDeadline $deadline) => [
                        'name' => $deadline->documentType->name,
                        'due' => $deadline->due_date->format('F j, Y'),
                        'left' => $this->daysLeftLabel($deadline),
                    ])->all()
                    : [],
                'proMonthly' => '$'.number_format(config('lien_waivers.prices.monthly.amount_cents') / 100),
                'waiverUrl' => $waiver !== null ? route('lien.waivers.show', $waiver) : route('lien.waivers.index'),
                'projectUrl' => $project !== null ? route('lien.projects.show', $project) : route('lien.waivers.index'),
                'newWaiverUrl' => route('lien.waivers.create'),
                'preferencesUrl' => URL::signedRoute('email.preferences', ['user' => $this->sequence->user_id]),
            ],
        );
    }

    private function waiver(): ?LienWaiver
    {
        $waiver = $this->sequence->sequenceable;

        return $waiver instanceof LienWaiver ? $waiver : null;
    }

    private function daysLeftLabel(LienProjectDeadline $deadline): ?string
    {
        $days = $deadline->daysRemaining();

        return match (true) {
            $days === null || $days < 0 => null,
            $days === 0 => 'today',
            $days === 1 => '1 day left',
            default => "{$days} days left",
        };
    }

    private function stateName(?LienWaiver $waiver): string
    {
        return WaiverStateRegistry::STATE_NAMES[strtoupper((string) $waiver?->state)] ?? 'lien';
    }

    /**
     * Deadlines the app has actually calculated for this job and that are
     * still ahead. Nothing is listed (and the email says so) when the project
     * has no dates to calculate from.
     *
     * @return Collection<int, LienProjectDeadline>
     */
    private function upcomingDeadlines(?LienWaiver $waiver): Collection
    {
        if ($waiver?->project === null) {
            return collect();
        }

        return $waiver->project->deadlines()
            ->with('documentType')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', today())
            ->whereIn('status', [DeadlineStatus::NotStarted, DeadlineStatus::DueSoon])
            ->orderBy('due_date')
            ->get()
            ->filter(fn (LienProjectDeadline $deadline) => in_array($deadline->documentType?->slug, self::DEADLINE_TYPES, true))
            ->take(2)
            ->values();
    }
}
