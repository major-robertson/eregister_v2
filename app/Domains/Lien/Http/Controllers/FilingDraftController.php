<?php

namespace App\Domains\Lien\Http\Controllers;

use App\Domains\Lien\Models\LienFiling;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The draft sent for review, shown inline on the customer's filing page
 * (EREG-140): the "Review your draft" panel embeds it and links to it. The
 * route is signed, and FilingShow signs it only for the draft under review,
 * so other draft versions (not sent yet) can't be reached by changing the id.
 */
class FilingDraftController
{
    public function show(Request $request, LienFiling $filing, string $media): StreamedResponse
    {
        Gate::authorize('view', $filing);

        $draft = $filing->media()
            ->where('collection_name', 'drafts')
            ->whereKey((int) $media)
            ->firstOrFail();

        return $draft->toInlineResponse($request);
    }
}
