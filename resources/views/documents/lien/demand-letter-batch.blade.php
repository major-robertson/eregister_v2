<!DOCTYPE html>
{{--
    Combined "download all" PDF: one demand letter per recipient, one page each
    (page break between). Reuses the same body partial as the single letter.

    Vars: $letters — list of per-recipient payloads (see DemandLetterGenerator::data()).
--}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Demand Letters</title>
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
    </style>
</head>
<body>
    @foreach ($letters as $letter)
        @if (! $loop->first)
            @pageBreak
        @endif
        @include('documents.lien._demand-letter-body', $letter)
    @endforeach
</body>
</html>
