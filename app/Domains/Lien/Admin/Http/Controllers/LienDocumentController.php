<?php

namespace App\Domains\Lien\Admin\Http\Controllers;

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentUnavailable;
use App\Domains\Lien\Documents\LienServiceDocumentGenerator;
use App\Domains\Lien\Enums\LienPackageDocument;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPdf\PdfBuilder;

class LienDocumentController
{
    /**
     * Generate and stream one piece of a filing's document package: the
     * instrument or notice itself, or a service document (proof of service
     * and cover letter per recipient, the label sheet, the filing cover
     * sheet). Everything renders from live data; nothing is stored.
     */
    public function download(
        LienDocumentGenerator $generator,
        LienServiceDocumentGenerator $service,
        string $publicId,
        string $document,
        ?string $recipient = null,
    ): PdfBuilder {
        $filing = $this->resolveFiling($publicId);

        Gate::authorize('view', $filing);
        abort_unless($filing->hasGeneratedDocuments(), 404);

        $document = LienPackageDocument::tryFrom($document);
        abort_if($document === null, 404);

        try {
            $form = $generator->resolve($filing);
        } catch (LienDocumentUnavailable) {
            abort(404);
        }

        abort_unless(view()->exists($form->body), 404);

        $recipientModel = null;

        if ($document->perRecipient()) {
            abort_if($recipient === null, 404);
            $recipientModel = $filing->recipients->firstWhere('id', (int) $recipient);
            abort_if($recipientModel === null, 404);
        } else {
            abort_unless($recipient === null, 404);
        }

        $pdf = match ($document) {
            LienPackageDocument::Main => $generator->render($filing, $form),
            LienPackageDocument::ProofOfService => $service->proofOfService($filing, $recipientModel, $form),
            LienPackageDocument::CoverLetter => $service->coverLetter($filing, $recipientModel, $form),
            LienPackageDocument::Labels => $this->labels($service, $filing, $form),
            LienPackageDocument::FilingCoverSheet => $this->filingCoverSheet($service, $filing, $form),
        };

        return $pdf->download($service->filename($filing, $form, $document, $recipientModel));
    }

    private function labels(LienServiceDocumentGenerator $service, LienFiling $filing, $form): PdfBuilder
    {
        abort_if($filing->recipients->isEmpty(), 404);

        return $service->labels($filing, $form);
    }

    private function filingCoverSheet(LienServiceDocumentGenerator $service, LienFiling $filing, $form): PdfBuilder
    {
        abort_unless($form->isInstrument() && ! empty($form->recording['cover_sheet']), 404);

        return $service->filingCoverSheet($filing, $form);
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
