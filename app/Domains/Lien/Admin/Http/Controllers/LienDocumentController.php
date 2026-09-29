<?php

namespace App\Domains\Lien\Admin\Http\Controllers;

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentPackage;
use App\Domains\Lien\Documents\LienDocumentUnavailable;
use App\Domains\Lien\Documents\LienServiceDocumentGenerator;
use App\Domains\Lien\Documents\ResolvedLienDocument;
use App\Domains\Lien\Enums\LienPackageDocument;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienFilingRecipient;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class LienDocumentController
{
    public function __construct(
        private readonly LienDocumentGenerator $generator,
        private readonly LienServiceDocumentGenerator $service,
    ) {}

    /**
     * Generate and stream one piece of a filing's document package: the
     * instrument or notice itself, or a service document (proof of service
     * and cover letter per recipient, the label sheet, the filing cover
     * sheet). Everything renders from live data; nothing is stored.
     */
    public function download(string $publicId, string $document, ?string $recipient = null): PdfBuilder
    {
        $filing = $this->resolveFiling($publicId);

        Gate::authorize('view', $filing);
        $form = $this->form($filing);

        $document = LienPackageDocument::tryFrom($document);
        abort_if($document === null, 404);

        $recipientModel = null;

        if ($document->perRecipient()) {
            abort_if($recipient === null, 404);
            $recipientModel = $filing->recipients->firstWhere('id', (int) $recipient);
            abort_if($recipientModel === null, 404);
        } else {
            abort_unless($recipient === null, 404);
        }

        return $this->render($filing, $form, $document, $recipientModel)
            ->download($this->service->filename($filing, $form, $document, $recipientModel));
    }

    /**
     * The whole package in one ZIP: the main document, a proof of service
     * and cover letter per recipient, the labels and the filing cover sheet.
     * Built in a temp file that is deleted once it has been sent.
     */
    public function zip(string $publicId): BinaryFileResponse
    {
        $filing = $this->resolveFiling($publicId);

        Gate::authorize('view', $filing);
        $form = $this->form($filing);

        $package = LienDocumentPackage::forFiling($filing);
        abort_unless($package->isAvailable(), 404);

        $path = tempnam(sys_get_temp_dir(), 'lien-package-');
        $zip = new ZipArchive;
        abort_unless($zip->open($path, ZipArchive::OVERWRITE) === true, 500, 'Could not create the ZIP archive.');

        $used = [];

        foreach ($package->items as $item) {
            $recipient = $item['recipient'] === null ? null : $filing->recipients->firstWhere('id', $item['recipient']);
            $name = $this->service->filename($filing, $form, $item['document'], $recipient);

            // Two recipients with the same name would collide; number the repeats.
            if (isset($used[$name])) {
                $name = (string) preg_replace('/\.pdf$/', ' ('.(++$used[$name]).').pdf', $name);
            } else {
                $used[$name] = 1;
            }

            $zip->addFromString($name, base64_decode($this->render($filing, $form, $item['document'], $recipient)->base64()));
        }

        $zip->close();

        return response()
            ->download($path, $this->service->zipFilename($filing, $form), ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    private function form(LienFiling $filing): ResolvedLienDocument
    {
        abort_unless($filing->hasGeneratedDocuments(), 404);

        try {
            $form = $this->generator->resolve($filing);
        } catch (LienDocumentUnavailable) {
            abort(404);
        }

        abort_unless(view()->exists($form->body), 404);

        return $form;
    }

    private function render(LienFiling $filing, ResolvedLienDocument $form, LienPackageDocument $document, ?LienFilingRecipient $recipient): PdfBuilder
    {
        return match ($document) {
            LienPackageDocument::Main => $this->generator->render($filing, $form),
            LienPackageDocument::ProofOfService => $this->service->proofOfService($filing, $recipient, $form),
            LienPackageDocument::CoverLetter => $this->service->coverLetter($filing, $recipient, $form),
            LienPackageDocument::Labels => $this->labels($filing, $form),
            LienPackageDocument::FilingCoverSheet => $this->filingCoverSheet($filing, $form),
        };
    }

    private function labels(LienFiling $filing, ResolvedLienDocument $form): PdfBuilder
    {
        abort_if($filing->recipients->isEmpty(), 404);

        return $this->service->labels($filing, $form);
    }

    private function filingCoverSheet(LienFiling $filing, ResolvedLienDocument $form): PdfBuilder
    {
        abort_unless($form->isInstrument() && ! empty($form->recording['cover_sheet']), 404);

        return $this->service->filingCoverSheet($filing, $form);
    }

    /**
     * Resolve the filing manually (rather than via implicit binding) so the
     * business global scope doesn't hide other businesses' filings from admins,
     * mirroring DemandLetterController. Everything the payload reads is eager-loaded.
     */
    private function resolveFiling(string $publicId): LienFiling
    {
        return LienFiling::withoutGlobalScope('business')
            ->withTrashed()
            ->with([
                'documentType',
                'project.business',
                'project.parties',
                'recipients' => fn ($query) => $query->withoutGlobalScope('business')->with('party'),
            ])
            ->where('public_id', $publicId)
            ->firstOrFail();
    }
}
