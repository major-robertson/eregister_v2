<?php

namespace App\Domains\Esign\Contracts;

use App\Domains\Esign\Models\SignatureDocument;

/**
 * A single document to be signed within a session (one recipient letter). The
 * descriptor carries everything needed to render the letter — `renderPayload` is
 * a self-contained snapshot so rendering never depends on live data. The send
 * action assigns the human `identifier` (e.g. DL-1001) and freezes it on the row.
 */
final class SignableDocument
{
    /**
     * @param  array<string, mixed>  $renderPayload
     */
    public function __construct(
        public readonly string $label,
        public readonly ?string $recipientRef,
        public readonly array $renderPayload,
        public readonly int $sortOrder = 0,
        public ?string $identifier = null,
    ) {}

    /**
     * Rebuild a descriptor from a persisted document row, reading the immutable
     * render snapshot taken at lock time (so signed output reproduces the locked
     * letter even if the live source later changes).
     */
    public static function fromModel(SignatureDocument $document): self
    {
        $snapshot = $document->document_snapshot_json ?? [];

        return new self(
            label: $document->label,
            recipientRef: $document->recipient_ref,
            renderPayload: $snapshot['render'] ?? [],
            sortOrder: $document->sort_order,
            identifier: $document->document_identifier,
        );
    }
}
