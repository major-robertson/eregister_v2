<!DOCTYPE html>
{{--
    The page appended to a signed uploaded lien document (EREG-141,
    PdfSignatureStamper). The document's own pages come before it, carried
    over as they are. This page holds the electronic signature block and the
    Certificate of Completion, from the same partials and wording as the
    signed demand letter, plus the document id and the signer's IP address.
    The certificate follows the signature on the same page and runs onto a
    second page only when the audit trail is long.

    DOMPDF driver: plain HTML + inline CSS only.

    Vars: $signature, $certificate, $document[identifier|label|pages|ip]
--}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Electronic signature</title>
    <style>
        @page { margin: 0.5in 0.6in; }
        body { font-family: 'DejaVu Sans', Helvetica, sans-serif; font-size: 10pt; line-height: 1.4; color: #000; }
        .esign-page-title { font-size: 15pt; margin: 0 0 4px 0; }
        .esign-page-sub { color: #444; margin: 0 0 6px 0; }

        /* Electronic signature block (as on the signed demand letter) */
        .esign-signature { margin-top: 14px; border-top: 1px solid #999; padding-top: 8px; }
        .esign-signature-label { font-size: 8.5pt; color: #555; }
        .esign-signature-name { font-family: 'DejaVu Serif', serif; font-style: italic; font-size: 24pt; color: #1a1a1a; margin: 2px 0 4px 0; }
        .esign-signature-meta { font-size: 8.5pt; color: #555; line-height: 1.3; }
        .esign-document-meta { margin-top: 2px; }

        /* Certificate of Completion: on this page, after the signature */
        .esign-cert { margin-top: 14px; font-size: 9pt; }
        .esign-cert-title { font-size: 13pt; margin: 0 0 4px 0; }
        .esign-cert-sub { color: #444; margin: 0 0 8px 0; }
        .esign-cert-table { border-collapse: collapse; width: 100%; margin-bottom: 8px; }
        .esign-cert-table td { border: 1px solid #ddd; padding: 2px 6px; vertical-align: top; }
        .esign-cert-table td:first-child { width: 32%; color: #555; background: #f7f7f7; white-space: nowrap; }
        .esign-cert-hash { font-family: 'DejaVu Sans Mono', monospace; font-size: 7.5pt; word-break: break-all; }
        .esign-cert-intent { margin: 0 0 8px 0; }
        .esign-cert-h3 { font-size: 11pt; margin: 0 0 6px 0; }
        .esign-cert-events { border-collapse: collapse; width: 100%; font-size: 8.5pt; }
        .esign-cert-events th, .esign-cert-events td { border: 1px solid #ddd; padding: 2px 5px; text-align: left; vertical-align: top; }
        .esign-cert-events th { background: #f1f1f1; }
        .esign-cert-foot { margin-top: 10px; font-size: 8pt; color: #666; line-height: 1.4; }
    </style>
</head>
<body>
    <h1 class="esign-page-title">Electronic signature</h1>
    <p class="esign-page-sub">
        This page is part of <strong>{{ $document['label'] }}</strong> (Document ID {{ $document['identifier'] }}).
        It records the electronic signature on the {{ $document['pages'] }} {{ $document['pages'] === 1 ? 'page' : 'pages' }} before it.
    </p>

    @include('documents.lien._signature-block', ['signature' => $signature])
    <div class="esign-signature-meta esign-document-meta">
        Document ID: {{ $document['identifier'] }}<br>
        Signed from IP address: {{ $document['ip'] ?: '—' }}
    </div>

    @include('documents.lien._esign-certificate', ['certificate' => $certificate])
</body>
</html>
