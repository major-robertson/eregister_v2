<!DOCTYPE html>
{{--
    Signed Payment Demand Letter. Renders the shared body partial (fed from the
    locked render snapshot), the electronic signature block, and an appended
    Certificate of Completion on its own page.

    DOMPDF driver: plain HTML + inline CSS only.

    Vars: $letter (body payload), $signature, $certificate
--}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Signed Demand Letter</title>
    <style>
        @page { margin: 1in; }
        body { font-family: 'DejaVu Sans', Helvetica, sans-serif; font-size: 11pt; line-height: 1.4; color: #000; }
        .letter p { margin: 0 0 10px 0; }
        .date { margin-bottom: 18px; }
        .recipient { margin-bottom: 14px; }
        .recipient div, .signature div { line-height: 1.3; }
        .salutation { margin-bottom: 12px; }
        .details { border-collapse: collapse; margin: 0 0 10px 0; }
        .details td { padding: 1px 0; vertical-align: top; }
        .details td:first-child { padding-right: 10px; white-space: nowrap; }
        .work-label { margin-bottom: 4px; }
        .work { margin-top: 0; }
        .closing { margin-top: 16px; margin-bottom: 0; }

        /* Electronic signature block */
        .esign-signature { margin-top: 26px; border-top: 1px solid #999; padding-top: 8px; }
        .esign-signature-label { font-size: 8.5pt; color: #555; }
        .esign-signature-name { font-family: 'DejaVu Serif', serif; font-style: italic; font-size: 24pt; color: #1a1a1a; margin: 2px 0 4px 0; }
        .esign-signature-meta { font-size: 8.5pt; color: #555; line-height: 1.3; }

        /* Certificate of Completion */
        .esign-cert { page-break-before: always; font-size: 9.5pt; }
        .esign-cert-title { font-size: 15pt; margin: 0 0 4px 0; }
        .esign-cert-sub { color: #444; margin: 0 0 12px 0; }
        .esign-cert-table { border-collapse: collapse; width: 100%; margin-bottom: 12px; }
        .esign-cert-table td { border: 1px solid #ddd; padding: 4px 6px; vertical-align: top; }
        .esign-cert-table td:first-child { width: 32%; color: #555; background: #f7f7f7; white-space: nowrap; }
        .esign-cert-hash { font-family: 'DejaVu Sans Mono', monospace; font-size: 7.5pt; word-break: break-all; }
        .esign-cert-intent { margin: 0 0 14px 0; }
        .esign-cert-h3 { font-size: 11pt; margin: 0 0 6px 0; }
        .esign-cert-events { border-collapse: collapse; width: 100%; font-size: 8.5pt; }
        .esign-cert-events th, .esign-cert-events td { border: 1px solid #ddd; padding: 3px 5px; text-align: left; vertical-align: top; }
        .esign-cert-events th { background: #f1f1f1; }
        .esign-cert-foot { margin-top: 16px; font-size: 8pt; color: #666; line-height: 1.4; }
    </style>
</head>
<body>
    @include('documents.lien._demand-letter-body', $letter)
    @include('documents.lien._signature-block', ['signature' => $signature])
    @include('documents.lien._esign-certificate', ['certificate' => $certificate])
</body>
</html>
