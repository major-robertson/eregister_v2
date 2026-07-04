<?php

namespace App\Domains\Esign\Actions;

use App\Domains\Esign\Contracts\SignableDocument;
use App\Domains\Esign\Contracts\SignatureContext;
use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Enums\SignatureRequestStatus;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\SignableResolver;
use App\Domains\Esign\Support\PdfBytes;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Applies the signer's single adoption to every document in the session and
 * generates the final signed PDFs. Like the send action, this is an idempotent
 * state machine — signed-PDF rendering + S3 uploads run OUTSIDE a wrapping
 * transaction, and a per-document loop skips already-signed letters so a partial
 * failure can resume. The filing only advances after every letter is stored.
 */
class CompleteSignature
{
    public function __construct(
        private readonly SignableResolver $resolver,
        private readonly AppendSignatureEvent $events,
    ) {}

    /**
     * @param  array<string, mixed>  $presentedText  Exact UI strings + document list the signer saw.
     * @param  \App\Models\UserSignature|null  $signature  The visual signature (drawn or typed-in-font) applied to the documents.
     */
    public function execute(SignatureRequest $request, User $signer, string $adoptedName, array $presentedText, ?\App\Models\UserSignature $signature = null): SignatureRequest
    {
        if ($request->isCompleted()) {
            return $request;
        }

        $signable = $this->resolver->for($request->signable);
        $ip = request()?->ip();
        $ua = request()?->userAgent();

        // Inline the signature PNG once so every signed PDF embeds identical
        // bytes and the audit metadata can hash them.
        $signatureImage = $signature?->imageDataUri();
        $signatureMethod = $signature?->esignMethod() ?? 'typed_name';

        // Record intent + adoption (short transaction).
        DB::transaction(function () use ($request, $signer, $adoptedName, $presentedText, $ip, $ua, $signature, $signatureMethod): void {
            $request->update([
                'adopted_name' => $adoptedName,
                'signature_method' => $signatureMethod,
                'user_signature_id' => $signature?->id,
                'email_verified_at_sign' => $signer->email_verified_at,
                'presented_text_json' => $presentedText,
                'status' => SignatureRequestStatus::Signing,
            ]);

            $this->events->execute($request, SignatureEventType::SignatureStarted,
                actorType: 'signer', actorUserId: $signer->id, ip: $ip, userAgent: $ua);

            $this->events->execute($request, SignatureEventType::SignatureCompleted,
                actorType: 'signer', actorUserId: $signer->id, ip: $ip, userAgent: $ua,
                metadata: array_filter([
                    'adopted_name' => $adoptedName,
                    'signature_method' => $signatureMethod,
                    'user_signature_id' => $signature?->id,
                    'signature_image_sha256' => $signature ? \App\Domains\Esign\Actions\AdoptSignature::imageSha256($signature) : null,
                ], fn ($value) => $value !== null));
        });

        // Render + store each signed letter (idempotent; outside a wrapping txn).
        foreach ($request->documents()->get() as $document) {
            if ($document->isSigned()) {
                continue;
            }

            $context = new SignatureContext(
                adoptedName: $adoptedName,
                signedAtUtc: Carbon::now(),
                signatureId: $document->document_identifier,
                request: $request,
                document: $document,
                signatureImageDataUri: $signatureImage,
                signatureMethod: $signatureMethod,
            );

            $bytes = $signable->renderSigned(SignableDocument::fromModel($document), $context);
            $hash = PdfBytes::sha256($bytes);
            $document->storeSigned($bytes, $hash);

            $this->events->execute($request, SignatureEventType::FinalPdfGenerated,
                document: $document, actorType: 'signer', actorUserId: $signer->id, ip: $ip, userAgent: $ua,
                metadata: ['sha256' => $hash, 'document_identifier' => $document->document_identifier]);
        }

        // Finalize only once every letter is signed + stored.
        $request->update([
            'status' => SignatureRequestStatus::Completed,
            'completed_at' => Carbon::now(),
        ]);

        $signable->onCompleted($request);

        return $request->refresh();
    }
}
