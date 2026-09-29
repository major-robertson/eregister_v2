<?php

namespace App\Domains\Lien\Admin\Http\Controllers;

use App\Domains\Lien\Documents\LienDocumentGenerator;
use App\Domains\Lien\Documents\LienDocumentUnavailable;
use App\Domains\Lien\Enums\LienPackageDocument;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPdf\PdfBuilder;

class LienDocumentController
{
    /**
     * Generate and stream one piece of a filing's document package. Only the
     * main document (the instrument or notice itself) exists yet; the
     * per-recipient service documents land with the Recipients card.
     */
    public function download(LienDocumentGenerator $generator, string $publicId, string $document, ?string $recipient = null): PdfBuilder
    {
        $filing = $this->resolveFiling($publicId);

        Gate::authorize('view', $filing);
        abort_unless($filing->hasGeneratedDocuments(), 404);

        $document = LienPackageDocument::tryFrom($document);
        abort_unless($document === LienPackageDocument::Main && $recipient === null, 404);

        try {
            $form = $generator->resolve($filing);
        } catch (LienDocumentUnavailable) {
            abort(404);
        }

        abort_unless(view()->exists($form->body), 404);

        return $generator->render($filing, $form)
            ->download($generator->filename($filing, $form));
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
            ->with(['documentType', 'project.business', 'project.parties', 'recipients.party'])
            ->where('public_id', $publicId)
            ->firstOrFail();
    }
}
