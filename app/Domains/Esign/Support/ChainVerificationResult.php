<?php

namespace App\Domains\Esign\Support;

final class ChainVerificationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly int $eventCount,
        public readonly ?int $brokenAtEventId = null,
        public readonly ?string $reason = null,
    ) {}
}
