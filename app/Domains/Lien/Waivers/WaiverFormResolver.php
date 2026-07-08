<?php

namespace App\Domains\Lien\Waivers;

use App\Domains\Lien\Enums\WaiverKind;

/**
 * Routes a (state, kind) pair to the state-correct form. Missouri's
 * residential-only statutory form is the one property-class-sensitive case:
 * unconditional final waivers on residential property get the § 429.016.27
 * document, everything else falls through to the state's normal mapping.
 */
class WaiverFormResolver
{
    public function resolve(string $state, WaiverKind $kind, ?string $propertyClass = null): ResolvedWaiverForm
    {
        $rules = WaiverStateRegistry::for($state);
        $entry = $rules['kinds'][$kind->value] ?? null;

        if ($entry === null || ! ($entry['enabled'] ?? true)) {
            $reason = $entry['disabled_reason'] ?? "This waiver type isn't used in {$rules['state_name']}.";

            throw new WaiverFormUnavailable($reason);
        }

        $template = $entry['template'];
        $title = $entry['title'];

        if ($propertyClass === 'residential' && ! empty($entry['residential_template'])) {
            $template = $entry['residential_template'];
            $title = $entry['residential_title'] ?? $title;
        }

        return new ResolvedWaiverForm(
            state: strtoupper($state),
            kind: $kind,
            template: $template,
            title: $title,
            templateVersion: (int) ($entry['template_version'] ?? 1),
            complianceStandard: $rules['compliance_standard'],
            notarizationRequired: (bool) $rules['notarization_required'],
            witnessRequired: (bool) $rules['witness_required'],
            esignAllowed: (bool) $rules['esign_allowed'],
            esignDisabledReason: $rules['esign_disabled_reason'],
            deemedEffectiveDays: $rules['deemed_effective_days'],
            statute: $rules['statute'],
            uiNotes: $rules['ui_notes'] ?? [],
        );
    }

    /**
     * Every canonical kind with its state-specific availability + naming, for
     * the wizard's selector (disabled entries render greyed-out with the
     * state's explanation instead of disappearing).
     *
     * @return array<string, array{kind: WaiverKind, enabled: bool, title: string, disabled_reason: ?string, redirect_kind: ?string}>
     */
    public function availableKinds(string $state): array
    {
        $rules = WaiverStateRegistry::for($state);
        $out = [];

        foreach (WaiverKind::cases() as $kind) {
            $entry = $rules['kinds'][$kind->value] ?? null;

            $out[$kind->value] = [
                'kind' => $kind,
                'enabled' => $entry !== null && ($entry['enabled'] ?? true),
                'title' => $entry['title'] ?? $kind->label(),
                'disabled_reason' => $entry['disabled_reason'] ?? null,
                'redirect_kind' => $entry['redirect_kind'] ?? null,
            ];
        }

        return $out;
    }
}
