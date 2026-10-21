<?php

namespace App\Domains\Lien\Support;

use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Services\CountyRecordingFee;

/**
 * Self-serve next to full service for one document in one state (and county,
 * when known), for the pricing page and the wizard's service step (EREG-143):
 * who prepares it, who signs it and how (EREG-142's signature requirements
 * and instruction sets), who records or sends it, the county recording fee,
 * the typical turnaround and what comes back.
 * Unknown facts say so; nothing here is guessed.
 */
final class ServiceComparison
{
    /**
     * The service prices (cents) for a document in a state: config
     * lien.pricing with the state's lien.state_pricing over it, as the
     * wizard and the pricing page show them.
     *
     * @return array{self_serve: int, full_service: int}
     */
    public static function prices(string $kind, string $state = ''): array
    {
        $prices = array_replace(
            config("lien.pricing.{$kind}", ['self_serve' => 0, 'full_service' => 0]),
            $state === '' ? [] : config('lien.state_pricing.'.strtoupper($state).".{$kind}", []),
        );

        return ['self_serve' => (int) $prices['self_serve'], 'full_service' => (int) $prices['full_service']];
    }

    /**
     * The table rows, label => [self-serve, full service].
     *
     * @return list<array{label: string, self_serve: string, full_service: string}>
     */
    public static function rows(string $kind, string $state, ?string $countyKey = null): array
    {
        $state = strtoupper($state);
        $recorded = in_array($kind, CountyRecordingFee::KINDS, true);
        $requirements = LienDocumentRegistry::isSupported($state)
            ? LienDocumentRegistry::signatureRequirements($state, $countyKey, $kind)
            : LienDocumentRegistry::SIGNATURE_REQUIREMENTS;

        $rows = [
            self::row('Who prepares it', 'We do, from your answers.', 'We do, and our team checks it before it goes out.'),
            self::row('Who signs it',
                NotarizationInstructions::needsNotary($requirements, $state, $kind)
                    ? 'You print it and sign it in front of a notary.'
                    : 'You print it and sign it.',
                match (NotarizationInstructions::keyFor($requirements, $state, $kind, 'full_service')) {
                    NotarizationInstructions::ESIGN => 'You sign it online. No printing or notary.',
                    NotarizationInstructions::NOTARIZE_AND_UPLOAD => 'You sign it in front of a notary and upload a scan.',
                    default => 'You sign it in front of a notary and overnight the original to us.',
                },
            ),
        ];

        if (! $recorded) {
            return [
                ...$rows,
                self::row('Who sends it', 'You mail or deliver it.', 'We mail it by certified mail.'),
                self::row('What you get back', 'The document as a PDF, with sending instructions.', 'Proof of mailing.'),
            ];
        }

        $fee = CountyRecordingFee::cents($state, $countyKey, $kind);
        $turnaround = RecordingTurnaround::words($state, $countyKey);

        return [
            ...$rows,
            self::row('Who records it',
                'You record it with the county.',
                $kind === 'mechanics_lien'
                    ? 'We record it with the county and send copies to every party the law requires.'
                    : 'We record it with the county.',
            ),
            self::row('County recording fee',
                'You pay the county when you record it.',
                $fee !== null ? '$'.number_format($fee / 100, 2).', added at checkout.' : 'Billed at cost after recording.',
            ),
            self::row('Typical turnaround',
                $turnaround !== null ? "Typically {$turnaround} after you submit." : 'Not known for this county.',
                $turnaround !== null ? "Typically {$turnaround} after we submit." : 'Not known for this county.',
            ),
            self::row('What you get back',
                'The document as a PDF, with filing instructions.',
                $kind === 'mechanics_lien' ? 'The recorded copy and proof of mailing.' : 'The recorded copy.',
            ),
        ];
    }

    /** @return array{label: string, self_serve: string, full_service: string} */
    private static function row(string $label, string $selfServe, string $fullService): array
    {
        return ['label' => $label, 'self_serve' => $selfServe, 'full_service' => $fullService];
    }
}
