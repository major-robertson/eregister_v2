<?php

namespace App\Domains\Esign\Listeners;

use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Models\User;
use Illuminate\Auth\Events\Login;

/**
 * When someone logs in (including right after registering), attach any
 * COMPLETED guest signature requests addressed to their email to the account,
 * so "documents I've signed" shows their pre-account history. Active guest
 * sessions are left alone; converting one mid-ceremony would flip its access
 * mode under the signer.
 */
class ClaimGuestSignatureRequests
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (! $user instanceof User || blank($user->email)) {
            return;
        }

        // Only claim onto accounts that have PROVEN the email. Without this, a
        // squatter registering someone else's address (unverified) would take
        // ownership of that person's signed documents at first login.
        if ($user->email_verified_at === null) {
            return;
        }

        SignatureRequest::query()
            ->whereNull('signer_user_id')
            ->where('signer_email_snapshot', $user->email)
            ->where('status', SignatureRequestStatus::Completed)
            ->update(['signer_user_id' => $user->id]);
    }
}
