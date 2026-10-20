<?php

namespace App\Domains\Lien\Services;

use App\Domains\Lien\Engine\BusinessDays;
use App\Domains\Lien\Enums\ServiceLevel;
use App\Domains\Lien\Models\LienFiling;
use App\Models\Price;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Rush fees for full-service orders placed close to their deadline
 * (EREG-146).
 *
 * The tier comes from the business days between today (Eastern) and the
 * date we must submit the document by (must_submit_by, from the EREG-138
 * deadline gate), read against config lien.rush_tiers: 4 to 6 days is
 * rush; 3 or fewer is expedite, including 0 and negative (the submit-by
 * date has gone but the deadline itself has not). Once the deadline itself
 * has passed there is no fee (the deadline gate blocks those orders).
 * Only full-service orders on the documents in config
 * lien.rush_fee_documents pay one; never self-serve, documents without a
 * deadline (demand letters, releases) or orders without a submit-by date.
 *
 * The fee is the active prices row lien/rush_fee/{tier}, charged on top of
 * the service price (FilingCheckout). A tier without an active row charges
 * nothing.
 */
final class RushTier
{
    public const RUSH = 'rush';

    public const EXPEDITE = 'expedite';

    /** Documents with a deadline, so a submit-by date: the most config lien.rush_fee_documents can turn on. */
    public const DOCUMENTS = ['mechanics_lien', 'prelim_notice', 'noi'];

    /** Notices are sent; liens are submitted to the county. */
    private const NOTICES = ['prelim_notice', 'noi'];

    /**
     * The tier for a filing as it stands today: its document, service level,
     * stored submit-by date and its deadline row's due date.
     */
    public static function forFiling(LienFiling $filing, ?CarbonInterface $today = null): ?string
    {
        return self::for(
            $filing->documentType?->slug,
            $filing->service_level?->value,
            $filing->must_submit_by,
            $filing->projectDeadline?->due_date,
            $today,
        );
    }

    /**
     * The tier for a document slug, service level, submit-by date and
     * deadline, or null when no rush fee applies. A deadline before today
     * means no fee; an unknown deadline counts as still ahead.
     */
    public static function for(
        ?string $documentSlug,
        ?string $serviceLevel,
        ?CarbonInterface $mustSubmitBy,
        ?CarbonInterface $deadline = null,
        ?CarbonInterface $today = null,
    ): ?string {
        if ($serviceLevel !== ServiceLevel::FullService->value
            || ! self::charges($documentSlug)
            || $mustSubmitBy === null) {
            return null;
        }

        $today ??= now()->eastern();

        if ($deadline !== null && $deadline->format('Y-m-d') < $today->format('Y-m-d')) {
            return null;
        }

        return self::forBusinessDaysLeft(BusinessDays::between($today, $mustSubmitBy));
    }

    /** Whether this document can pay a rush fee: a deadline document listed in config lien.rush_fee_documents. */
    public static function charges(?string $documentSlug): bool
    {
        return in_array($documentSlug, self::DOCUMENTS, true)
            && in_array($documentSlug, (array) config('lien.rush_fee_documents', ['mechanics_lien']), true);
    }

    /**
     * The tier for this many business days left (config lien.rush_tiers):
     * expedite at or below its max_days, however few (its min_days is not
     * read); any other tier inside its min_days to max_days; else null.
     */
    public static function forBusinessDaysLeft(int $days): ?string
    {
        foreach ((array) config('lien.rush_tiers', []) as $tier => $range) {
            $max = (int) ($range['max_days'] ?? PHP_INT_MIN);
            $min = $tier === self::EXPEDITE ? PHP_INT_MIN : (int) ($range['min_days'] ?? PHP_INT_MAX);

            if ($days >= $min && $days <= $max) {
                return (string) $tier;
            }
        }

        return null;
    }

    /**
     * The active fee row for a tier, or null for no tier. A missing row is
     * logged and charges nothing.
     */
    public static function price(?string $tier): ?Price
    {
        if ($tier === null) {
            return null;
        }

        try {
            return Price::resolve('lien', 'rush_fee', $tier);
        } catch (InvalidArgumentException) {
            Log::warning('Lien rush fee has no active price row; not charged', ['tier' => $tier]);

            return null;
        }
    }

    public static function label(string $tier): string
    {
        return match ($tier) {
            self::RUSH => 'Rush',
            self::EXPEDITE => 'Expedite',
            default => Str::headline($tier),
        };
    }

    /** The line item label on the checkout page and the receipt. */
    public static function itemLabel(string $tier): string
    {
        return self::label($tier).' fee';
    }

    /** Only the expedite tier asks the customer to accept its terms. */
    public static function requiresAcknowledgement(?string $tier): bool
    {
        return $tier === self::EXPEDITE;
    }

    /** The one sentence on the review step that says why the fee applies. */
    public static function reason(int $businessDaysLeft, ?string $documentSlug): string
    {
        $left = $businessDaysLeft > 0
            ? $businessDaysLeft.' '.Str::plural('business day', $businessDaysLeft).' left'
            : 'No business days left';

        return in_array($documentSlug, self::NOTICES, true)
            ? "{$left} before we must send it."
            : "{$left} before we must submit to the county.";
    }

    /**
     * The expedite terms the customer ticks before paying. The exact text
     * shown is stored on the rush_acknowledged filing event.
     */
    public static function acknowledgement(?string $documentSlug): string
    {
        $fewerThan = (int) config('lien.rush_tiers.expedite.max_days', 3) + 1;

        $promise = in_array($documentSlug, self::NOTICES, true)
            ? "we can't promise it is served in time"
            : "we can't promise the county records it in time";

        return "We start today. With fewer than {$fewerThan} business days left {$promise}, and the order is not refunded on that basis.";
    }
}
