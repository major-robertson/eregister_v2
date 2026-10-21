<?php

namespace App\Domains\Esign\Support;

use App\Domains\Esign\Contracts\SignatureContext;

/**
 * The facts printed on a signed PDF: the electronic signature block and the
 * Certificate of Completion (documents.lien._signature-block and
 * documents.lien._esign-certificate). Shared by the signed demand letter and
 * the stamped uploaded lien document so both print the same wording.
 */
final class SignatureCertificateData
{
    /**
     * @return array{name: string, image: ?string, signed_at_eastern: string, signed_at_utc: string, signature_id: string}
     */
    public static function signatureBlock(SignatureContext $context): array
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
    public static function certificate(SignatureContext $context): array
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
