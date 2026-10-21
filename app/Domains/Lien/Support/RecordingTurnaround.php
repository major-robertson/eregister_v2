<?php

namespace App\Domains\Lien\Support;

use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\ServiceLevel;

/**
 * How long recording typically takes once we submit (EREG-143), for the
 * pricing page, the service comparison and the review step: the words in
 * config lien.recording_turnaround for the county's recording method
 * (LienDocumentRegistry::recordingMethod(); a county without a file uses
 * the state's), or the county file's `recording.turnaround` (one string, or
 * strings keyed by method), as EREG-130's milestone email reads them.
 */
final class RecordingTurnaround
{
    /** The documents the county records. */
    public const KINDS = ['mechanics_lien', 'lien_release'];

    /** Whether we record this order with the county: a full-service lien or release. */
    public static function weRecord(?string $serviceLevel, ?string $kind): bool
    {
        return $serviceLevel === ServiceLevel::FullService->value
            && in_array($kind, self::KINDS, true);
    }

    /** "1 to 5 business days", or null when nothing is known for the method. */
    public static function words(string $state, ?string $countyKey = null): ?string
    {
        $state = strtoupper($state);

        if (! LienDocumentRegistry::isSupported($state)) {
            return null;
        }

        $method = LienDocumentRegistry::recordingMethod($state, $countyKey);
        $override = LienDocumentRegistry::county($state, $countyKey)['recording']['turnaround'] ?? null;

        $words = match (true) {
            is_string($override) => $override,
            is_array($override) => $override[$method] ?? null,
            default => null,
        } ?: config("lien.recording_turnaround.{$method}");

        return is_string($words) && $words !== '' ? $words : null;
    }

    /** "Typically 1 to 5 business days after we submit.", or null. */
    public static function sentence(string $state, ?string $countyKey = null): ?string
    {
        $words = self::words($state, $countyKey);

        return $words === null ? null : "Typically {$words} after we submit.";
    }
}
