<?php

namespace App\Domains\Lien\Enums;

/**
 * The four canonical waiver families. States with their own structure (GA/MS
 * interim+final, WY single form, UT two forms) map onto these via the state
 * registry. The kind captures the user's intent (progress vs final, paid vs
 * not yet paid) and the registry resolves the state-correct document.
 */
enum WaiverKind: string
{
    case ConditionalProgress = 'conditional_progress';
    case UnconditionalProgress = 'unconditional_progress';
    case ConditionalFinal = 'conditional_final';
    case UnconditionalFinal = 'unconditional_final';

    public function label(): string
    {
        return match ($this) {
            self::ConditionalProgress => 'Conditional Waiver: Progress Payment',
            self::UnconditionalProgress => 'Unconditional Waiver: Progress Payment',
            self::ConditionalFinal => 'Conditional Waiver: Final Payment',
            self::UnconditionalFinal => 'Unconditional Waiver: Final Payment',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::ConditionalProgress => 'Conditional · Progress',
            self::UnconditionalProgress => 'Unconditional · Progress',
            self::ConditionalFinal => 'Conditional · Final',
            self::UnconditionalFinal => 'Unconditional · Final',
        };
    }

    public function isConditional(): bool
    {
        return in_array($this, [self::ConditionalProgress, self::ConditionalFinal], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::ConditionalFinal, self::UnconditionalFinal], true);
    }

    /**
     * Plain-English explanation used by the guided selector.
     */
    public function description(): string
    {
        return match ($this) {
            self::ConditionalProgress => 'Waives rights through a date only once the payment actually arrives. The safe default when you are handing over a waiver before the check clears.',
            self::UnconditionalProgress => 'Waives rights through a date immediately when signed, even if the payment never arrives. Only use after the money is in hand.',
            self::ConditionalFinal => 'Waives all remaining rights on the project once the final payment actually arrives.',
            self::UnconditionalFinal => 'Waives all remaining rights on the project immediately when signed. Only use after final payment is in hand.',
        };
    }
}
