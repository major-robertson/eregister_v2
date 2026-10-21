<?php

namespace App\Domains\Lien\Support;

use App\Domains\Lien\Documents\LienCountyKey;
use App\Domains\Lien\Documents\LienDocumentPayload;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\BusinessDays;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Services\OvernightOriginalFee;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

/**
 * How the customer signs a filing's document (EREG-142), in one place: the
 * copy lines the product pages and the wizard show from the state's and
 * county's signature_requirements (LienDocumentRegistry), the admin
 * summary of those flags, and the instruction set the filing page shows
 * while the filing is Awaiting E-Signature or Awaiting Notary:
 *
 * - esign: sign here online.
 * - notarize_and_upload: print, sign before a notary, upload the scan.
 * - notarize_and_mail_original: print, sign before a notary, overnight the
 *   original to eRegister (config lien.documents.preparer) by the date it
 *   must arrive; a full-service order in a county that needs the original.
 *
 * TODO(EREG-128): the request email to the customer uses for($filing) once
 * the request mechanism is on main.
 */
final class NotarizationInstructions
{
    public const ESIGN = 'esign';

    public const NOTARIZE_AND_UPLOAD = 'notarize_and_upload';

    public const NOTARIZE_AND_MAIL_ORIGINAL = 'notarize_and_mail_original';

    /** Next to the document name where notary_required is true. */
    public const NOTARY_LINE = 'Requires a notarized signature.';

    /** Next to the document name where wet_ink_original_required is true. */
    public const ORIGINAL_LINE = 'The county needs the original signed document, mailed to us.';

    /**
     * The filing's signature requirements: its state and county, for its
     * document (LienDocumentRegistry::signatureRequirements()). Every flag
     * unknown when the filing has no supported state.
     *
     * @return array{esign_accepted: ?bool, notary_required: ?bool, wet_ink_original_required: ?bool, ron_accepted: ?bool, source: ?string}
     */
    public static function requirements(LienFiling $filing): array
    {
        $state = self::state($filing);

        if (! LienDocumentRegistry::isSupported($state)) {
            return LienDocumentRegistry::SIGNATURE_REQUIREMENTS;
        }

        return LienDocumentRegistry::signatureRequirements($state, LienCountyKey::forFiling($filing), $filing->documentType?->slug);
    }

    /**
     * The lines to show next to the document name. A flag that is false or
     * unknown adds nothing.
     *
     * @param  array<string, mixed>  $requirements
     * @return list<string>
     */
    public static function lines(array $requirements): array
    {
        return array_values(array_filter([
            ($requirements['notary_required'] ?? null) === true ? self::NOTARY_LINE : null,
            ($requirements['wet_ink_original_required'] ?? null) === true ? self::ORIGINAL_LINE : null,
        ]));
    }

    /**
     * The flags for staff: "Notary required · Original required · E-sign
     * not accepted", or "Unknown" when nothing is known.
     *
     * @param  array<string, mixed>  $requirements
     */
    public static function summary(array $requirements): string
    {
        $flag = fn (string $key, string $yes, string $no): ?string => match ($requirements[$key] ?? null) {
            true => $yes,
            false => $no,
            default => null,
        };

        $parts = array_filter([
            $flag('notary_required', 'Notary required', 'No notary'),
            $flag('wet_ink_original_required', 'Original required', 'Original not required'),
            $flag('esign_accepted', 'E-sign accepted', 'E-sign not accepted'),
            $flag('ron_accepted', 'Online notary accepted', 'Online notary not accepted'),
        ]);

        return $parts === [] ? 'Unknown' : implode(' · ', $parts);
    }

    /**
     * The day the signed original must reach us: one business day before
     * the date we must submit the document by.
     */
    public static function originalDueBy(?CarbonInterface $mustSubmitBy): ?CarbonImmutable
    {
        return $mustSubmitBy === null ? null : BusinessDays::subtract($mustSubmitBy, 1);
    }

