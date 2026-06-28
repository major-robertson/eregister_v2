<?php

namespace App\Domains\Esign\Support;

/**
 * Tiny helpers for hashing rendered PDF bytes. Bytes come from
 * Spatie\LaravelPdf\PdfBuilder::generatePdfContent() — render ONCE into a single
 * string, then hash + store from that same string (a second render can differ
 * because DOMPDF embeds a creation timestamp).
 */
final class PdfBytes
{
    public static function sha256(string $bytes): string
    {
        return hash('sha256', $bytes);
    }

    public static function looksLikePdf(string $bytes): bool
    {
        return str_starts_with($bytes, '%PDF-');
    }
}
