<?php

namespace App\Domains\Marketing\Services;

class MailResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $providerId = null,
        public readonly ?string $status = null,
        public readonly ?array $payload = null,
        public readonly ?string $errorMessage = null,
        public readonly bool $retryable = false,
    ) {}

    /**
     * Create a successful result.
     */
    public static function success(string $providerId, string $status, array $payload = []): self
    {
        return new self(
            success: true,
            providerId: $providerId,
            status: $status,
            payload: $payload,
        );
    }

    /**
     * Create a failed result.
     */
    public static function failure(string $errorMessage, bool $retryable = false, ?array $payload = null): self
    {
        return new self(
            success: false,
            errorMessage: $errorMessage,
            retryable: $retryable,
            payload: $payload,
        );
    }
}
