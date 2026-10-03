<?php

namespace App\Domains\ResaleCert\Services;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Pdf\StateCertificateFactory;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

/**
 * Renders a resale certificate PDF: imports the official state form and
 * stamps coordinate-mapped text via FPDI, or draws a generic certificate
 * from scratch for template-less states (AL, OK). Ported from the
 * original TaxResaleCertificate app — DOMPDF can't fill existing PDFs,
 * so this domain deliberately deviates from the blade-template convention.
 */
class CertificatePdfService
{
    /**
     * Dev aid: overlays a red 5mm coordinate grid on every page so field
     * positions can be read off the PDF while mapping a state form.
     */
    protected bool $showGrid = false;

    public function __construct(protected StateCertificateFactory $factory) {}

    /**
     * Generate the PDF, store it on the resale-cert disk, and return the
     * storage path.
     */
    public function generateCertificate(ResaleCertificate $certificate, ?bool $showGrid = null): string
    {
        $bytes = $this->renderCertificate($certificate, $showGrid);

        $path = sprintf(
            '%s/%s/%s/%s_%s_%s.pdf',
            config('resale_cert.storage_prefix'),
            $certificate->business_id,
            now()->format('Y/m'),
            $certificate->state_code,
            $certificate->id,
            now()->format('YmdHis'),
        );

        Storage::disk(config('resale_cert.disk'))->put($path, $bytes);

        return $path;
    }

    /**
     * Render the certificate to raw PDF bytes without storing — used by
     * generation (which persists) and by the admin coordinate-mapper's
     * sample previews (which stream, certificate unsaved).
     */
    public function renderCertificate(ResaleCertificate $certificate, ?bool $showGrid = null): string
    {
        if ($showGrid !== null) {
            $this->showGrid = $showGrid;
        }

        $stateCode = $certificate->state_code;

        if (! $stateCode || ! $this->factory->has($stateCode)) {
            throw new \RuntimeException("No certificate handler found for state: {$stateCode}");
        }

        $pdf = new Fpdi;
        $stateCertificate = $this->factory->make($stateCode);

        if (method_exists($stateCertificate, 'usesCustomGeneration') && $stateCertificate->usesCustomGeneration()) {
            $stateCertificate->generateCustomPdf($pdf, $certificate);

            if ($this->showGrid) {
                $this->addCoordinateGrid($pdf);
            }
        } else {
            $this->generateFromTemplate($pdf, $certificate);
        }

        return $pdf->Output('S');
    }

    protected function generateFromTemplate(Fpdi $pdf, ResaleCertificate $certificate): void
    {
        $stateCode = $certificate->state_code;
        $relativePath = $this->factory->getTemplatePathForCertificate($stateCode, $certificate);
        $templatePath = $relativePath ? resource_path($relativePath) : null;

        if (! $templatePath || ! file_exists($templatePath)) {
            throw new \RuntimeException(
                "PDF template not found for state: {$stateCode}. Expected: ".($relativePath ?? '(none)')
            );
        }

        $stateCertificate = $this->factory->make($stateCode);
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
            $tplIdx = $pdf->importPage($pageNumber);
            $size = $pdf->getTemplateSize($tplIdx);

            // Every page takes its template size; a bare AddPage() would fall
            // back to FPDF's A4 default and clip letter-size forms.
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);

            $pdf->useTemplate($tplIdx);
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            // Fields near the bottom edge must stay on this page, never
            // trip FPDF's auto page break into a new one.
            $pdf->SetAutoPageBreak(false);

            $stateCertificate->fillFormFields($pdf, $certificate, $pageNumber, $pageCount);

            if ($this->showGrid) {
                $this->addCoordinateGrid($pdf);
            }
        }
    }

    /**
     * Overlay a labeled coordinate grid (5mm minor / 10mm major lines) used
     * when mapping field positions for a new or updated state form. Labels
     * use Text(), not Write(): Write() near the bottom edge trips the auto
     * page break and appends blank pages.
     */
    protected function addCoordinateGrid(Fpdi $pdf): void
    {
        $pdf->SetFont('Helvetica', '', 6);
        $pdf->SetTextColor(255, 128, 128);
        $pdf->SetDrawColor(255, 128, 128);
        $pdf->SetLineWidth(0.1);

        $pageWidth = $pdf->GetPageWidth();
        $pageHeight = $pdf->GetPageHeight();

        for ($x = 0; $x <= $pageWidth; $x += 5) {
            if ($x % 10 === 0 && $x > 0) {
                $pdf->SetLineWidth(0.2);
                $pdf->Line($x, 0, $x, $pageHeight);
                $pdf->SetLineWidth(0.1);

                $labelWidth = strlen((string) $x) * 2;
                $pdf->Text($x - ($labelWidth / 2), 2.6, (string) $x);
                $pdf->Text($x - ($labelWidth / 2), $pageHeight - 7.4, (string) $x);
            } else {
                $pdf->Line($x, 0, $x, $pageHeight);
            }
        }

        for ($y = 0; $y <= $pageHeight; $y += 5) {
            if ($y % 10 === 0 && $y > 0) {
                $pdf->SetLineWidth(0.2);
                $pdf->Line(0, $y, $pageWidth, $y);
                $pdf->SetLineWidth(0.1);

                $pdf->Text(1, $y - 0.4, (string) $y);
                $labelWidth = strlen((string) $y) * 2;
                $pdf->Text($pageWidth - $labelWidth - 2, $y - 0.4, (string) $y);
            } else {
                $pdf->Line(0, $y, $pageWidth, $y);
            }
        }

        $pdf->SetFont('Helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
    }
}
