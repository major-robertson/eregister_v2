<?php

namespace App\Support\Seo;

final class Text
{
    /**
     * Repair UTF-8 that was decoded as Windows-1252 and re-encoded ("Â§",
     * "â€”"), which is how some of the lien rule notes were imported. Strings
     * without those marker bytes are returned untouched, so it is safe to
     * apply to already-clean text.
     */
    public static function fixMojibake(?string $value): ?string
    {
        if ($value === null || $value === '' || ! preg_match('/[\x{00C2}\x{00C3}\x{00E2}]/u', $value)) {
            return $value;
        }

        $repaired = @mb_convert_encoding($value, 'Windows-1252', 'UTF-8');

        if ($repaired === false || ! mb_check_encoding($repaired, 'UTF-8')) {
            return $value;
        }

        // Only accept the repair if it actually removed the mojibake markers.
        return preg_match('/[\x{00C2}\x{00C3}\x{00E2}][\x{0080}-\x{00BF}\x{20AC}\x{201C}-\x{201E}\x{2013}\x{2014}]/u', $repaired)
            ? $value
            : $repaired;
    }

    /** Ordinal words for small numbers: 1 => "1st", 3 => "3rd". */
    public static function ordinal(int $n): string
    {
        $suffix = match (true) {
            $n % 100 >= 11 && $n % 100 <= 13 => 'th',
            $n % 10 === 1 => 'st',
            $n % 10 === 2 => 'nd',
            $n % 10 === 3 => 'rd',
            default => 'th',
        };

        return $n.$suffix;
    }
}
