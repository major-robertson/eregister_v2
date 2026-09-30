<!DOCTYPE html>
{{--
    Avery 5160 label sheet: 3 columns x 10 rows of 2.625in x 1in labels with
    0.125in gutters, 0.5in top and 0.1875in side margins. Each recipient label
    is followed by an eRegister return label, the way staff lay the sheet out
    by hand. Labels are absolutely positioned on a page-sized sheet (DOMPDF's
    table layout does not keep fixed column widths), and the generator clamps
    every label to five lines because nothing here clips.

    Vars: $doc (LienDocumentPayload), $sheets (list of up to 30 labels, each a list of lines)
--}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Mailing Labels - {{ $doc['form']['title'] }}</title>
    <meta name="author" content="eRegister">
    <style>
        @page { margin: 0.5in 0.1875in; }
        body { font-family: 'DejaVu Sans', Helvetica, sans-serif; font-size: 9.5pt; color: #000; margin: 0; padding: 0; }
        .sheet { position: relative; width: 8.125in; height: 10in; }
        .sheet.break { page-break-after: always; }
        .label { position: absolute; width: 2.325in; height: 0.8in; padding: 0.1in 0.15in; line-height: 1.2; overflow: hidden; }
    </style>
</head>
<body>
    @foreach ($sheets as $sheetIndex => $labels)
        <div class="sheet{{ $sheetIndex < count($sheets) - 1 ? ' break' : '' }}">
            @foreach ($labels as $position => $lines)
                @if ($lines !== [])
                    <div class="label" style="left: {{ ($position % 3) * 2.75 }}in; top: {{ intdiv($position, 3) }}in;">
                        @foreach ($lines as $line)
                            <div>{{ $line }}</div>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
    @endforeach
</body>
</html>
