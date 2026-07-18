<?php

namespace App\Domains\Lien\Waivers\Actions;

use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

/**
 * Store an executed (wet-signed) copy on a waiver and mark it Signed — the
 * paper path shared by the wizard's review step and the waiver page. Required
 * in notary/witness states where e-signing is unavailable, and used anywhere
 * a counterparty signs a printout. Callers handle their own gating and any
 * outstanding e-sign request; this only records the executed copy.
 */
class StoreSignedCopy
{
    public function execute(LienWaiver $waiver, UploadedFile $file, ?Carbon $preservedSentAt = null): LienWaiver
    {
        $waiver->addMedia($file->getRealPath())
            ->usingFileName($file->getClientOriginalName())
            ->toMediaCollection('signed');

        $signedAt = now();

        // GA/MS: a signed waiver becomes conclusively effective N days after
        // execution unless payment arrives or an Affidavit of Nonpayment is
        // filed. The statutory countdown is a calendar-date rule, so anchor it
        // on the Eastern date we display signed_at in (not raw UTC), or an
        // evening-Eastern signing lands a day late.
        $deemedDays = WaiverStateRegistry::for($waiver->state)['deemed_effective_days'];

        $waiver->update([
            'status' => WaiverStatus::Signed,
            'signed_at' => $signedAt,
            // A voided e-sign request nulls sent_at; the caller passes the
            // original so the "Sent for signature" milestone survives.
            'sent_at' => $preservedSentAt ?? $waiver->sent_at,
            'deemed_effective_at' => $deemedDays !== null
                ? $signedAt->copy()->eastern()->addDays($deemedDays)->toDateString()
                : null,
        ]);

        return $waiver;
    }
}
