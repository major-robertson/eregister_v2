<?php

namespace App\Domains\Lien\Services;

use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;

/**
 * The county's recording fee, passed through at cost (EREG-143). A
 * full-service lien or release pays it on top of the service price when the
 * state or county data file states the fee (recording_fee_cents,
 * LienDocumentRegistry::recordingFeeCents()). When the fee is unknown the
 * review step says it is billed at cost after recording. Never on
 * self-serve: the customer records it and pays the county.
 *
 * Price rows cannot hold a per-county amount, so the checkout line item
 * carries the amount itself with a null price_id (FilingCheckout).
 */
final class CountyRecordingFee
{
    public const PRODUCT_KEY = 'county_recording_fee';

    /** The line item on the review step, the checkout page and the receipt. */
    public const LABEL = 'County recording fee';

    /** The review step's line when the fee is not known. */
    public const UNKNOWN_LINE = 'County recording fee billed at cost after recording.';

    /** The documents the county records. */
    public const KINDS = ['mechanics_lien', 'lien_release'];

    /** Whether an order of this document at this service level pays the fee through us. */
    public static function applies(?string $serviceLevel, ?string $kind): bool
    {
        return $serviceLevel === ServiceLevel::FullService->value
            && in_array($kind, self::KINDS, true);
    }

    /** The fee to record this document in this state and county, in cents, or null when unknown. */
    public static function cents(string $state, ?string $countyKey, string $kind): ?int
    {
        $state = strtoupper($state);

        return LienDocumentRegistry::isSupported($state)
            ? LienDocumentRegistry::recordingFeeCents($state, $countyKey, $kind)
            : null;
    }

    /** The fee to charge for the filing as it stands, or null (not full service, not recorded, or unknown). */
    public static function centsFor(LienFiling $filing): ?int
    {
        $kind = $filing->documentType?->slug;

        if (! self::applies($filing->service_level?->value, $kind)) {
            return null;
        }

        $state = (string) ($filing->jurisdiction_state ?: $filing->project?->jobsite_state);

        return self::cents($state, LienCountyKey::forFiling($filing), $kind);
    }

    /**
     * The checkout line item: no price row, the amount itself.
     *
     * @return array{price_id: null, product_key: string, variant_key: null, label: string, amount_cents: int}
     */
    public static function item(int $cents): array
    {
        return [
            'price_id' => null,
            'product_key' => self::PRODUCT_KEY,
            'variant_key' => null,
            'label' => self::LABEL,
            'amount_cents' => $cents,
        ];
    }
}
