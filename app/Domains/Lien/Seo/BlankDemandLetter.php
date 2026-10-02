<?php

namespace App\Domains\Lien\Seo;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Ungated blank payment demand letter for "/liens/payment-demand-letter":
 * the letter DemandLetterGenerator renders for customers, through the same
 * template, with a ruled line in every field instead of a name, address,
 * amount or date.
 *
 * Cached on the local disk like the blank lien documents, under a key that
 * carries a hash of the letter templates, so a wording change re-renders it.
 */
class BlankDemandLetter
{
    public const VIEW = 'documents.lien.demand-letter';

    public const FILENAME = 'payment-demand-letter-blank.pdf';

    public function pdf(): string
    {
        $disk = Storage::disk('local');
        $path = $this->cachePath();

        if ($disk->exists($path)) {
            return (string) $disk->get($path);
        }

        $bytes = Pdf::view(self::VIEW, ['letter' => $this->data()])
            ->driver('dompdf')
            ->format('letter')
            ->generatePdfContent();

        $disk->put($path, $bytes);

        return $bytes;
    }

    /**
     * DemandLetterGenerator::data()'s shape with every field blank. The
     * template prints the "$" and the labels; each value is a ruled line.
     *
     * @return array<string, mixed>
     */
    public function data(): array
    {
        $line = fn (int $length) => str_repeat('_', $length);

        return [
            'date' => $line(22),
            'recipient' => [
                'name' => $line(36),
                'company' => $line(36),
                'address_lines' => [$line(36)],
            ],
            'salutation' => 'Dear '.$line(30).',',
            'amount' => $line(14),
            'start_date' => $line(22),
            'end_date' => $line(22),
            // Two ruled lines that wrap inside the 6.5in text block.
            'work' => implode(' ', [$line(70), $line(70)]),
            // Kept to four lines so the letter stays on one page, as the template expects.
            'sender' => [
                'name' => $line(36),
                'company' => $line(36),
                'address_lines' => [$line(36)],
                'phone' => $line(24),
                'email' => null,
            ],
        ];
    }

    public function cachePath(): string
    {
        $sources = array_map(
            fn (string $view) => (string) @file_get_contents(view()->getFinder()->find($view)),
            [self::VIEW, 'documents.lien._demand-letter-body'],
        );

        return sprintf(
            '%s/v%d/demand_letter/demand-letter-%s.pdf',
            BlankLienDocument::CACHE_DIRECTORY,
            BlankLienDocument::CACHE_VERSION,
            substr(md5(implode("\n", $sources)), 0, 8),
        );
    }
}
