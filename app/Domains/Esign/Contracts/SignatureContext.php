<?php

namespace App\Domains\Esign\Contracts;

use App\Domains\Esign\Models\SignatureDocument;
use App\Domains\Esign\Models\SignatureRequest;
use Carbon\CarbonInterface;

/**
 * Everything an adapter needs to render the SIGNED version of a document: the
 * adopted (typed) name, when it was signed, a stable signature id, and the
 * request + document models so the certificate page can read the consent,
 * hashes, and audit-trail events.
 */
final class SignatureContext
{
    public function __construct(
        public readonly string $adoptedName,
        public readonly CarbonInterface $signedAtUtc,
        public readonly string $signatureId,
        public readonly SignatureRequest $request,
        public readonly SignatureDocument $document,
    ) {}
}
