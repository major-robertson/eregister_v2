<?php

namespace App\Domains\Lien\Admin\Http\Controllers;

use App\Domains\Lien\Documents\DemandLetterGenerator;
use App\Domains\Lien\Models\LienFiling;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPdf\PdfBuilder;

class DemandLetterController
{
    /**
     * Generate and stream a Payment Demand Letter PDF addressed to one recipient
     * party (any party on the project except the claimant, who is the sender).
     *
     * Returns the PdfBuilder (a Responsable); Laravel renders it to a PDF response
     * with the attachment headers set by ->download().
     */
    public function download(string $publicId, string $party, DemandLetterGenerator $generator): PdfBuilder
    {
        $filing = $this->resolveFiling($publicId);

        Gate::authorize('view', $filing);
        abort_unless($filing->isDemandLetter(), 404);

        $recipient = $filing->project?->nonClaimantParties()->firstWhere('id', (int) $party);
        abort_unless($recipient !== null, 404);

        return $generator->render($filing, $recipient)
            ->download($generator->filename($filing, $recipient));
    }

    /**
     * Generate one combined PDF with a letter for every non-claimant party
     * (one page each).
     */
    public function downloadAll(string $publicId, DemandLetterGenerator $generator): PdfBuilder
    {
        $filing = $this->resolveFiling($publicId);

        Gate::authorize('view', $filing);
        abort_unless($filing->isDemandLetter(), 404);

        $recipients = $filing->project?->nonClaimantParties() ?? collect();
        abort_if($recipients->isEmpty(), 404);

        return $generator->renderAll($filing, $recipients)
            ->download($generator->filename($filing));
    }

    /**
     * Resolve the filing manually (rather than via implicit binding) so the
     * business global scope doesn't hide other businesses' filings from admins —
     * mirroring LienFilingDetail::mount. Everything the generator reads is eager-loaded.
     */
    private function resolveFiling(string $publicId): LienFiling
    {
        return LienFiling::withoutGlobalScope('business')
            ->withTrashed()
            ->with(['documentType', 'project.business', 'project.parties'])
            ->where('public_id', $publicId)
            ->firstOrFail();
    }
}
