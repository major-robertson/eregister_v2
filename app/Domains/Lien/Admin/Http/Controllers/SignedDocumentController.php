<?php

namespace App\Domains\Lien\Admin\Http\Controllers;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Models\SignatureDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * Admin download of a signed letter. Authorizes via the signable's view policy
 * (admins bypass through Gate::before), logs an access event into the audit
 * chain, and redirects to a short-lived temporary S3 URL.
 */
class SignedDocumentController
{
    public function __construct(private readonly AppendSignatureEvent $events) {}

    public function download(string $publicId): RedirectResponse
    {
        $document = SignatureDocument::with('signatureRequest.signable')
            ->where('public_id', $publicId)
            ->firstOrFail();

        $request = $document->signatureRequest;

        abort_if($request->signable === null, 404);
        Gate::authorize('view', $request->signable);

        $media = $document->signedMedia();
        abort_unless($media !== null, 404, 'Signed document not available.');

        $this->events->execute($request, SignatureEventType::DocumentDownloaded,
            document: $document,
            actorType: 'admin',
            actorUserId: auth()->id(),
            ip: request()->ip(),
            userAgent: request()->userAgent(),
            metadata: [
                'actor_role' => 'admin',
                'document_id' => $document->document_identifier,
                'media_collection' => SignatureDocument::COLLECTION_SIGNED,
            ],
        );

        return redirect($media->getTemporaryUrl(now()->addMinutes(5)));
    }
}
