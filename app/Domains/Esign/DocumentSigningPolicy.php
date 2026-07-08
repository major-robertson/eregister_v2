<?php

namespace App\Domains\Esign;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * The rules for signing a given document type, read from config('esign.document_types').
 * Keeps demand letters simple today while leaving room for liens / prelim notices /
 * resale certs / notarized docs to differ — e.g. requires_notary routes to the
 * future eNotarize path rather than typed signatures.
 */
final class DocumentSigningPolicy
{
    /**
     * @param  array<string, mixed>  $config
     */
    private function __construct(
        public readonly string $key,
        private readonly array $config,
    ) {}

    public static function for(string $key): self
    {
        $config = config("esign.document_types.{$key}");

        if (! is_array($config)) {
            throw new InvalidArgumentException("No signing policy configured for document type [{$key}].");
        }

        return new self($key, $config);
    }

    public function supportsEsign(): bool
    {
        return (bool) ($this->config['supports_esign'] ?? false);
    }

    public function requiresNotary(): bool
    {
        return (bool) ($this->config['requires_notary'] ?? false);
    }

    public function signatureMethod(): string
    {
        return $this->config['signature_method'] ?? 'typed_name';
    }

    public function requiresRecipientAcknowledgment(): bool
    {
        return (bool) ($this->config['requires_recipient_acknowledgment'] ?? false);
    }

    public function allowedSignerRole(): string
    {
        return $this->config['allowed_signer_role'] ?? 'filing_creator';
    }

    public function consentScope(): string
    {
        return $this->config['consent_scope'] ?? $this->key;
    }

    public function title(): string
    {
        return $this->config['title'] ?? Str::headline($this->key);
    }

    /**
     * The sign-button label shown (and snapshotted) on the review screen.
     * Falls back to the global signing config for legacy document types.
     */
    public function signButton(): string
    {
        return $this->config['sign_button'] ?? config('esign.signing.sign_button');
    }

    /**
     * The legally-meaningful intent statement the signer sees.
     */
    public function intentStatement(): string
    {
        return $this->config['intent'] ?? config('esign.signing.intent');
    }

    public function documentIdPrefix(): string
    {
        return $this->config['document_id_prefix'] ?? 'DOC';
    }
}
