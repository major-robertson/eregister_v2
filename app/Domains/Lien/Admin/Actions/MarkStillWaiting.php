<?php

namespace App\Domains\Lien\Admin\Actions;

use App\Domains\Lien\Models\LienFiling;
use App\Models\EmailSequence;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MarkStillWaiting
{
    /**
     * "Still waiting" on a filing the customer emailed us about (EREG-132):
     * their email did not give us what we need. Clears the flag, logs a
     * reply_dismissed event with the agent's optional note and, while the
     * filing waits on the customer, restarts the reminders from step 1
     * (startReminderFor replaces the paused sequence). The status stays.
     *
     * Returns false, doing nothing, when the filing is not flagged.
     */
    public function execute(LienFiling $filing, ?string $note = null): bool
    {
        if (! $filing->hasCustomerEmail()) {
            return false;
        }

        DB::transaction(function () use ($filing, $note) {
            $waiting = $filing->status->isWaitingOnCustomer();
            $user = $waiting ? ($filing->createdBy ?? User::find($filing->created_by_user_id)) : null;

            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'reply_dismissed',
                'payload_json' => [
                    'note' => filled($note) ? trim($note) : null,
                    'emailed_at' => $filing->customer_emailed_at->toIso8601String(),
                    'front_url' => $filing->customer_emailed_front_url,
                    'reminders_restarted' => $user !== null,
                ],
                'created_by' => auth()->id(),
            ]);

            $filing->update([
                'customer_emailed_at' => null,
                'customer_emailed_front_url' => null,
            ]);

            if ($user !== null) {
                EmailSequence::startReminderFor($filing, $filing->status, $user, $filing->business);
            }
        });

        return true;
    }
}
