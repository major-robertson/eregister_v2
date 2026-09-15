<?php

namespace App\Domains\Lien\Esign\Actions;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Models\LienFiling;
use App\Mail\SignerReminder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * Admin "Send reminder" for a demand letter still awaiting e-signature.
 * Re-emails the signer their signing link and renews it for a fresh TTL
 * window, so a reminder sent after the original link lapsed still works. The
 * nudge is recorded in the hash-chained audit trail and on the filing's
 * activity timeline.
 */
class SendDemandLetterReminder
{
    public function __construct(private readonly AppendSignatureEvent $events) {}

    public function execute(LienFiling $filing, User $admin): SignatureRequest
    {
        if ($filing->trashed()) {
            throw new EsignException('This filing has been deleted.');
        }

        $request = $filing->activeSignatureRequest();

        if ($request?->status !== SignatureRequestStatus::AwaitingSignature) {
            throw new EsignException('This filing has no signature request awaiting the signer.');
        }

        // Postmark suppresses a bounced address and rejects every later send.
        if ($request->invitation_bounced_at !== null) {
            throw new EsignException("Email to {$request->signer_email_snapshot} is bouncing, so a reminder can't be delivered. Copy the signing link and share it with the signer directly, or void and re-send once their address is fixed.");
        }

        $this->ensureCooldownElapsed($request);

        $ttlDays = (int) config('esign.signing.invitation_link_ttl_days', 14);
        $email = $request->signer_email_snapshot;

        DB::transaction(function () use ($request, $filing, $admin, $ttlDays, $email): void {
            $request->update(['expires_at' => Carbon::now()->addDays($ttlDays)]);

            $this->events->execute($request, SignatureEventType::ReminderSent,
                actorType: 'admin', actorUserId: $admin->id, ip: request()?->ip(), userAgent: request()?->userAgent(),
                metadata: ['email' => $email, 'link_expires_at' => $request->expires_at->toIso8601String()],
            );

            // An afterCommit mailable: a rolled-back reminder never goes out.
            Mail::to($email)->queue(new SignerReminder($request));

            // Lightweight mirror for the admin activity timeline.
            $filing->events()->create([
                'business_id' => $filing->business_id,
                'event_type' => 'esign_reminder_sent',
                'payload_json' => [
                    'signature_request_id' => $request->public_id,
                    'signer_email' => $email,
                ],
                'created_by' => $admin->id,
            ]);
        });

        return $request->refresh();
    }

    /**
     * One signer email per cooldown window, counted from the most recent send
     * (the invitation or an earlier reminder), so a double-click or two admins
     * can't stack emails.
     */
    private function ensureCooldownElapsed(SignatureRequest $request): void
    {
        $lastReminder = $request->events()
            ->where('event_type', SignatureEventType::ReminderSent->value)
            ->reorder('id', 'desc')
            ->first();

        $lastEmailedAt = $lastReminder?->occurred_at ?? $request->invited_at;

        if ($lastEmailedAt === null) {
            return;
        }

        $nextAllowedAt = $lastEmailedAt->copy()->addMinutes((int) config('esign.signing.reminder_cooldown_minutes', 10));

        if ($nextAllowedAt->isFuture()) {
            throw new EsignException(sprintf(
                'The signer was last emailed at %s ET. You can send another reminder after %s ET, or copy the signing link and share it directly.',
                $lastEmailedAt->eastern()->format('g:i A'),
                $nextAllowedAt->eastern()->format('g:i A'),
            ));
        }
    }
}
