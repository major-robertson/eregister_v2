<?php

namespace App\Domains\Lien\Esign;

use App\Domains\Esign\Contracts\Signable;
use App\Domains\Esign\Contracts\SignableDocument;
use App\Domains\Esign\Contracts\SignatureContext;
use App\Domains\Esign\Exceptions\EsignException;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\PdfBytes;
use App\Domains\Lien\Documents\PdfSignatureStamper;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Makes a lien filing's uploaded draft signable (EREG-141): every filing that
 * is not a demand letter (prelim notice, NOI, mechanics lien, release). One
 * document: the newest PDF in the filing's `drafts` collection, frozen at send
 * time by media id, version, file name and SHA-256. The locked copy is the
 * stored PDF byte for byte; the signed copy is the locked copy with the
 * signature and certificate page appended (PdfSignatureStamper). The filing
 * creator signs, as for demand letters.
 */
class UploadedDocumentSignable implements Signable
{
    public const DOCUMENT_TYPE = 'uploaded_document';

    /** The filing's media collection that keeps each signed copy. */
    public const SIGNED_COLLECTION = 'signed';

    /** @var array{media: Media, bytes: string, sha256: string}|null */
    private ?array $draft = null;

    public function __construct(
        private readonly LienFiling $filing,
        private readonly PdfSignatureStamper $stamper,
    ) {}

    /**
     * Whether this filing's document can be e-signed (config
     * esign.uploaded_documents): the notice types listed there in every
     * state, mechanics liens and releases only in the listed states. Never a
     * demand letter (DemandLetterSignable handles those).
     */
    public static function isAllowedFor(LienFiling $filing): bool
    {
        $slug = $filing->documentType?->slug;

        if ($slug === null || $filing->isDemandLetter()) {
            return false;
        }

        $config = config('esign.uploaded_documents', []);

        if (in_array($slug, $config['document_types'] ?? [], true)) {
            return true;
        }

        $state = self::state($filing);
        $states = array_map('strtoupper', $config['lien_states'] ?? []);

        return $state !== null && in_array($state, $states, true);
    }

    /**
     * The one line the admin page shows when e-sign is off for this filing,
     * or null when it is allowed (and for demand letters).
     */
    public static function notAllowedReason(LienFiling $filing): ?string
    {
        if ($filing->isDemandLetter() || self::isAllowedFor($filing)) {
            return null;
        }

        $document = $filing->documentType?->name ?: 'this document';
        $state = self::state($filing);
        $where = $state !== null ? config("states.{$state}", $state) : 'this state';

        return "E-sign is not enabled for {$document} in {$where}. Use the notarization path.";
    }

    public function model(): Model
    {
        return $this->filing;
    }

    public function signer(): ?User
    {
        return $this->filing->createdBy;
    }

    public function businessId(): ?int
    {
        return $this->filing->business_id;
    }

    public function documentTypeKey(): string
    {
        return self::DOCUMENT_TYPE;
    }

    public function title(): string
    {
        return config('esign.document_types.uploaded_document.title', 'Lien document');
    }

    /**
     * The newest draft, or nothing when the filing has no draft yet.
     *
     * @return list<SignableDocument>
     */
    public function documents(): array
    {
        $draft = $this->newestDraft();

        if ($draft === null) {
            return [];
        }

        $version = (int) $draft['media']->getCustomProperty('version', 0);
        $name = $this->filing->documentType?->name ?: 'Lien document';

        return [new SignableDocument(
            label: "{$name} v{$version}",
            recipientRef: $this->filing->public_id,
            renderPayload: [
                'media_id' => $draft['media']->id,
                'version' => $version,
                'file_name' => $draft['media']->file_name,
                'sha256' => $draft['sha256'],
            ],
            sortOrder: 0,
        )];
    }

    public function snapshotMeta(): array
    {
        $draft = $this->newestDraft();

        return [
            'filing_public_id' => $this->filing->public_id,
            'document_type' => self::DOCUMENT_TYPE,
            'lien_document_type' => $this->filing->documentType?->slug,
            'media_id' => $draft['media']->id ?? null,
            'version' => $draft !== null ? (int) $draft['media']->getCustomProperty('version', 0) : null,
            'sha256' => $draft['sha256'] ?? null,
            'stamper' => class_basename(PdfSignatureStamper::class),
            'stamper_version' => PdfSignatureStamper::VERSION,
        ];
    }

    /**
     * The stored draft exactly as uploaded, checked against the hash frozen
     * when it was sent.
     */
    public function renderUnsigned(SignableDocument $document): string
    {
        $payload = $document->renderPayload;
        $mediaId = (int) ($payload['media_id'] ?? 0);
        $version = $payload['version'] ?? '?';

        if ($this->draft !== null && $this->draft['media']->id === $mediaId) {
            $bytes = $this->draft['bytes'];
        } else {
            $media = $this->filing->media()->where('collection_name', 'drafts')->whereKey($mediaId)->first();

            if ($media === null) {
                throw new EsignException("Draft v{$version} is no longer on this filing.");
            }

            $bytes = self::mediaBytes($media);
        }

        if (! hash_equals((string) ($payload['sha256'] ?? ''), PdfBytes::sha256($bytes))) {
            throw new EsignException("Draft v{$version} changed after it was sent for signature. Void the request and send it again.");
        }

        return $bytes;
    }

