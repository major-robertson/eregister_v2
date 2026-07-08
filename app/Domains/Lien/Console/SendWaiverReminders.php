<?php

namespace App\Domains\Lien\Console;

use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Esign\LienWaiverSignable;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Models\LienWaiverNotificationLog;
use App\Mail\WaiverSignatureReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Nudges signers about lien waivers still awaiting signature. Runs hourly;
 * the per-(waiver, interval) log rows make each reminder send exactly once,
 * and the log is written before the mail is queued so a crash can't double-send.
 * A signer gets at most one email per run: catching up after downtime logs
 * every overdue interval but only mails the latest.
 */
class SendWaiverReminders extends Command
{
    protected $signature = 'lien:send-waiver-reminders';

    protected $description = 'Send reminder emails for lien waivers awaiting signature';

    public function handle(): int
    {
        $intervals = config('lien_waivers.reminder_intervals_days', [3, 7, 12]);
        $sent = 0;

        $requests = SignatureRequest::query()
            ->where('document_signing_policy_key', LienWaiverSignable::DOCUMENT_TYPE)
            ->where('status', SignatureRequestStatus::AwaitingSignature)
            ->whereNotNull('invited_at')
            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now()))
            ->get();

        foreach ($requests as $request) {
            $waiver = $request->signable;

            if (! $waiver instanceof LienWaiver) {
                continue;
            }

            $daysWaiting = (int) $request->invited_at->diffInDays(Carbon::now());

            $alreadyLogged = LienWaiverNotificationLog::query()
                ->withoutGlobalScopes()
                ->where('lien_waiver_id', $waiver->id)
                ->where('type', 'signature_reminder')
                ->pluck('interval_days')
                ->all();

            $due = array_values(array_filter($intervals,
                fn (int $interval) => $daysWaiting >= $interval && ! in_array($interval, $alreadyLogged, true)));

            if ($due === []) {
                continue;
            }

            // Every passed interval is logged so it never re-fires, but the
            // signer gets a single email per run: catching up after scheduler
            // downtime must not burst day-3/7/12 mails all at once.
            foreach ($due as $interval) {
                LienWaiverNotificationLog::create([
                    'business_id' => $waiver->business_id,
                    'lien_waiver_id' => $waiver->id,
                    'type' => 'signature_reminder',
                    'interval_days' => $interval,
                    'sent_at' => Carbon::now(),
                ]);
            }

            Mail::to($request->signer_email_snapshot)
                ->queue(new WaiverSignatureReminder($request, $waiver, $daysWaiting));

            $sent++;
        }

        $this->info("Sent {$sent} waiver signature reminder(s).");

        return self::SUCCESS;
    }
}
