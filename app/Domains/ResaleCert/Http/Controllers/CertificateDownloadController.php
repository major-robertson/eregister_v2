<?php

namespace App\Domains\ResaleCert\Http\Controllers;

use App\Domains\ResaleCert\Actions\AppendResaleSignatureEvent;
use App\Domains\ResaleCert\Enums\ResaleSignatureEventType;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a certificate PDF with a meaningful filename and records the
 * download (who, from where, which bytes) in the hash-chained audit log.
 * Tenant isolation comes from the model's business global scope during
 * route binding.
 */
class CertificateDownloadController
{
    public function __invoke(Request $request, ResaleCertificate $certificate, AppendResaleSignatureEvent $auditLog): StreamedResponse
    {
        $disk = Storage::disk(config('resale_cert.disk'));

        abort_unless($certificate->pdf_path && $disk->exists($certificate->pdf_path), 404, 'PDF not found.');

        $auditLog->execute(
            $certificate->business_id,
            ResaleSignatureEventType::CertificateDownloaded,
            certificateId: $certificate->id,
            actorUserId: $request->user()?->id,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            metadata: array_filter([
                'filename' => $certificate->downloadFilename(),
                'pdf_sha256' => $certificate->pdf_sha256,
            ]),
        );

        return $disk->download($certificate->pdf_path, $certificate->downloadFilename());
    }
}
