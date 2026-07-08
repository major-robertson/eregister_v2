<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Lien\Enums\WaiverKind;

/**
 * The state-correct document for a (state, kind) pair: which Blade body to
 * render, what it's titled, and the execution rules that travel with it.
 */
final class ResolvedWaiverForm
{
    public function __construct(
        public readonly string $state,
        public readonly WaiverKind $kind,
        public readonly string $template,
        public readonly string $title,
        public readonly int $templateVersion,
        public readonly string $complianceStandard,
        public readonly bool $notarizationRequired,
        public readonly bool $witnessRequired,
        public readonly bool $esignAllowed,
        public readonly ?string $esignDisabledReason,
        public readonly ?int $deemedEffectiveDays,
        public readonly ?string $statute,
        /** @var list<string> */
        public readonly array $uiNotes = [],
    ) {}
}
