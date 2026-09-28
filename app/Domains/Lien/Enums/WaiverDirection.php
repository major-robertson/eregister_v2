<?php

namespace App\Domains\Lien\Enums;

/**
 * Which side of the waiver exchange the user is on. Both directions share the
 * same form engine; they differ in who signs and who is tracked.
 */
enum WaiverDirection: string
{
    /** "I need waivers from people I'm paying": a vendor/sub signs at the user's request. Listed first in the wizard. */
    case Collect = 'collect';

    /** "I'm being asked for a waiver to get paid": the user signs their own waiver. */
    case Provide = 'provide';

    public function label(): string
    {
        return match ($this) {
            self::Provide => 'Provide a waiver',
            self::Collect => 'Collect a waiver',
        };
    }

    /**
     * The answer to "Are you paying or getting paid?" in the wizard's first
     * step and the landing-page starter. It names the side of the payment,
     * not the waiver: contractors who were getting paid picked "Collect a
     * waiver" by mistake (EREG-85).
     */
    public function choice(): string
    {
        return match ($this) {
            self::Collect => "I'm paying someone",
            self::Provide => "I'm getting paid",
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Collect => "I need a signed lien waiver from the sub or supplier I'm paying. They sign it.",
            self::Provide => 'My customer wants a signed lien waiver before they pay me. I sign it.',
        };
    }
}
