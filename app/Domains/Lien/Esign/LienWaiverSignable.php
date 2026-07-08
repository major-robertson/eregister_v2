<?php

namespace App\Domains\Lien\Esign;

use App\Domains\Esign\Contracts\Signable;
use App\Domains\Esign\Contracts\SignableDocument;
use App\Domains\Esign\Contracts\SignatureContext;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Documents\WaiverGenerator;
use App\Domains\Lien\Documents\WaiverSignedGenerator;
use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Mail\WaiverCompleted;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Makes a LienWaiver signable. Exactly one document per session (the waiver).
 * The signer depends on direction: provide = the business's own user signs
 * their waiver (account mode); collect = the counterparty vendor signs as a
 * guest (email-code identity, no account).
 */
class LienWaiverSignable implements Signable
{
    public const DOCUMENT_TYPE = 'lien_waiver';

    public function __construct(
        private readonly LienWaiver $waiver,
        private readonly WaiverGenerator $generator,
        private readonly WaiverSignedGenerator $signedGenerator,
    ) {}

    public function model(): Model
    {
        return $this->waiver;
    }

    /**
     * Null for collect-direction waivers: the vendor signs as a guest.
     */
    public function signer(): ?User
    {
        return $this->waiver->direction === WaiverDirection::Provide
            ? $this->waiver->createdBy
            : null;
    }

    public function businessId(): ?int
    {
        return $this->waiver->business_id;
    }

    public function documentTypeKey(): string
    {
        return self::DOCUMENT_TYPE;
    }

    public function title(): string
    {
        return config('esign.document_types.lien_waiver.title', 'Lien Waiver');
    }

    /**
     * @return list<SignableDocument>
     */
    public function documents(): array
    {
        $payload = $this->renderPayload();
        $project = $this->waiver->project?->name;

        return [new SignableDocument(
            label: $payload['form']['title'].($project ? " / {$project}" : ''),
            recipientRef: (string) $this->waiver->id,
            renderPayload: $payload,
            sortOrder: 0,
        )];
    }

    public function snapshotMeta(): array
    {
        return [
            'waiver_public_id' => $this->waiver->public_id,
            'document_type' => self::DOCUMENT_TYPE,
            'template' => $this->renderPayload()['form']['template'],
            'template_version' => $this->renderPayload()['form']['template_version'],
            'generator' => class_basename(WaiverGenerator::class),
        ];
    }

    public function renderUnsigned(SignableDocument $document): string
    {
        return $this->generator->renderFromSnapshot($document->renderPayload)->generatePdfContent();
    }

    public function renderSigned(SignableDocument $document, SignatureContext $context): string
    {
        return $this->signedGenerator->render($document->renderPayload, $context)->generatePdfContent();
    }

    public function postSignRedirectRoute(SignatureRequest $request): string
    {
        return route('esign.sign.done', ['request' => $request->public_id]);
    }

    public function onCompleted(SignatureRequest $request): void
    {
        $waiver = $this->waiver;
        $signedAt = Carbon::now();

        $rules = WaiverStateRegistry::for($waiver->state);
        $deemedDays = $rules['deemed_effective_days'];

        $waiver->update([
            'status' => WaiverStatus::Signed,
            'signed_at' => $signedAt,
            // GA/MS: a signed waiver becomes conclusively effective N days
            // after execution unless payment arrives or an Affidavit of
            // Nonpayment is filed; surfaced as a dashboard countdown. Anchored
            // on the Eastern calendar date (matching the paper-upload path and
            // how signed_at is displayed) so an evening signing isn't a day off.
            'deemed_effective_at' => $deemedDays !== null ? $signedAt->copy()->eastern()->addDays($deemedDays)->toDateString() : null,
        ]);

        // Both parties get the signed copy: the counterparty and the business.
        // Dedup case-insensitively so one inbox that appears as both signer and
        // creator doesn't get two copies.
        $recipients = array_filter(array_unique(array_map(
            fn (?string $email) => $email === null ? null : mb_strtolower($email),
            [$waiver->counterparty_email, $waiver->signer_email, $waiver->createdBy?->email],
        )));

        // A queue-push failure must not abort finalization; the signed PDFs are
        // already stored and the request is Completed. Log and continue.
        foreach ($recipients as $email) {
            try {
                Mail::to($email)->queue(new WaiverCompleted($waiver, $request));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function renderPayload(): array
    {
        return $this->waiver->render_snapshot_json ?: $this->generator->data($this->waiver);
    }
}
