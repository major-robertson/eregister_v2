<?php

namespace App\Domains\Lien\Http\Controllers;

use App\Domains\Lien\Models\LienWaiver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Streams a waiver PDF via a short-lived signed S3 URL. ?copy=signed serves
 * the executed copy (an uploaded file, or the e-sign output living on the
 * signature request's document); anything else serves the unsigned original.
 */
class WaiverDownloadController
{
    public function download(Request $request, LienWaiver $waiver): RedirectResponse
    {
        // Route binding is already pinned to the current business by the
        // BelongsToBusiness global scope; this is belt and suspenders.
        abort_unless($waiver->business_id === $request->user()?->currentBusiness()?->id, 404);

        $media = $request->query('copy') === 'signed'
            ? $this->signedMedia($waiver)
            : $waiver->getFirstMedia('generated');

        if (! $media) {
            abort(404, 'Document not ready');
        }

        // Return temporary signed URL (expires in 5 minutes)
        return redirect($media->getTemporaryUrl(now()->addMinutes(5)));
    }

    /**
     * The signed copy lives on the waiver itself when it was uploaded, or on
     * a signature request's document when it came through e-sign. The e-sign
     * lookup spans all requests, not just the latest; a void + re-send after
     * completion must not hide the earlier executed copy.
     */
    private function signedMedia(LienWaiver $waiver): ?Media
    {
        return $waiver->getFirstMedia('signed')
            ?: $waiver->latestSignedDocument()?->signedMedia();
    }
}
