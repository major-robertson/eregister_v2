<?php

namespace App\Domains\ResaleCert\Pdf;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use setasign\Fpdi\Fpdi;

interface StateCertificateInterface
{
    /**
     * Fill form fields on the imported PDF template page.
     *
     * Coordinates inside implementations are millimeters (FPDI default unit)
     * mapped against the official state form — ported verbatim from the
     * original TaxResaleCertificate app. Do not "clean up" coordinate values.
     *
     * @param  int  $currentPage  Current page number (1-based)
     * @param  int  $totalPages  Total number of pages in the PDF
     */
    public function fillFormFields(Fpdi $pdf, ResaleCertificate $certificate, int $currentPage, int $totalPages): void;

    /**
     * Get the template file path for this certificate, relative to resources/.
     */
    public function getTemplatePath(): string;
}
