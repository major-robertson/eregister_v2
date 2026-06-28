<?php

namespace App\Domains\Esign\Actions;

use App\Domains\Esign\Models\SignatureEvent;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\ChainVerificationResult;
use App\Domains\Esign\Support\SignatureChainHasher;

/**
 * Walks a request's audit log in order and confirms the hash chain is intact:
 * genesis has a null previous hash, each link points at the prior event's hash,
 * and every recomputed event_hash matches the stored one (no row was tampered).
 */
class VerifySignatureChain
{
    public function __construct(private readonly SignatureChainHasher $hasher) {}

    public function execute(SignatureRequest $request): ChainVerificationResult
    {
        $events = SignatureEvent::query()
            ->where('signature_request_id', $request->id)
            ->orderBy('id')
            ->get();

        $previousHash = null;

        foreach ($events as $index => $event) {
            if ($event->previous_event_hash !== $previousHash) {
                return new ChainVerificationResult(
                    valid: false,
                    eventCount: $events->count(),
                    brokenAtEventId: $event->id,
                    reason: $index === 0
                        ? 'Genesis event must have a null previous hash.'
                        : 'Broken chain: previous_event_hash does not match the prior event.',
                );
            }

            $expected = $this->hasher->hash($this->hasher->fields(
                previousEventHash: $event->previous_event_hash,
                signatureRequestId: $event->signature_request_id,
                signatureDocumentId: $event->signature_document_id,
                eventType: $event->event_type->value,
                actorType: $event->actor_type,
                actorUserId: $event->actor_user_id,
                ip: $event->ip_address,
                userAgent: $event->user_agent,
                occurredAt: $event->occurred_at,
                metadata: $event->metadata_json ?? [],
            ));

            if (! hash_equals($event->event_hash, $expected)) {
                return new ChainVerificationResult(
                    valid: false,
                    eventCount: $events->count(),
                    brokenAtEventId: $event->id,
                    reason: 'Event hash mismatch — this row was altered after it was written.',
                );
            }

            $previousHash = $event->event_hash;
        }

        return new ChainVerificationResult(valid: true, eventCount: $events->count());
    }
}
