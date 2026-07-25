<?php

namespace App\Support\Email;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Flags every user account behind a dead email address. Called from the
 * Postmark webhook (hard bounce / spam complaint) and from the queue-failure
 * fallback that parses Postmark's "inactive recipient" 406 rejection.
 *
 * The flag stops outbound sequences (EmailSequence::shouldSuppress) and shows
 * the portal banner prompting the user to update their address; changing the
 * email clears it (User::booted).
 */
class RecordEmailBounce
{
    /**
     * @return int number of user accounts flagged
     */
    public static function record(string $email, string $reason): int
    {
        $users = User::query()->where('email', $email)->get();

        foreach ($users as $user) {
            $user->forceFill([
                'email_bounced_at' => now(),
                'email_bounce_reason' => Str::limit($reason, 255, ''),
            ])->save();
        }

        if ($users->isNotEmpty()) {
            Log::info('RecordEmailBounce: flagged bounced email', [
                'email' => $email,
                'reason' => $reason,
                'users' => $users->pluck('id')->all(),
            ]);
        }

        return $users->count();
    }
}
