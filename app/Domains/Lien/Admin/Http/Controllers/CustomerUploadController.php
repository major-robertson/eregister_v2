<?php

namespace App\Domains\Lien\Admin\Http\Controllers;

use App\Domains\Lien\Models\LienFiling;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Admin download of a file the customer sent from their filing page (the
 * customer_uploads collection: answers' files and signed documents).
 * Authorizes via the filing's view policy (admins bypass through
 * Gate::before) and redirects to a short-lived temporary S3 URL.
 */
class CustomerUploadController
{
    public function download(string $publicId, string $media): RedirectResponse
    {
        // Resolved by hand so the business global scope does not hide other
        // businesses' filings from admins (as LienFilingDetail::mount does).
        $filing = LienFiling::withoutGlobalScope('business')
            ->withTrashed()
            ->where('public_id', $publicId)
            ->firstOrFail();

        Gate::authorize('view', $filing);

        $file = $filing->media()
            ->where('collection_name', 'customer_uploads')
            ->whereKey((int) $media)
            ->firstOrFail();

        return redirect($file->getTemporaryUrl(now()->addMinutes(5)));
    }
}
