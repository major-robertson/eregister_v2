<?php

namespace App\Domains\Esign\Actions;

use App\Domains\Esign\Enums\SignatureEventType;
use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureEvent;
use App\Domains\Esign\Models\SignatureRequest;
use App\Domains\Esign\Support\SignatureChainHasher;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The single writer for the append-only, hash-chained audit log. Locks the
 * chain tail so concurrent appends can't share a previous_event_hash, then
 * computes event_hash = sha256(canonical(previous_hash, ...this event)).
 */
class AppendSignatureEvent
{
    public function __construct(private readonly SignatureChainHasher $hasher) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        SignatureRequest $request,
        SignatureEventType $type,
        ?SignatureDocument $document = null,
        ?string $actorType = null,
        ?int $actorUserId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        array $metadata = [],
        ?CarbonInterface $occurredAt = null,
    ): SignatureEvent {
        return DB::transaction(function () use (
            $request, $type, $document, $actorType, $actorUserId, $ip, $userAgent, $metadata, $occurredAt
        ): SignatureEvent {
            $previous = SignatureEvent::query()
                ->where('signature_request_id', $request->id)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $previousHash = $previous?->event_hash;
            $occurredAt ??= Carbon::now();

            $fields = $this->hasher->fields(
                previousEventHash: $previousHash,
                signatureRequestId: $request->id,
                signatureDocumentId: $document?->id,
                eventType: $type->value,
                actorType: $actorType,
                actorUserId: $actorUserId,
                ip: $ip,
                userAgent: $userAgent,
                occurredAt: $occurredAt,
                metadata: $metadata,
            );

            return SignatureEvent::create([
                'signature_request_id' => $request->id,
                'signature_document_id' => $document?->id,
                'event_type' => $type,
                'actor_type' => $actorType,
                'actor_user_id' => $actorUserId,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'occurred_at' => $occurredAt,
                'metadata_json' => $metadata === [] ? null : $metadata,
                'previous_event_hash' => $previousHash,
                'event_hash' => $this->hasher->hash($fields),
            ]);
        });
    }
}
