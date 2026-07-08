<?php

namespace App\Domains\Lien\Esign\Actions;

use App\Domains\Esign\Actions\VoidSignatureRequest;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\User;

/**
 * Voids a waiver's active signature request and returns the waiver to
 * Generated so it can be corrected and re-sent.
 */
class VoidWaiverSignatureRequest
{
    public function __construct(private readonly VoidSignatureRequest $void) {}

    public function execute(LienWaiver $waiver, User $actor, ?string $reason = null): void
    {
        $request = $waiver->activeSignatureRequest();

        if ($request !== null) {
            $this->void->execute($request, $actor, $reason);
        }

        if ($waiver->status === WaiverStatus::AwaitingSignature) {
            $waiver->update(['status' => WaiverStatus::Generated, 'sent_at' => null]);
        }
    }
}
