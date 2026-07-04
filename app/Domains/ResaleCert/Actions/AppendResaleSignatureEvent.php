<?php

namespace App\Domains\ResaleCert\Actions;

use App\Domains\Business\Models\Business;
use App\Domains\Esign\Support\SignatureChainHasher;
use App\Domains\ResaleCert\Enums\ResaleSignatureEventType;
use App\Domains\ResaleCert\Models\ResaleSignatureEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The single writer for the resale-cert audit log. Locks the business's chain
 * tail so concurrent appends can't share a previous_event_hash, then computes
 * event_hash = sha256(canonical(previous_hash, ...this event)) using the same
 * canonicalization as the Esign chain.
 */
class AppendResaleSignatureEvent
{
    public const CHAIN_VERSION = 1;

    public function __construct(private readonly SignatureChainHasher $hasher) {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function execute(
        Business|int $business,
        ResaleSignatureEventType $type,
        ?int $signatureId = null,
        ?int $certificateId = null,
        ?int $actorUserId = null,
        ?string $ip = null,
        ?string $userAgent = null,
        array $metadata = [],
        ?CarbonInterface $occurredAt = null,
    ): ResaleSignatureEvent {
        $businessId = $business instanceof Business ? $business->id : $business;

        return DB::transaction(function () use (
            $businessId, $type, $signatureId, $certificateId, $actorUserId, $ip, $userAgent, $metadata, $occurredAt
        ): ResaleSignatureEvent {
            $previous = ResaleSignatureEvent::query()
                ->where('business_id', $businessId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $previousHash = $previous?->event_hash;
            $occurredAt ??= Carbon::now();

            $fields = self::canonicalFields(
                previousEventHash: $previousHash,
                businessId: $businessId,
                signatureId: $signatureId,
                certificateId: $certificateId,
                eventType: $type->value,
                actorUserId: $actorUserId,
                ip: $ip,
                userAgent: $userAgent,
                occurredAt: $occurredAt,
                metadata: $metadata,
            );

            return ResaleSignatureEvent::create([
                'business_id' => $businessId,
                'event_type' => $type,
                'resale_signature_id' => $signatureId,
                'resale_certificate_id' => $certificateId,
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

    /**
     * The canonical field set that gets hashed — shared with the verify
     * command so recomputation reproduces the stored hash byte-for-byte.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public static function canonicalFields(
        ?string $previousEventHash,
        int $businessId,
        ?int $signatureId,
        ?int $certificateId,
        string $eventType,
        ?int $actorUserId,
        ?string $ip,
        ?string $userAgent,
        CarbonInterface $occurredAt,
        array $metadata,
    ): array {
        return [
            'v' => self::CHAIN_VERSION,
            'chain' => 'resale_cert',
            'previous_event_hash' => $previousEventHash,
            'business_id' => $businessId,
            'resale_signature_id' => $signatureId,
            'resale_certificate_id' => $certificateId,
            'event_type' => $eventType,
            'actor_user_id' => $actorUserId,
            'ip' => $ip,
            'ua' => $userAgent,
            'occurred_at_utc' => $occurredAt->utc()->format('Y-m-d\TH:i:s\Z'),
            'metadata' => $metadata,
        ];
    }
}