    /**
     * The locked copy the signer reviewed, with the signature and certificate
     * page appended.
     */
    public function renderSigned(SignableDocument $document, SignatureContext $context): string
    {
        return $this->stamper->stamp($this->lockedBytes($context->document), $context);
    }

    public function postSignRedirectRoute(SignatureRequest $request): string
    {
        return route('esign.sign.done', ['request' => $request->public_id]);
    }

    /**
     * Keep the signed copy on the filing (`signed` collection), move the filing
     * from Awaiting E-Signature to Needs Review, and log esign_completed with
     * the signed copy's media id (the customer's timeline links it).
     */
    public function onCompleted(SignatureRequest $request): void
    {
        $document = $request->documents()->first();
        $payload = $document?->document_snapshot_json['render'] ?? [];
        $signed = $document !== null ? $this->storeSignedCopy($request, $document, $payload) : null;

        // Mirror for the admin activity log and the customer's timeline
        // (logged before the status change so the timeline reads in order).
        $this->filing->events()->create([
            'business_id' => $this->filing->business_id,
            'event_type' => 'esign_completed',
            'payload_json' => [
                'signature_request_id' => $request->public_id,
                'documents' => $request->documents()->count(),
                'media_id' => $signed?->id,
                'version' => $payload['version'] ?? null,
                'file_name' => $signed?->file_name,
            ],
            'created_by' => $request->signer_user_id,
        ]);

        if ($this->filing->status === FilingStatus::AwaitingEsign
            && $this->filing->canTransitionTo(FilingStatus::NeedsReview)) {
            $this->filing->transitionTo(FilingStatus::NeedsReview, ['signature_request_id' => $request->public_id]);
        }
    }

    /**
     * Copy the signed PDF onto the filing once per request.
     *
     * @param  array<string, mixed>  $payload
     */
    private function storeSignedCopy(SignatureRequest $request, SignatureDocument $document, array $payload): ?Media
    {
        $existing = $this->filing->media()
            ->where('collection_name', self::SIGNED_COLLECTION)
            ->get()
            ->first(fn (Media $media) => $media->getCustomProperty('signature_request_id') === $request->public_id);

        if ($existing !== null) {
            return $existing;
        }

        $source = $document->signedMedia();

        if ($source === null) {
            return null;
        }

        $baseName = pathinfo((string) ($payload['file_name'] ?? ''), PATHINFO_FILENAME) ?: $document->document_identifier;

        return $this->filing->addMediaFromString(self::mediaBytes($source))
            ->usingFileName("{$baseName}-signed.pdf")
            ->usingName("{$document->label} (signed)")
            ->withCustomProperties([
                'signature_request_id' => $request->public_id,
                'signature_document_id' => $document->public_id,
                'document_identifier' => $document->document_identifier,
                'version' => $payload['version'] ?? null,
                'draft_media_id' => $payload['media_id'] ?? null,
                'sha256' => $document->signed_document_hash,
            ])
            ->toMediaCollection(self::SIGNED_COLLECTION);
    }

    /**
     * The newest draft with its bytes and hash, read once per adapter.
     *
     * @return array{media: Media, bytes: string, sha256: string}|null
     */
    private function newestDraft(): ?array
    {
        if ($this->draft === null) {
            $media = $this->filing->draftVersions()->first();

            if ($media === null) {
                return null;
            }

            $bytes = self::mediaBytes($media);
            $this->draft = ['media' => $media, 'bytes' => $bytes, 'sha256' => PdfBytes::sha256($bytes)];
        }

        return $this->draft;
    }

    private function lockedBytes(SignatureDocument $document): string
    {
        $media = $document->lockedMedia();

        if ($media === null) {
            throw new EsignException("Document {$document->document_identifier} has no locked copy to sign.");
        }

        $bytes = self::mediaBytes($media);

        if ($document->locked_document_hash !== null && ! hash_equals($document->locked_document_hash, PdfBytes::sha256($bytes))) {
            throw new EsignException("The locked copy of {$document->document_identifier} does not match its hash.");
        }

        return $bytes;
    }

    private static function mediaBytes(Media $media): string
    {
        $bytes = Storage::disk($media->disk)->get($media->getPathRelativeToRoot());

        if (! is_string($bytes) || $bytes === '') {
            throw new EsignException("The file {$media->file_name} could not be read.");
        }

        return $bytes;
    }

    private static function state(LienFiling $filing): ?string
    {
        $state = strtoupper(trim((string) ($filing->jurisdiction_state ?: $filing->project?->jobsite_state)));

        return $state !== '' ? $state : null;
    }
}
