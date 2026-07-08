<?php

namespace App\Domains\Esign\Actions;

use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureEvent;
use App\Domains\Esign\Models\SignatureRequest;
use App\Mail\GuestSignerCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Emails a guest signer a one-time 6-digit code proving control of the invited
 * address. Only the SHA-256 of the code is stored; codes live 15 minutes,
 * re-sends are throttled to one per minute, and a request can issue at most
 * MAX_SENDS codes over its lifetime (each send is an audit event); after that
 * the sender must void and re-send the request.
 */
class SendGuestSignerCode
{
    public const TTL_MINUTES = 15;

    public const RESEND_SECONDS = 60;

    public const MAX_ATTEMPTS = 5;

    public const MAX_SENDS = 12;

    public function __construct(private readonly AppendSignatureEvent $events) {}

    /**
     * @return bool Whether a code email was issued (false when throttled or capped).
     */
    public function execute(SignatureRequest $request): bool
    {
        if ($request->signer_user_id !== null) {
            throw new EsignException('This signing session belongs to an account, not a guest.');
        }

        if ($request->signer_email_snapshot === null) {
            throw new EsignException('This signing session has no signer email on record.');
        }

        $lastSent = $request->guest_code_last_sent_at;
        if ($lastSent !== null && $lastSent->diffInSeconds(Carbon::now()) < self::RESEND_SECONDS) {
            return false; // Quietly ignore rapid re-sends.
        }

        $lifetimeSends = SignatureEvent::query()
            ->where('signature_request_id', $request->id)
            ->where('event_type', SignatureEventType::GuestCodeSent->value)
            ->count();

        if ($lifetimeSends >= self::MAX_SENDS) {
            return false;
        }

        $code = (string) random_int(100000, 999999);

        $request->update([
            'guest_code_hash' => hash('sha256', $code),
            'guest_code_expires_at' => Carbon::now()->addMinutes(self::TTL_MINUTES),
            'guest_code_attempts' => 0,
            'guest_code_last_sent_at' => Carbon::now(),
        ]);

        $this->events->execute($request, SignatureEventType::GuestCodeSent,
            actorType: 'system', ip: request()?->ip(), userAgent: request()?->userAgent(),
            metadata: ['email' => $request->signer_email_snapshot, 'lifetime_sends' => $lifetimeSends + 1]);

        Mail::to($request->signer_email_snapshot)->queue(new GuestSignerCode($request, $code));

        return true;
    }
}
