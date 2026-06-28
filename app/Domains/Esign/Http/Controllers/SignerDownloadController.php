<?php

namespace App\Domains\Esign\Http\Controllers;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Lets the signer download their own signed letters. Logs the access into the
 * audit chain and redirects to a short-lived temporary S3 URL.
 */
class SignerDownloadController
{
    public function __construct(private readonly AppendSignatureEvent $events) {}

    public function download(SignatureRequest $request, SignatureDocument $document): RedirectResponse
    {
        abort_unless(auth()->id() === $request->signer_user_id, 403);
        abort_unless($document->signature_request_id === $request->id, 404);

        $media = $document->signedMedia();
        abort_unless($media !== null, 404, 'Signed document not available.');

        $this->events->execute($request, SignatureEventType::DocumentDownloaded,
            document: $document,
            actorType: 'signer',
            actorUserId: auth()->id(),
            ip: request()->ip(),
            userAgent: request()->userAgent(),
            metadata: [
                'actor_role' => 'signer',
                'document_id' => $document->document_identifier,
                'media_collection' => SignatureDocument::COLLECTION_SIGNED,
            ],
        );

        return redirect($media->getTemporaryUrl(now()->addMinutes(5)));
    }
}
