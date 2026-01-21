<?php

namespace App\Domains\Lien\Http\Controllers;

use App\Domains\Lien\Models\LienFiling;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class FilingDownloadController
{
    public function download(LienFiling $filing): RedirectResponse|Response
    {
        Gate::authorize('download', $filing);

        if (! $filing->isPaid()) {
            abort(403, 'Payment required');
        }

        $media = $filing->getFirstMedia('generated');

        if (! $media) {
            abort(404, 'Document not ready');
        }

        // Return temporary signed URL (expires in 5 minutes)
        return redirect($media->getTemporaryUrl(now()->addMinutes(5)));
    }
}
