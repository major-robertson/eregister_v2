<?php

namespace App\Domains\Esign\Http\Controllers;

use App\Domains\Esign\Actions\AppendSignatureEvent;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\GuestSignerSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Lets the signer download their own signed letters. Logs the access into the
 * audit chain and redirects to a short-lived temporary S3 URL. Also serves the
 * locked (unsigned) PDF inline for the review screen's preview, for document
 * types that show one (uploaded lien documents).
 */
class SignerDownloadController
{
    public function __construct(private readonly AppendSignatureEvent $events) {}

    public function download(SignatureRequest $request, SignatureDocument $document): RedirectResponse
    {
        $this->authorizeSigner($request, $document);

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

    /**
     * The locked PDF the signer is asked to sign, inline. Not logged per
     * load: the review screen records Document viewed once.
     */
    public function preview(Request $http, SignatureRequest $request, SignatureDocument $document): StreamedResponse
    {
        $this->authorizeSigner($request, $document);

        abort_unless($request->policy()->showsInlinePreview(), 404);

        if (! $request->isCompleted()) {
            abort_if($request->status === SignatureRequestStatus::Voided, 410, 'This signing request has been voided.');
            abort_if($request->isExpired(), 410, 'This signing link has expired.');
        }

        $media = $document->lockedMedia();
        abort_unless($media !== null, 404, 'Document not available.');

        return $media->toInlineResponse($http);
    }

    private function authorizeSigner(SignatureRequest $request, SignatureDocument $document): void
    {
        if ($request->isGuest()) {
            abort_unless(GuestSignerSession::isVerified($request), 403);
        } else {
            abort_unless(auth()->id() === $request->signer_user_id, 403);
        }

        abort_unless($document->signature_request_id === $request->id, 404);
    }
}
