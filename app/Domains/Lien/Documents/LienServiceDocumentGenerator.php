<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Enums\LienPackageDocument;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use InvalidArgumentException;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * The service set that travels with a generated lien document: a proof of
 * service (declaration, or notarized affidavit where the state wants one)
 * and a cover letter per recipient, the Avery 5160 label sheet, and the
 * mail-in filing cover sheet for clerks that take paper. All render from
 * the same payload as the main document and read each recipient's address
 * snapshot, never the live party.
 */
class LienServiceDocumentGenerator
{
    /** Avery 5160: 3 columns x 10 rows of 2.625in x 1in labels. */
    public const LABELS_PER_SHEET = 30;

    public const LABEL_LINES = 5;

    public function __construct(private readonly LienDocumentGenerator $documents) {}

    public function proofOfService(LienFiling $filing, LienFilingRecipient $recipient, ?ResolvedLienDocument $form = null): PdfBuilder
    {
        [$doc, $form] = $this->payload($filing, $form);

        return $this->pdf('documents.lien.service.proof-of-service', [
            'doc' => $doc,
            'recipient' => $this->recipientData($doc, $recipient),
            'affidavit' => ($form->service['proof'] ?? 'declaration') === 'affidavit',
            'perjuryState' => $this->perjuryStateName($doc, $form),
        ]);
    }

    public function coverLetter(LienFiling $filing, LienFilingRecipient $recipient, ?ResolvedLienDocument $form = null): PdfBuilder
    {
        [$doc] = $this->payload($filing, $form);

        return $this->pdf('documents.lien.service.cover-letter', [
            'doc' => $doc,
            'recipient' => $this->recipientData($doc, $recipient),
        ]);
    }

    public function labels(LienFiling $filing, ?ResolvedLienDocument $form = null): PdfBuilder
    {
        [$doc] = $this->payload($filing, $form);

        return $this->pdf('documents.lien.service.mailing-labels', [
            'doc' => $doc,
            'sheets' => array_chunk($this->labelSheet($doc), self::LABELS_PER_SHEET),
        ]);
    }

    public function filingCoverSheet(LienFiling $filing, ?ResolvedLienDocument $form = null): PdfBuilder
    {
        [$doc] = $this->payload($filing, $form);

        return $this->pdf('documents.lien.service.filing-cover-sheet', ['doc' => $doc]);
    }

    /**
     * "{Claimant} {Title} Proof of Service {Recipient} {YYYY-MM-DD}.pdf".
     */
    public function filename(LienFiling $filing, ResolvedLienDocument $form, LienPackageDocument $document, ?LienFilingRecipient $recipient = null): string
    {
        if ($document === LienPackageDocument::Main) {
            return $this->documents->filename($filing, $form);
        }

        $main = pathinfo($this->documents->filename($filing, $form), PATHINFO_FILENAME);
        $date = now()->eastern()->format('Y-m-d');
        $stem = substr($main, 0, -strlen(' '.$date));

        $suffix = match ($document) {
            LienPackageDocument::ProofOfService => 'Proof of Service',
            LienPackageDocument::CoverLetter => 'Cover Letter',
            LienPackageDocument::Labels => 'Labels',
            LienPackageDocument::FilingCoverSheet => 'Filing Cover Sheet',
        };

        $name = trim("{$stem} {$suffix} ".($recipient?->snapshotName() ?? '')." {$date}");
        $name = str_replace(["'", "\u{2019}"], '', $name);
        $name = (string) preg_replace('/[^\w .-]+/u', ' ', $name);
        $name = trim((string) preg_replace('/\s+/', ' ', $name));

        return "{$name}.pdf";
    }

    /**
     * "{Claimant} {Title} package {YYYY-MM-DD}.zip" for the whole set.
     */
    public function zipFilename(LienFiling $filing, ResolvedLienDocument $form): string
    {
        $main = pathinfo($this->documents->filename($filing, $form), PATHINFO_FILENAME);
        $date = now()->eastern()->format('Y-m-d');
        $stem = substr($main, 0, -strlen(' '.$date));

        return "{$stem} package {$date}.zip";
    }

    /**
     * Label text: each recipient followed by an eRegister return label, each
     * clamped to five lines because nothing on the sheet clips.
     *
     * @param  array<string, mixed>  $doc
     * @return list<list<string>>
     */
    public function labelSheet(array $doc): array
    {
        $return = array_values(array_filter(array_map('trim', (array) config('lien.documents.return_label_lines', []))));

        if ($return === []) {
            $return = array_values(array_filter(array_merge([$doc['preparer']['name']], $doc['preparer']['address_lines'])));
        }

        $labels = [];

        foreach ($doc['recipients'] as $recipient) {
            $contact = $recipient['company'] && $recipient['name'] && $recipient['name'] !== $recipient['company']
                ? 'Attn: '.$recipient['name']
                : null;

            $lines = array_values(array_filter(array_merge(
                [$recipient['company'] ?? $recipient['name'], $contact],
                $recipient['address_lines'],
            )));

            $labels[] = array_slice($lines, 0, self::LABEL_LINES);
            $labels[] = array_slice($return, 0, self::LABEL_LINES);
        }

        return $labels;
    }

    /**
     * @return array{0: array<string, mixed>, 1: ResolvedLienDocument}
     */
    private function payload(LienFiling $filing, ?ResolvedLienDocument $form): array
    {
        $form ??= $this->documents->resolve($filing);

        return [$this->documents->data($filing, $form), $form];
    }

    /**
     * @param  array<string, mixed>  $doc
     * @return array<string, mixed>
     */
    private function recipientData(array $doc, LienFilingRecipient $recipient): array
    {
        foreach ($doc['recipients'] as $data) {
            if ($data['id'] === $recipient->id) {
                return $data;
            }
        }

        throw new InvalidArgumentException('That recipient is not on this filing.');
    }

    /**
     * @param  array<string, mixed>  $doc
     */
    private function perjuryStateName(array $doc, ResolvedLienDocument $form): string
    {
        $code = strtoupper((string) ($form->service['perjury_state'] ?: $doc['server']['state']));

        return WaiverStateRegistry::STATE_NAMES[$code] ?? $code;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function pdf(string $view, array $data): PdfBuilder
    {
        return Pdf::view($view, $data)->driver('dompdf')->format('letter');
    }
}
