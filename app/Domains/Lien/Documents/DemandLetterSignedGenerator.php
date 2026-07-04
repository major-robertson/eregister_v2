<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Esign\Contracts\SignatureContext;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

/**
 * Renders the SIGNED Payment Demand Letter: the same letter body (from the
 * locked render snapshot, not live data) plus an electronic signature block and
 * an appended Certificate of Completion / audit page. Kept separate from
 * DemandLetterGenerator so that generator's small surface stays untouched.
 *
 * Pinned to the DOMPDF driver so it never depends on Chrome.
 */
class DemandLetterSignedGenerator
{
    /**
     * @param  array<string, mixed>  $letter  The frozen render payload (DemandLetterGenerator::data()).
     */
    public function render(array $letter, SignatureContext $context): PdfBuilder
    {
        return Pdf::view('documents.lien.demand-letter-signed', [
            'letter' => $letter,
            'signature' => $this->signatureBlock($context),
            'certificate' => $this->certificate($context),
        ])->driver('dompdf')->format('letter');
    }

    /**
     * @return array<string, mixed>
     */
    private function signatureBlock(SignatureContext $context): array
    {
        return [
            'name' => $context->adoptedName,
            // Drawn/typed-in-font signature PNG as a data URI (DOMPDF embeds
            // it inline); null falls back to the italic-serif typed name.
            'image' => $context->signatureImageDataUri,
            'signed_at_eastern' => $context->signedAtUtc->eastern()->format('M j, Y g:i A').' ET',
            'signed_at_utc' => $context->signedAtUtc->utc()->format('M j, Y g:i A').' UTC',
            'signature_id' => $context->signatureId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function certificate(SignatureContext $context): array
    {
        $request = $context->request;
        $document = $context->document;
        $consent = $request->consent;

        $events = $request->events()->with('actor')->orderBy('id')->get()->map(fn ($event) => [
            'label' => $event->event_type->label(),
            'at' => $event->occurred_at?->eastern()->format('M j, Y g:i:s A').' ET',
            'ip' => $event->ip_address,
            'actor' => $event->actor?->name ?? ucfirst((string) $event->actor_type),
            'document' => $event->meta('document_identifier'),
        ])->all();

        return [
            'document_identifier' => $document->document_identifier,
            'document_label' => $document->label,
            'signer_name' => $request->signer_name_snapshot ?: $request->adopted_name,
            'adopted_name' => $request->adopted_name,
            'signer_email' => $request->signer_email_snapshot,
            'signer_phone' => $request->signer_phone_snapshot,
            'email_verified_at' => $request->email_verified_at_sign?->utc()->format('M j, Y g:i A').' UTC',
            'signature_method' => str_replace('_', ' ', (string) $request->signature_method),
            'intent' => $request->intent_statement,
            'consent_version' => $consent?->version,
            'consent_scope' => $consent?->consent_scope,
            'consent_at' => $consent?->consented_at?->eastern()->format('M j, Y g:i A').' ET',
            'locked_hash' => $document->locked_document_hash,
            'request_public_id' => $request->public_id,
            'events' => $events,
        ];
    }
}
