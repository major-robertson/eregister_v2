<?php

namespace App\Domains\ResaleCert\Pdf;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

abstract class BaseStateCertificate implements StateCertificateInterface
{
    /**
     * Set position and write text in one call. Skips null/'' so empty data
     * never stamps stray characters (but "0" still writes).
     */
    protected function writeAt(Fpdi $pdf, float $x, float $y, ?string $text, float $height = 0): void
    {
        if ($text !== null && $text !== '') {
            $pdf->SetXY($x, $y);
            $pdf->Write($height, $text);
        }
    }

    /**
     * Add the creator's signature image at the default height (20mm).
     */
    protected function addSignature(Fpdi $pdf, ResaleCertificate $certificate, float $x, float $y): void
    {
        $this->addSignatureWithHeight($pdf, $certificate, $x, $y, 20);
    }

    /**
     * Add the creator's signature image, width auto-calculated from the
     * capture canvas's fixed 500x100 (5:1) aspect ratio.
     */
    protected function addSignatureWithHeight(Fpdi $pdf, ResaleCertificate $certificate, float $x, float $y, float $height): void
    {
        $signature = $certificate->createdBy?->currentSignature;
        $disk = Storage::disk(config('resale_cert.disk'));

        if (! $signature || ! $disk->exists($signature->image_path)) {
            return;
        }

        // FPDF reads images from a local path, so buffer the PNG to a temp file.
        $tempPath = storage_path('app/temp/signature_'.uniqid().'.png');

        if (! is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0777, true);
        }

        file_put_contents($tempPath, $disk->get($signature->image_path));

        try {
            $pdf->Image($tempPath, $x, $y, $height * 5, $height, 'PNG');
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * Extract all certificate data into the flat object every state class
     * reads its fields from. Data comes from the frozen snapshots taken at
     * generation time so re-rendering never drifts from what was issued.
     *
     * The key set is a stable contract with the ~40 state classes — extend it,
     * never rename members.
     */
    protected function extractCertificateData(ResaleCertificate $certificate): object
    {
        $business = $certificate->business_snapshot;
        $vendor = $certificate->vendor_snapshot;
        $user = $certificate->createdBy;

        $signerName = trim(($user?->first_name ?? '').' '.($user?->last_name ?? ''));
        $signerTitle = $business['signer_title'] ?? 'Authorized Representative';

        return (object) [
            // Vendor data
            'vendorName' => $vendor['legal_name'],
            'vendorContact' => $vendor['contact']['name'] ?? '',
            'vendorContactEmail' => $vendor['contact']['email'] ?? '',
            'vendorPhone' => $vendor['contact']['phone'] ?? '',
            'vendorAddress1' => $vendor['address']['line1'],
            'vendorAddress2' => $vendor['address']['line2'] ?? '',
            'vendorStreetAddress' => trim($vendor['address']['line1'].' '.($vendor['address']['line2'] ?? '')),
            'vendorCity' => $vendor['address']['city'],
            'vendorState' => $vendor['address']['state'],
            'vendorZip' => $vendor['address']['postal_code'],
            'vendorCityStateZip' => $vendor['address']['city'].', '.$vendor['address']['state'].' '.$vendor['address']['postal_code'],
            'vendorFullAddress' => $vendor['address']['line1'].' '.($vendor['address']['line2'] ?? '').' '.$vendor['address']['city'].', '.$vendor['address']['state'].' '.$vendor['address']['postal_code'],

            // Business data
            'businessName' => $business['legal_name'],
            'businessDba' => $business['dba'] ?? '',
            'businessEin' => $business['ein'] ?? '',
            'businessTaxId' => $business['tax_id'] ?? '',
            'businessAddress1' => $business['address']['line1'],
            'businessAddress2' => $business['address']['line2'] ?? '',
            'businessStreetAddress' => trim($business['address']['line1'].' '.($business['address']['line2'] ?? '')),
            'businessCity' => $business['address']['city'],
            'businessState' => $business['address']['state'],
            'businessZip' => $business['address']['postal_code'],
            'businessCityStateZip' => $business['address']['city'].', '.$business['address']['state'].' '.$business['address']['postal_code'],
            'businessFullAddress' => $business['address']['line1'].' '.($business['address']['line2'] ?? '').' '.$business['address']['city'].', '.$business['address']['state'].' '.$business['address']['postal_code'],

            // User/Signer data
            'signerName' => $signerName !== '' ? $signerName : 'Authorized Representative',
            'signerTitle' => $signerTitle,
            'signerNameAndTitle' => ($signerName !== '' ? $signerName : 'Authorized Representative').' - '.$signerTitle,

            // Certificate data
            'issueDate' => $certificate->issue_date->format('m/d/Y'),
            'productDescription' => $business['products_description'] ?? 'General Merchandise',
            'email' => $business['email'] ?? ($user?->email ?? ''),
            'phone' => $business['phone'] ?? '',

            // Common values
            'businessType' => 'Resale/Drop Shipping',
            'checkmarkX' => 'X',
        ];
    }
}