    /**
     * Which instruction set the filing needs. The status staff set wins:
     * Awaiting E-Signature signs online and Awaiting Notary always goes to
     * a notary. Otherwise a notary is needed where the requirements say so
     * (unknown: where the document prints a notary certificate) or where
     * e-signing is not accepted. The original is mailed to us only on a
     * full-service order in a county that needs it.
     */
    public static function key(LienFiling $filing): string
    {
        if ($filing->status === FilingStatus::AwaitingEsign) {
            return self::ESIGN;
        }

        $requirements = self::requirements($filing);

        $notarize = $filing->status === FilingStatus::AwaitingNotary
            || ($requirements['notary_required'] ?? self::documentIsNotarized($filing))
            || $requirements['esign_accepted'] === false;

        if (! $notarize) {
            return self::ESIGN;
        }

        return OvernightOriginalFee::applies($filing->service_level?->value, $requirements)
            ? self::NOTARIZE_AND_MAIL_ORIGINAL
            : self::NOTARIZE_AND_UPLOAD;
    }

    /**
     * The instruction set for the filing: a heading and 3 to 5 short steps;
     * the mail-in set adds a note on the overnight fee its order paid.
     *
     * @return array{key: string, heading: string, steps: list<string>, note: ?string}
     */
    public static function for(LienFiling $filing): array
    {
        $key = self::key($filing);
        $document = Str::lower($filing->documentType?->name ?? 'document');

        $notarySteps = [
            'Print the document. Do not sign it yet.',
            'Take it and your photo ID to a notary. Sign it in front of the notary.',
            'Check that the notary signed, dated and stamped it.',
        ];

        return match ($key) {
            self::ESIGN => [
                'key' => $key,
                'heading' => "Sign your {$document} online",
                'steps' => [
                    'Open the signing email we sent you.',
                    'Check every name, date and amount.',
                    'Sign where it asks you to.',
                    'You do not need to print it or see a notary.',
                ],
                'note' => null,
            ],
            self::NOTARIZE_AND_UPLOAD => [
                'key' => $key,
                'heading' => "Sign your {$document} in front of a notary",
                'steps' => [
                    ...$notarySteps,
                    'Scan every page and upload the scan to us.',
                    'Use a scanner or a scanning app. Counties can reject phone photos.',
                ],
                'note' => null,
            ],
            self::NOTARIZE_AND_MAIL_ORIGINAL => [
                'key' => $key,
                'heading' => "Sign your {$document} in front of a notary and mail us the original",
                'steps' => [
                    ...$notarySteps,
                    'The county needs the original. Overnight it to us at '.self::mailingAddress().'.',
                    self::arrivalStep($filing),
                ],
                'note' => self::overnightFeeNote($filing),
            ],
        };
    }

    /** Whether the filing's generated document prints a notary certificate for its state. */
    private static function documentIsNotarized(LienFiling $filing): bool
    {
        $kind = $filing->documentKind();
        $state = self::state($filing);

        if ($kind === null || ! LienDocumentRegistry::isSupported($state)) {
            return false;
        }

        $execution = LienDocumentRegistry::for($state)['kinds'][$kind]['execution'];

        return (bool) ($execution['notary'] ?? false) || (bool) ($execution['notary_in_body'] ?? false);
    }

    private static function state(LienFiling $filing): string
    {
        return strtoupper((string) ($filing->jurisdiction_state ?: $filing->project?->jobsite_state));
    }

    /** "eRegister, 4869 Brownsboro Rd STE 101-E, Louisville, KY 40207": the preparer's return-to address. */
    private static function mailingAddress(): string
    {
        $preparer = LienDocumentPayload::preparer();

        return implode(', ', array_filter([
            $preparer['name'],
            $preparer['attention'] ? 'Attn: '.$preparer['attention'] : null,
            ...$preparer['address_lines'],
        ]));
    }

    private static function arrivalStep(LienFiling $filing): string
    {
        $dueBy = self::originalDueBy($filing->must_submit_by);

        return $dueBy === null
            ? 'Send it as soon as it is signed.'
            : 'It must reach us by '.$dueBy->format('F j, Y').'.';
    }

    /** What the order paid for the overnight return, from its payment's line items, or null. */
    private static function overnightFeeNote(LienFiling $filing): ?string
    {
        $item = collect($filing->payment?->meta['items'] ?? [])
            ->firstWhere('product_key', OvernightOriginalFee::PRODUCT_KEY);

        return $item === null
            ? null
            : 'Your order included $'.number_format(((int) $item['amount_cents']) / 100, 2).' for the overnight return of your original.';
    }
}
