<?php

namespace App\Domains\Lien\Enums;

/**
 * Which side of the waiver exchange the user is on. Both directions share the
 * same form engine; they differ in who signs and who is tracked.
 */
enum WaiverDirection: string
{
    /** "I'm being asked for a waiver to get paid": the user signs their own waiver. */
    case Provide = 'provide';

    /** "I need waivers from people I'm paying": a vendor/sub signs at the user's request. */
    case Collect = 'collect';

    public function label(): string
    {
        return match ($this) {
            self::Provide => 'Provide a waiver',
            self::Collect => 'Collect a waiver',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Provide => "I'm being asked to sign a lien waiver so I can get paid.",
            self::Collect => "I'm paying someone and need a signed lien waiver from them.",
        };
    }
}
