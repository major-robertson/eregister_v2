<?php

namespace App\Domains\Lien\Services;

use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Support\NotarizationInstructions;
use App\Models\Price;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * The overnight original fee (EREG-142). A full-service order whose county
 * needs the original signed document (signature_requirements
 * wet_ink_original_required, LienDocumentRegistry) pays it on top of the
 * service price for the overnight return of the original to us. Never on
 * self-serve, and never where the flag is false or unknown.
 *
 * The fee is the active prices row lien/overnight_original/standard, seeded
 * from config lien.overnight_original_fee_cents and charged in the same
 * PaymentIntent as the service (FilingCheckout). No active row charges
 * nothing.
 */
final class OvernightOriginalFee
{
    public const PRODUCT_KEY = 'overnight_original';

    public const VARIANT_KEY = 'standard';

    /** The line item on the review step, the checkout page and the receipt. */
    public const LABEL = 'Overnight return of your original';

    /**
     * Whether a service level and a filing's signature requirements
     * (NotarizationInstructions::requirements()) pay the fee.
     *
     * @param  array<string, mixed>  $requirements
     */
    public static function applies(?string $serviceLevel, array $requirements): bool
    {
        return $serviceLevel === ServiceLevel::FullService->value
            && ($requirements['wet_ink_original_required'] ?? null) === true;
    }

    /** The fee row to charge for the filing as it stands, or null. */
    public static function priceFor(LienFiling $filing): ?Price
    {
        return self::applies($filing->service_level?->value, NotarizationInstructions::requirements($filing))
            ? self::price()
            : null;
    }

    /** The active fee row, or null (logged) when there is none. */
    public static function price(): ?Price
    {
        try {
            return Price::resolve('lien', self::PRODUCT_KEY, self::VARIANT_KEY);
        } catch (InvalidArgumentException) {
            Log::warning('Lien overnight original fee has no active price row; not charged');

            return null;
        }
    }
}
