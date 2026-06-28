<?php

namespace App\Domains\Lien\Esign;

use App\Domains\Esign\Contracts\Signable;
use App\Domains\Esign\Contracts\SignableDocument;
use App\Domains\Esign\Contracts\SignatureContext;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Lien\Documents\DemandLetterGenerator;
use App\Domains\Lien\Documents\DemandLetterSignedGenerator;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienParty;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * Makes a demand-letter LienFiling signable. One SignableDocument per recipient
 * (non-claimant party); the claimant (filing creator) signs once and that single
 * signature is applied to each letter. Rendering reads only the descriptor's
 * frozen payload, so signed output reproduces the exact locked letter.
 */
class DemandLetterSignable implements Signable
{
    public const DOCUMENT_TYPE = 'demand_letter';

    public const TEMPLATE_VERSION = 1;

    public function __construct(
        private readonly LienFiling $filing,
        private readonly DemandLetterGenerator $generator,
        private readonly DemandLetterSignedGenerator $signedGenerator,
    ) {}

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
        return config('esign.document_types.demand_letter.title', 'Payment Demand Letters');
    }

    /**
     * @return list<SignableDocument>
     */
    public function documents(): array
    {
        $recipients = $this->filing->project?->nonClaimantParties() ?? collect();

        return $recipients->values()->map(fn (LienParty $party, int $index): SignableDocument => new SignableDocument(
            label: $this->label($party),
            recipientRef: (string) $party->id,
            renderPayload: $this->generator->data($this->filing, $party),
            sortOrder: $index,
        ))->all();
    }

    public function snapshotMeta(): array
    {
        return [
            'filing_public_id' => $this->filing->public_id,
            'document_type' => self::DOCUMENT_TYPE,
            'template' => 'documents.lien.demand-letter',
            'template_version' => self::TEMPLATE_VERSION,
            'generator' => class_basename(DemandLetterGenerator::class),
        ];
    }

    public function renderUnsigned(SignableDocument $document): string
    {
        return Pdf::view('documents.lien.demand-letter', ['letter' => $document->renderPayload])
            ->driver('dompdf')
            ->format('letter')
            ->generatePdfContent();
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
        if ($this->filing->status === FilingStatus::AwaitingEsign
            && $this->filing->canTransitionTo(FilingStatus::NeedsReview)) {
            $this->filing->transitionTo(FilingStatus::NeedsReview, ['signature_request_id' => $request->public_id]);
        }

        // Lightweight mirror for the admin activity timeline.
        $this->filing->events()->create([
            'business_id' => $this->filing->business_id,
            'event_type' => 'esign_completed',
            'payload_json' => [
                'signature_request_id' => $request->public_id,
                'documents' => $request->documents()->count(),
            ],
            'created_by' => $request->signer_user_id,
        ]);
    }

    private function label(LienParty $party): string
    {
        $who = $party->displayName() ?: 'Unnamed party';
        $role = $party->role?->label() ?? 'Recipient';

        return "Demand Letter to {$who} ({$role})";
    }
}
