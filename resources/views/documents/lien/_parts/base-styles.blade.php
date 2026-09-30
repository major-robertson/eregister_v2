{{--
    CSS shared by every generated lien document (instrument shell, letter
    shell and the service set). Included inside each page's <style>.
    Recorder rules, not taste: DejaVu Serif 11pt, nothing under 10pt, the
    title and any .bold-statement at 12pt as the largest type on the page.
    DOMPDF: no flex/grid.
--}}
body { font-family: 'DejaVu Serif', Georgia, serif; font-size: 11pt; line-height: 1.35; color: #000; }
p { margin: 0 0 8pt 0; text-align: justify; }

.doc-title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; letter-spacing: 0.5pt; margin: 0 0 2pt 0; }
.doc-statute { font-size: 10pt; text-align: center; margin: 0 0 10pt 0; }
.notice-box { border: 1.5pt solid #000; padding: 6pt 8pt; margin: 0 0 10pt 0; font-weight: bold; }
.notice-box p { margin: 0 0 6pt 0; }
.bold-statement { font-size: 12pt; font-weight: bold; text-align: left; margin: 0 0 10pt 0; }

table.item { width: 100%; border-collapse: collapse; margin: 0 0 7pt 0; page-break-inside: avoid; }
table.item td { vertical-align: top; padding: 0; }
table.item td.n { width: 26pt; font-weight: bold; }
.fields { border-collapse: collapse; width: 100%; margin: 0 0 10pt 0; }
.fields td { vertical-align: top; padding: 2pt 0; }
.fields td.k { width: 2.4in; font-weight: bold; padding-right: 8pt; }
.lbl { font-weight: bold; }
.block div { line-height: 1.3; }
.indent { margin: 0 0 8pt 24pt; }

table.amounts { border-collapse: collapse; margin: 3pt 0 6pt 0; }
table.amounts td { padding: 1pt 12pt 1pt 0; vertical-align: top; }
table.amounts td.money { text-align: right; white-space: nowrap; padding-right: 0; }
table.amounts tr.total td { border-top: 1px solid #000; font-weight: bold; padding-top: 3pt; }

.fill { display: inline-block; min-width: 1.5in; border-bottom: 1px solid #000; }
.fill-short { min-width: 0.55in; }
.fill-mid { min-width: 1in; }
.fill-wide { min-width: 3in; }
.box { display: inline-block; width: 9pt; height: 9pt; border: 1px solid #000; vertical-align: -1pt; margin: 0 2pt 0 1pt; font-size: 8pt; line-height: 9pt; text-align: center; }

.execution { margin-top: 12pt; }
.sig-table { width: 100%; border-collapse: collapse; margin-top: 8pt; }
.sig-table td { padding: 12pt 18pt 0 0; vertical-align: bottom; }
.sig-line { border-bottom: 1px solid #000; height: 20pt; }
.sig-caption { font-size: 10pt; padding-top: 2pt; }
.notary { border: 1px solid #000; padding: 8pt 10pt; margin-top: 14pt; font-size: 10.5pt; page-break-inside: avoid; }
.notary p { margin: 0 0 6pt 0; }
.notary .sig-table td { padding-top: 10pt; }
.notary-disclaimer { border: 1.5pt solid #000; padding: 5pt 7pt; margin: 0 0 8pt 0; font-size: 10pt; }
.venue-lines { line-height: 1.6; margin: 0 0 6pt 0; }

.keep { page-break-inside: avoid; }
.caps { text-transform: uppercase; }
.center { text-align: center; }
.small { font-size: 10pt; }

.page-number { position: fixed; bottom: -0.55in; left: 0; right: 0; text-align: center; font-size: 10pt; }
.page-number .n:after { content: counter(page); }
