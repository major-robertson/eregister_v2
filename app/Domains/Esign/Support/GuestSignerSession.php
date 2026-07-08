<?php

namespace App\Domains\Esign\Support;

use App\Domains\Esign\Models\SignatureRequest;

/**
 * Session markers for guest signers (no account). A guest proves control of
 * the invited email with a one-time code; the proof lives in their session so
 * every subsequent page in the ceremony can check it cheaply.
 */
class GuestSignerSession
{
    public static function verifiedKey(SignatureRequest $request): string
    {
        return "esign_guest_verified_{$request->id}";
    }

    public static function markVerified(SignatureRequest $request): void
    {
        session()->put(self::verifiedKey($request), now()->toIso8601String());
    }

    public static function isVerified(SignatureRequest $request): bool
    {
        return session()->has(self::verifiedKey($request));
    }

    /**
     * Set when the guest arrives through the emailed signed URL. Code emails
     * are only (re)issued to challenged sessions, so knowing a request's
     * public id alone can't be used to bombard the signer with code emails.
     */
    public static function challengedKey(SignatureRequest $request): string
    {
        return "esign_guest_challenged_{$request->id}";
    }

    public static function markChallenged(SignatureRequest $request): void
    {
        session()->put(self::challengedKey($request), now()->toIso8601String());
    }

    public static function isChallenged(SignatureRequest $request): bool
    {
        return session()->has(self::challengedKey($request));
    }
}
