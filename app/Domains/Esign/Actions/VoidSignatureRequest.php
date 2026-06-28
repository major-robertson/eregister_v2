<?php

namespace App\Domains\Esign\Actions;

use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Voids an active signing session (e.g. to resend with corrected recipients).
 * Idempotent — does nothing to an already-finished session. The audit chain
 * stays intact; voiding just appends a terminal event.
 */
class VoidSignatureRequest
{
    public function __construct(private readonly AppendSignatureEvent $events) {}

    public function execute(SignatureRequest $request, ?User $actor = null, ?string $reason = null): void
    {
        if (! $request->isActive()) {
            return;
        }

        $request->update([
            'status' => SignatureRequestStatus::Voided,
            'voided_at' => Carbon::now(),
            'failure_reason' => $reason,
        ]);

        $this->events->execute($request, SignatureEventType::SignatureVoided,
            actorType: $actor !== null ? 'admin' : 'system',
            actorUserId: $actor?->id,
            ip: request()?->ip(),
            userAgent: request()?->userAgent(),
            metadata: $reason !== null ? ['reason' => $reason] : [],
        );
    }
}
