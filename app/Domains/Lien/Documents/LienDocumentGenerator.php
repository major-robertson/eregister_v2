<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Models\LienFiling;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * Renders a filing's main document (preliminary notice, notice of intent,
 * mechanics lien or release) through the layout its family uses: the
 * recordable-instrument shell or the notice-letter shell. Same
 * data / filename / render shape as DemandLetterGenerator; DOMPDF only, and
 * streamed, never stored.
 */
class LienDocumentGenerator
{
    public function __construct(private readonly LienDocumentResolver $resolver) {}

    public function resolve(LienFiling $filing, ?string $kind = null): ResolvedLienDocument
    {
        return $this->resolver->resolve($filing, $kind);
    }

    public function render(LienFiling $filing, ?ResolvedLienDocument $form = null): PdfBuilder
    {
        $form ??= $this->resolve($filing);

        return Pdf::view($this->layout($form), ['doc' => $this->data($filing, $form)])
            ->driver('dompdf')
            ->format('letter');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(LienFiling $filing, ?ResolvedLienDocument $form = null): array
    {
        return LienDocumentPayload::fromFiling($filing, $form ?? $this->resolve($filing));
    }

    public function layout(ResolvedLienDocument $form): string
    {
        return $form->isInstrument()
            ? 'documents.lien.instruments.shell'
            : 'documents.lien.letters.shell';
    }

    /**
     * "{Claimant} {Title} {YYYY-MM-DD}.pdf", the way staff name the working
     * files, reduced to filename-safe characters.
     */
    public function filename(LienFiling $filing, ResolvedLienDocument $form): string
    {
        $project = $filing->project;
        $claimant = $project?->parties?->first(fn ($party) => $party->role->value === 'claimant')?->displayName()
            ?: $project?->business?->name
            ?: 'Claimant';

        $name = "{$claimant} {$form->title} ".now()->eastern()->format('Y-m-d');
        $name = str_replace(["'", "\u{2019}"], '', $name);
        $name = (string) preg_replace('/[^\w .-]+/u', ' ', $name);
        $name = trim((string) preg_replace('/\s+/', ' ', $name));

        return ($name === '' ? 'document' : $name).'.pdf';
    }
}
