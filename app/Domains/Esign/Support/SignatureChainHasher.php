<?php

namespace App\Domains\Esign\Support;

use Carbon\CarbonInterface;

/**
 * Builds the canonical serialization for a signature_events row and hashes it.
 * Shared by AppendSignatureEvent (write) and VerifySignatureChain (read) so the
 * recomputed hash reproduces the stored one byte-for-byte.
 */
class SignatureChainHasher
{
    public const VERSION = 1;

    /**
     * The canonical field set that gets hashed. Order is irrelevant because
     * canonicalize() recursively sorts keys; what matters is that the same
     * inputs always serialize identically.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function fields(
        ?string $previousEventHash,
        int $signatureRequestId,
        ?int $signatureDocumentId,
        string $eventType,
        ?string $actorType,
        ?int $actorUserId,
        ?string $ip,
        ?string $userAgent,
        CarbonInterface $occurredAt,
        array $metadata,
    ): array {
        return [
            'v' => self::VERSION,
            'previous_event_hash' => $previousEventHash,
            'signature_request_id' => $signatureRequestId,
            'signature_document_id' => $signatureDocumentId,
            'event_type' => $eventType,
            'actor_type' => $actorType,
            'actor_user_id' => $actorUserId,
            'ip' => $ip,
            'ua' => $userAgent,
            // Second precision in UTC keeps the round-trip reproducible.
            'occurred_at_utc' => $occurredAt->utc()->format('Y-m-d\TH:i:s\Z'),
            'metadata' => $metadata,
        ];
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public function hash(array $fields): string
    {
        return hash('sha256', $this->canonicalize($fields));
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    public function canonicalize(array $fields): string
    {
        return json_encode(
            $this->sortRecursive($fields),
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }

    /**
     * Recursively sort associative-array keys; list arrays keep their order.
     */
    private function sortRecursive(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $isList = array_is_list($value);

        $out = [];
        foreach ($value as $key => $item) {
            $out[$key] = $this->sortRecursive($item);
        }

        if (! $isList) {
            ksort($out);
        }

        return $out;
    }
}
