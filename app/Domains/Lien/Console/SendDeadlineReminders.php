<?php

namespace App\Domains\Lien\Console;

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Models\LienNotificationLog;
use App\Domains\Lien\Models\LienProjectDeadline;
use App\Domains\Lien\Notifications\DeadlineApproaching;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature = 'lien:send-deadline-reminders';

    protected $description = 'Send email reminders for upcoming lien deadlines';

    public function handle(): int
    {
        $intervals = config('lien.notifications.reminder_intervals', [14, 7, 3, 1, 0]);
        $totalSent = 0;

        // Process each business in their timezone
        Business::whereNotNull('timezone')
            ->chunk(100, function ($businesses) use ($intervals, &$totalSent) {
                foreach ($businesses as $business) {
                    $totalSent += $this->processBusinessDeadlines($business, $intervals);
                }
            });

        // Also process businesses without timezone set (using default)
        Business::whereNull('timezone')
            ->chunk(100, function ($businesses) use ($intervals, &$totalSent) {
                foreach ($businesses as $business) {
                    $totalSent += $this->processBusinessDeadlines($business, $intervals);
                }
            });

        $this->info("Sent {$totalSent} deadline reminder(s).");

        return self::SUCCESS;
    }

    private function processBusinessDeadlines(Business $business, array $intervals): int
    {
        $timezone = $business->timezone ?? 'America/Los_Angeles';
        $now = now()->timezone($timezone)->startOfDay();
        $sentCount = 0;

        foreach ($intervals as $days) {
            $targetDate = $days === 0
                ? $now->copy() // Overdue check - due today or before
                : $now->copy()->addDays($days);

            $query = LienProjectDeadline::withoutGlobalScope('business')
                ->where('business_id', $business->id)
                ->where('status', 'pending')
                ->whereNotNull('due_date')
                ->whereDoesntHave('notificationLogs', fn ($q) => $q->where('interval_days', $days))
                ->with(['project', 'documentType']);

            if ($days === 0) {
                // For overdue, check if due date is today or earlier
                $query->whereDate('due_date', '<=', $targetDate);
            } else {
                $query->whereDate('due_date', $targetDate);
            }

            $deadlines = $query->get();

            foreach ($deadlines as $deadline) {
                $this->sendReminder($business, $deadline, $days);
                $sentCount++;
            }
        }

        return $sentCount;
    }

    private function sendReminder(Business $business, LienProjectDeadline $deadline, int $intervalDays): void
    {
        // Send to all business users
        foreach ($business->users as $user) {
            $user->notify(new DeadlineApproaching($deadline, $intervalDays));
        }

        // Log to prevent duplicates
        LienNotificationLog::create([
            'business_id' => $business->id,
            'project_deadline_id' => $deadline->id,
            'interval_days' => $intervalDays,
            'sent_at' => now(),
        ]);

        $this->line("  Sent reminder for {$deadline->documentType->name} ({$deadline->project->name}) - {$intervalDays} days");
    }
}
