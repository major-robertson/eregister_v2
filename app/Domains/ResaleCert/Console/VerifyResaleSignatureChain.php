<?php

namespace App\Domains\ResaleCert\Console;

use App\Domains\Esign\Support\SignatureChainHasher;
use App\Domains\ResaleCert\Actions\AppendResaleSignatureEvent;
use App\Domains\ResaleCert\Models\ResaleSignatureEvent;
use Illuminate\Console\Command;

/**
 * Recomputes every resale signature event hash and verifies chain linkage
 * per business — detects any tampering with the append-only audit log.
 * Mirrors the Esign domain's esign:verify-chain.
 */
class VerifyResaleSignatureChain extends Command
{
    protected $signature = 'resale-cert:verify-chain
                            {--business= : Verify a single business id}';

    protected $description = 'Verify the resale certificate signature audit chain integrity';

    public function handle(SignatureChainHasher $hasher): int
    {
        $query = ResaleSignatureEvent::query()->orderBy('business_id')->orderBy('id');

        if ($this->option('business')) {
            $query->where('business_id', $this->option('business'));
        }

        $failures = 0;
        $checked = 0;
        $previousHashByBusiness = [];

        foreach ($query->cursor() as $event) {
            $checked++;
            $expectedPrevious = $previousHashByBusiness[$event->business_id] ?? null;

            if ($event->previous_event_hash !== $expectedPrevious) {
                $failures++;
                $this->error("Event {$event->id}: broken chain linkage (expected previous ".($expectedPrevious ?? 'null').', stored '.($event->previous_event_hash ?? 'null').')');
            }

            $fields = AppendResaleSignatureEvent::canonicalFields(
                previousEventHash: $event->previous_event_hash,
                businessId: $event->business_id,
                signatureId: $event->resale_signature_id,
                certificateId: $event->resale_certificate_id,
                eventType: $event->event_type->value,
                actorUserId: $event->actor_user_id,
                ip: $event->ip_address,
                userAgent: $event->user_agent,
                occurredAt: $event->occurred_at,
                metadata: $event->metadata_json ?? [],
            );

            if ($hasher->hash($fields) !== $event->event_hash) {
                $failures++;
                $this->error("Event {$event->id}: hash mismatch — row has been modified");
            }

            $previousHashByBusiness[$event->business_id] = $event->event_hash;
        }

        if ($failures > 0) {
            $this->error("{$failures} integrity failure(s) across {$checked} events.");

            return self::FAILURE;
        }

        $this->info("Chain intact: {$checked} events verified.");

        return self::SUCCESS;
    }
}
