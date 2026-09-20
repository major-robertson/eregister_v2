<?php

namespace App\Domains\Lien\Console;

use App\Domains\Lien\Engine\StepStatusCalculator;
use App\Domains\Lien\Enums\DeadlineStatus;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienNotificationLog;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Notifications\DeadlineApproaching;
use App\Models\EmailUnsubscribe;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Emails a business when one of its lien deadlines is exactly 14, 7, 3 or 1
 * days away, or due today. Runs hourly so each business is reached in its own
 * morning.
 *
 * It only ever looks forward. A reminder goes out on its exact day or not at
 * all: there is no catching up, so a deadline that is already past, or whose
 * reminder day went by while this command was down, is never mailed. That is
 * deliberate. This command matched nothing for months (it filtered on a status
 * that does not exist), and the old "due today or earlier" rule would have
 * mailed every overdue deadline in the database the moment it was fixed.
 *
 * Other guards: the deadline's real status is computed (the stored column is
 * not kept up to date), so a step the customer already bought or finished is
 * skipped; the log row is written before the mail is queued, and its unique
 * index means a crash or an overlapping run can drop a reminder but never
 * repeat one; and a run that would send more than a sane number of emails
 * sends none and reports an error instead.
 */
class SendDeadlineReminders extends Command
{
    protected $signature = 'lien:send-deadline-reminders
        {--dry-run : List what would be sent without sending or logging anything}';

    protected $description = 'Send email reminders for upcoming lien deadlines';

    /** A reminder only makes sense while the step is still the customer's to start or pay for. */
    private const OPEN_STATUSES = [
        DeadlineStatus::NotStarted,
        DeadlineStatus::DueSoon,
        DeadlineStatus::InDraft,
        DeadlineStatus::AwaitingPayment,
    ];

    /** The only filing states in which the customer still hasn't paid for the step. */
    private const UNPAID_FILING_STATUSES = [
        FilingStatus::Draft,
        FilingStatus::AwaitingPayment,
    ];

    public function handle(StepStatusCalculator $calculator): int
    {
        if (! config('lien.notifications.reminders_enabled', true)) {
            $this->info('Deadline reminders are turned off (LIEN_DEADLINE_REMINDERS_ENABLED).');

            return self::SUCCESS;
        }

        $reminders = $this->dueReminders($calculator);
        $emails = $reminders->sum(fn (array $reminder) => $reminder['recipients']->count());
        $limit = (int) config('lien.notifications.max_emails_per_run', 50);

        // Far above a normal morning. Anything bigger is a bug or bad data,
        // and the safe answer to that is silence, not a burst of email.
        if ($emails > $limit) {
            $message = "lien:send-deadline-reminders would send {$emails} emails in one run (limit {$limit}). Nothing was sent.";

            report(new RuntimeException($message));
            $this->error($message);

            return self::FAILURE;
        }

        $sent = 0;

        foreach ($reminders as $reminder) {
            /** @var LienProjectDeadline $deadline */
            $deadline = $reminder['deadline'];
            $label = "{$deadline->documentType->name} ({$deadline->project->name}), {$reminder['days']} day(s) out, {$reminder['recipients']->count()} recipient(s)";

            if ($this->option('dry-run')) {
                $this->line("  Would send: {$label}");

                continue;
            }

            if (! $this->claim($deadline, $reminder['days'])) {
                continue;
            }

            foreach ($reminder['recipients'] as $user) {
                $user->notify(new DeadlineApproaching($deadline, $reminder['days']));
                $sent++;
            }

            $this->line("  Sent: {$label}");
        }

        $this->info($this->option('dry-run')
            ? "Dry run: {$emails} email(s) for {$reminders->count()} deadline(s) would be sent."
            : "Sent {$sent} deadline reminder email(s).");

        return self::SUCCESS;
    }

    /**
     * Every (deadline, interval) whose day is today in the business's own
     * timezone, not yet logged, still open, and with someone to tell.
     *
     * @return Collection<int, array{deadline: LienProjectDeadline, days: int, recipients: Collection<int, User>}>
     */
    private function dueReminders(StepStatusCalculator $calculator): Collection
    {
        $intervals = array_map('intval', config('lien.notifications.reminder_intervals', [14, 7, 3, 1, 0]));
        $sendFromHour = (int) config('lien.notifications.send_from_hour', 8);

        // A day of slack either side of the window covers every timezone; the
        // exact-day test below is what actually decides.
        $candidates = LienProjectDeadline::withoutGlobalScope('business')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', now()->subDay()->toDateString())
            ->whereDate('due_date', '<=', now()->addDays(max($intervals) + 1)->toDateString())
            ->with(['business', 'project', 'documentType', 'notificationLogs'])
            ->get();

        $reminders = collect();

        foreach ($candidates as $deadline) {
            $business = $deadline->business;

            if ($business === null || $deadline->project === null || $deadline->documentType === null) {
                continue;
            }

            $localNow = now()->timezone($business->timezone ?: 'America/Los_Angeles');

            // Hourly runs start at midnight local time; hold the mail until morning.
            if ($localNow->hour < $sendFromHour) {
                continue;
            }

            $days = $this->wholeDaysBetween($localNow->toDateString(), $deadline->due_date->toDateString());

            // Exact day only. Past deadlines give a negative number, which is
            // in no interval list, so they can never be mailed.
            if (! in_array($days, $intervals, true)) {
                continue;
            }

            if ($deadline->notificationLogs->contains('interval_days', $days)) {
                continue;
            }

            $step = $calculator->forDeadline($deadline);

            if (! in_array($step->status, self::OPEN_STATUSES, true)) {
                continue;
            }

            // Belt and braces: some paid filing states (needs review, ready to
            // file, on hold) don't change the computed status. If they bought
            // this step, in any state, they don't get reminded to buy it.
            if ($step->filingStatus !== null && ! in_array($step->filingStatus, self::UNPAID_FILING_STATUSES, true)) {
                continue;
            }

            $recipients = $business->users()
                ->whereNull('email_bounced_at')
                ->get()
                ->reject(fn (User $user) => EmailUnsubscribe::isUnsubscribed($user, EmailUnsubscribe::CATEGORY_DEADLINE_REMINDERS))
                ->values();

            if ($recipients->isEmpty()) {
                continue;
            }

            $reminders->push(['deadline' => $deadline, 'days' => $days, 'recipients' => $recipients]);
        }

        return $reminders;
    }

    /**
     * Calendar days from one date to another. Both are plain dates read as
     * UTC midnights, so a daylight-saving change can't turn 7 days into 6.96.
     */
    private function wholeDaysBetween(string $from, string $to): int
    {
        return (int) round(Carbon::parse($from, 'UTC')->diffInDays(Carbon::parse($to, 'UTC'), false));
    }

    /**
     * Write the log row first. The unique (deadline, interval) index makes it
     * a claim: if another run got there first this one backs off, and if the
     * process dies before the mail is queued the reminder is lost, not doubled.
     */
    private function claim(LienProjectDeadline $deadline, int $days): bool
    {
        try {
            LienNotificationLog::create([
                'business_id' => $deadline->business_id,
                'project_deadline_id' => $deadline->id,
                'interval_days' => $days,
                'sent_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return false;
        }

        return true;
    }
}
