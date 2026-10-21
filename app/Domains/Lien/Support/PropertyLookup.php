<?php

namespace App\Domains\Lien\Support;

use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Models\LienFiling;

/**
 * Where a customer finds the property's legal description or parcel number
 * (EREG-136), for the wizard's Property step, the steps email and the
 * filing page's entry panel:
 *
 * - parcelLabel: what the state calls the parcel number,
 *   recording.parcel_label in database/data/lien_documents/{xx}.php ("Tax
 *   Parcel ID", "APN", "PIN"), which a county file may override.
 * - assessorUrl: the county assessor's search page, recording.assessor_url
 *   in the county file (database/data/lien_counties), else the state file's
 *   assessor directory under the same key. Null when neither has one: we
 *   never make up a link.
 *
 * We never look the property up for the customer: a legal description we
 * supply can be read as proof we wrote the lien.
 */
final class PropertyLookup
{
    public function __construct(
        public readonly string $parcelLabel,
        public readonly ?string $assessorUrl,
        public readonly string $stateName,
    ) {}

    public static function forFiling(LienFiling $filing): self
    {
        $state = strtoupper((string) ($filing->jurisdiction_state ?: $filing->project?->jobsite_state));

        if ($state === '') {
            return new self('Parcel ID', null, '');
        }

        return self::fromRecording(
            LienDocumentRegistry::for($state)['recording'] ?? [],
            LienDocumentRegistry::county($state, LienCountyKey::forFiling($filing))['recording'] ?? [],
            (string) (config('states')[$state] ?? $state),
        );
    }

    /**
     * From the state's and the county's `recording` blocks; the county wins.
     *
     * @param  array<string, mixed>  $stateRecording
     * @param  array<string, mixed>  $countyRecording
     */
    public static function fromRecording(array $stateRecording, array $countyRecording, string $stateName): self
    {
        $label = trim((string) ($countyRecording['parcel_label'] ?? $stateRecording['parcel_label'] ?? ''));

        return new self(
            parcelLabel: $label !== '' ? $label : 'Parcel ID',
            assessorUrl: self::url($countyRecording['assessor_url'] ?? null) ?? self::url($stateRecording['assessor_url'] ?? null),
            stateName: $stateName,
        );
    }

    /**
     * "parcel number (Tax Parcel ID)", or "parcel number" where the state's
     * own word says the same.
     */
    public function parcelNumberPhrase(): string
    {
        return strcasecmp($this->parcelLabel, 'parcel number') === 0
            ? 'parcel number'
            : "parcel number ({$this->parcelLabel})";
    }

    /**
     * The state's own name for the parcel number ("Tax Parcel ID", "PIN"),
     * or null when it is just the generic "Parcel ID" or "Parcel Number".
     */
    public function localParcelLabel(): ?string
    {
        return in_array(strtolower($this->parcelLabel), ['parcel id', 'parcel number'], true) || $this->stateName === ''
            ? null
            : $this->parcelLabel;
    }

    /**
     * An https link from the data files, or null.
     */
    private static function url(mixed $value): ?string
    {
        if (! is_string($value) || ! str_starts_with($value, 'https://')) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false ? $value : null;
    }
}
