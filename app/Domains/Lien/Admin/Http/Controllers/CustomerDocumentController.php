<?php

namespace App\Domains\Lien\Admin\Http\Controllers;

use App\Domains\Lien\Models\LienFiling;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Admin download of a contract, change order or invoice the customer
 * attached in the filing wizard (EREG-139): a short-lived temporary S3 URL,
 * for a file in that filing's customer_documents collection only.
 */
class CustomerDocumentController
{
    public function download(string $publicId, string $media): RedirectResponse
    {
        // Resolved by hand so the business global scope doesn't hide other
        // businesses' filings from admins, as in LienFilingDetail::mount.
        $filing = LienFiling::withoutGlobalScope('business')
            ->withTrashed()
            ->where('public_id', $publicId)
            ->firstOrFail();

        Gate::authorize('view', $filing);

        $document = $filing->getMedia(LienFiling::CUSTOMER_DOCUMENTS)->firstWhere('id', (int) $media);
        abort_if($document === null, 404);

        return redirect($document->getTemporaryUrl(now()->addMinutes(5)));
    }
}
