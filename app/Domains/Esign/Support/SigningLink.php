<?php

namespace App\Domains\Esign\Support;

use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Support\Facades\URL;

/**
 * The signed landing URL for a signing session: the same link the invitation
 * email carries. Lets the app take a signer who is already here (a user
 * signing their own lien waiver) straight into the ceremony instead of
 * sending them to their inbox first.
 */
class SigningLink
{
    public static function for(SignatureRequest $request): string
    {
        $expiresAt = $request->expires_at
            ?? now()->addDays((int) config('esign.signing.invitation_link_ttl_days', 14));

        return URL::temporarySignedRoute('esign.sign', $expiresAt, ['request' => $request->public_id]);
    }
}
