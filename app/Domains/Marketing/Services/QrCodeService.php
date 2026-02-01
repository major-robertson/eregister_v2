<?php

namespace App\Domains\Marketing\Services;

use App\Domains\Marketing\Models\MarketingTrackingLink;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a QR code SVG for a tracking link and upload to S3.
     */
    public function generateForTrackingLink(MarketingTrackingLink $trackingLink): ?string
    {
        try {
            $url = $trackingLink->getTrackingUrl();
            $svgContent = $this->generateSvg($url);

            if (! $svgContent) {
                return null;
            }

            $path = $this->uploadToS3($trackingLink->public_id, $svgContent);

            // Update the tracking link with the QR code path
            $trackingLink->update(['qr_code_path' => $path]);

            return $path;
        } catch (\Throwable $e) {
            Log::error('Failed to generate QR code for tracking link', [
                'tracking_link_id' => $trackingLink->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate an SVG QR code from a URL.
     */
    public function generateSvg(string $url): string
    {
        $builder = new Builder(
            writer: new SvgWriter,
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
        );

        $result = $builder->build();

        return $result->getString();
    }

    /**
     * Upload SVG content to S3.
     */
    protected function uploadToS3(string $publicId, string $svgContent): string
    {
        $path = "marketing/qr-codes/{$publicId}.svg";

        Storage::disk('s3')->put($path, $svgContent, [
            'ContentType' => 'image/svg+xml',
            'visibility' => 'public',
        ]);

        return $path;
    }

    /**
     * Delete a QR code from S3.
     */
    public function deleteFromS3(string $path): bool
    {
        return Storage::disk('s3')->delete($path);
    }

    /**
     * Get the public URL for a QR code path.
     */
    public function getPublicUrl(string $path): string
    {
        return Storage::disk('s3')->url($path);
    }
}
