<?php

namespace App\Domains\ResaleCert\Services;

use App\Domains\Business\Models\Business;
use App\Domains\ResaleCert\Actions\AppendResaleSignatureEvent;
use App\Domains\ResaleCert\Enums\ResaleSignatureEventType;
use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleProfile;
use App\Domains\ResaleCert\Models\ResaleStateRule;
use App\Domains\ResaleCert\Models\ResaleVendor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Creates certificate records (frozen snapshots + computed expiration),
 * renders their PDFs, and appends a hash-chained audit event per generated
 * certificate (who signed, from where, and the sha256 of the exact bytes).
 */
class CertificateGenerator
{
    public function __construct(
        protected CertificateSnapshots $snapshots,
        protected CertificatePdfService $pdfService,
        protected AppendResaleSignatureEvent $auditLog,
    ) {}

    /**
     * @param  list<array{state_code: string, covers_states: list<string>, is_blanket: bool, item_description: ?string}>  $forms
     * @return list<ResaleCertificate>
     */
    public function generate(Business $business, ResaleVendor $vendor, User $creator, array $forms): array
    {
        $generated = DB::transaction(function () use ($business, $vendor, $creator, $forms) {
            $profile = $business->resaleProfile;
            $vendorSnapshot = $this->snapshots->vendorSnapshot($vendor);
            $certificates = [];

            foreach ($forms as $form) {
                $stateCode = $form['state_code'];
                $stateRule = ResaleStateRule::where('state_code', $stateCode)->first();

                $issueDate = now();
                $expirationRule = $profile?->expirationRuleForState($stateCode) ?? 'end_of_current_year';

                $isBlanket = $form['is_blanket'] ?? true;
                $itemDescription = ! $isBlanket && filled($form['item_description'] ?? null)
                    ? $form['item_description']
                    : ($stateRule?->default_blanket_text ?? 'All tangible personal property for resale');

                $certificates[] = ResaleCertificate::create([
                    'business_id' => $business->id,
                    'resale_vendor_id' => $vendor->id,
                    'created_by_user_id' => $creator->id,
                    'state_code' => $stateCode,
                    'is_blanket' => $isBlanket,
                    'item_description' => $itemDescription,
                    'business_snapshot' => $this->snapshots->businessSnapshot(
                        $business,
                        $creator,
                        $stateCode,
                        $form['covers_states'] ?? [$stateCode],
                    ),
                    'vendor_snapshot' => $vendorSnapshot,
                    'issue_date' => $issueDate,
                    'expiration_date' => ResaleProfile::calculateExpirationDate($issueDate, $expirationRule),
                ]);
            }

            return $certificates;
        });

        $signatureId = $creator->currentSignature()->value('id');

        // PDF rendering happens outside the transaction: slow work doesn't
        // hold locks, and a render failure leaves a re-renderable record
        // rather than rolling back the batch.
        foreach ($generated as $certificate) {
            $pdfPath = $this->pdfService->generateCertificate($certificate);
            $pdfSha256 = hash('sha256', (string) Storage::disk(config('resale_cert.disk'))->get($pdfPath));

            $certificate->update([
                'pdf_path' => $pdfPath,
                'pdf_sha256' => $pdfSha256,
            ]);

            $this->auditLog->execute(
                $business,
                ResaleSignatureEventType::CertificateGenerated,
                signatureId: $signatureId,
                certificateId: $certificate->id,
                actorUserId: $creator->id,
                ip: request()?->ip(),
                userAgent: request()?->userAgent(),
                metadata: [
                    'state_code' => $certificate->state_code,
                    'vendor_id' => $vendor->id,
                    'vendor_name' => $vendor->legal_name,
                    'pdf_sha256' => $pdfSha256,
                    'issue_date' => $certificate->issue_date->toDateString(),
                ],
            );
        }

        return $generated;
    }
}
