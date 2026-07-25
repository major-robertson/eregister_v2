<?php

namespace App\Support\Email;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Models\LienWaiver;
use App\Mail\WaiverInvitationBounced;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Flags everything behind a dead email address. Called from the Postmark
 * webhook (hard bounce / spam complaint) and from the queue-failure fallback
 * that parses Postmark's "inactive recipient" 406 rejection.
 *
 * User accounts: the flag stops outbound sequences (EmailSequence::
 * shouldSuppress) and shows the portal banner prompting the user to update
 * their address; changing the email clears it (User::booted).
 *
 * Signature requests: guest signers (waiver counterparties) have no account,
 * so the flag lives on the request — reminders stop, the bounce lands on the
 * audit timeline, and the waiver owner is told to fix the address and re-send.
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

        static::flagSignatureRequests($email, $reason);

        return $users->count();
    }

    protected static function flagSignatureRequests(string $email, string $reason): void
    {
        $requests = SignatureRequest::query()
            ->active()
            ->where('signer_email_snapshot', $email)
            // Idempotent: Postmark reports one suppression several ways
            // (Bounce webhook, SubscriptionChange webhook, 406 on the next
            // send), and only the first should log an event and mail the owner.
            ->whereNull('invitation_bounced_at')
            ->get();

        foreach ($requests as $request) {
            $request->update(['invitation_bounced_at' => now()]);

            app(AppendSignatureEvent::class)->execute(
                request: $request,
                type: SignatureEventType::InvitationBounced,
                actorType: 'system',
                metadata: ['email' => $email, 'reason' => $reason],
            );

            Log::info('RecordEmailBounce: flagged signature request', [
                'signature_request_id' => $request->id,
                'email' => $email,
                'reason' => $reason,
            ]);

            static::notifyOwner($request, $email);
        }
    }

    /**
     * Tell the waiver owner the invitation never arrived so they can void,
     * fix the counterparty's address, and re-send.
     */
    protected static function notifyOwner(SignatureRequest $request, string $bouncedEmail): void
    {
        $waiver = $request->signable;

        if (! $waiver instanceof LienWaiver) {
            return;
        }

        $owner = $request->createdBy ?? $waiver->createdBy;

        // Never mail an address that's itself dead: on provide-direction
        // waivers the owner IS the signer, and the owner's own email may
        // have bounced independently. The portal banner covers them.
        if ($owner === null
            || strcasecmp($owner->email, $bouncedEmail) === 0
            || $owner->email_bounced_at !== null) {
            return;
        }

        Mail::to($owner->email)->queue(new WaiverInvitationBounced($waiver, $request));
    }
}
