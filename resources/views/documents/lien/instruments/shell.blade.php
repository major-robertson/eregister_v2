<!DOCTYPE html>
{{--
    Recordable-instrument shell: every state's mechanics lien and release
    renders through this page. The state body partial ($doc['form']['body'])
    supplies the operative text; the shell supplies what recorders check:
    the page-1 space for the recorder's stamp (with the preparer / return-to
    block in its left half), the title and statute line, the STATE OF /
    COUNTY OF caption with the page-1 index line (claimant, owner, amount,
    parcel), the execution block (signature, sworn or verified statement,
    notary certificate) and page numbers.

    Body-partial contract. Partials receive $doc (see LienDocumentPayload):
      form.{state,state_name,kind,title,statute,sections,clauses,execution,
            service,recording,county_name,recorder_space_in,template_version}
      titles.{prelim_notice,noi,mechanics_lien,lien_release}
      date, generated_at
      filing.{public_id,amount,amount_words,description_of_work,
              recording.{method,provider,reference,submitted_at,recorded_at}}
      project.{name,address.{lines,single_line},county,legal_description,apn,
               parcel_label,claimant_type_phrase,in_privity,contract_type,
               dates.{first_furnish,last_furnish,completion,…},amounts.{…}}
      parties.{claimant,owner,customer,hiring,gc,subcontractor,lender,others[]}
        (each: name, company, display_name, address_lines[], address_line, phone, email)
      signer.{name,title,company,license_number}, details.*, original_lien.*,
      preparer.*, server.state, recipients[]

    Typography is a recorder rule, not a style choice: 1in margins (the
    state file can widen the page-1 top), nothing under 10pt, DejaVu Serif
    11pt body. The title and any .bold-statement are 12pt bold, the largest
    type on the page, because Georgia wants its 395-day statement in at least
    12-point bold type. Missing values print as ruled blanks (.fill), never
    "N/A" or a dash. DOMPDF: plain HTML + inline CSS, no flex/grid.
--}}
@php
    $form = $doc['form'];
    $rec = $form['recording'];
    $margin = (float) ($rec['other_margin_in'] ?? 1.0);
    $space = (float) ($form['recorder_space_in'] ?? 2.0);
    $claimantName = $doc['parties']['claimant']['display_name'] ?? 'Claimant';
@endphp
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $form['title'] }} - {{ $claimantName }}</title>
    {{-- DOMPDF copies author and keywords into the PDF info dictionary (not subject), so
         staff can tell a regenerated draft from the one the client signed. --}}
    <meta name="author" content="eRegister">
    <meta name="keywords" content="{{ $form['title'] }} ({{ $form['state'] }}); template v{{ $form['template_version'] }}; generated {{ $doc['generated_at'] }}; filing {{ $doc['filing']['public_id'] }}">
    <style>
        @page { margin: {{ $margin }}in; }
        @include('documents.lien._parts.base-styles')

        .recorder-space { height: {{ $space }}in; overflow: hidden; }
        .preparer { font-size: 10pt; line-height: 1.3; width: 3.4in; }
        .preparer-below { margin: 4pt 0 10pt 0; }
        .recorder-rule { border-top: 1px solid #000; font-size: 10pt; text-align: right; padding-top: 1pt; margin: 0 0 10pt 0; }

        .caption { width: 100%; border-collapse: collapse; margin: 0 0 10pt 0; }
        .caption td { vertical-align: top; padding: 0; }
        .venue { line-height: 1.6; }
        .index { border: 1px solid #000; border-collapse: collapse; width: 100%; font-size: 10pt; }
        .index td { border: 1px solid #000; padding: 2pt 5pt; vertical-align: top; }
        .index td.k { font-weight: bold; white-space: nowrap; width: 1.15in; }
        .index-block { border: 1px solid #000; border-collapse: collapse; width: 100%; font-size: 10pt; margin: 0 0 10pt 0; }
        .index-block td { border: 1px solid #000; padding: 2pt 5pt; vertical-align: top; }
        .index-block td.k { font-weight: bold; white-space: nowrap; width: 1.7in; }
        .docket td { padding: 3pt 6pt; }

        .service-affidavit, .cancellation { border-top: 1px solid #000; margin-top: 16pt; padding-top: 8pt; page-break-inside: avoid; }
        .cancellation { font-size: 10.5pt; }
        .clerk-lines { margin-top: 14pt; line-height: 1.8; }
    </style>
</head>
<body>
    @if ($rec['page_numbers'] ?? true)
        <div class="page-number">{{ $form['title'] }} - Page <span class="n"></span></div>
    @endif

    @include('documents.lien._parts.recorder-block')

    @if (! empty($form['clauses']['notice_box']))
        @include('documents.lien._parts.clauses', ['only' => 'notice_box'])
    @endif

    {{-- Uppercased in the text itself, not only by CSS, so extracted text and index entries read the same. --}}
    <p class="doc-title">{{ mb_strtoupper($form['title']) }}</p>
    @if (! empty($form['statute']))
        <p class="doc-statute">{{ $form['statute'] }}</p>
    @endif

    @include('documents.lien._parts.caption')

    @if (! empty($form['clauses']['bold_statement']))
        <p class="bold-statement">{{ $form['clauses']['bold_statement'] }}</p>
    @endif

    @include($form['body'])

    @include('documents.lien._parts.execution')

    @if (! empty($form['service']['certificate_on_instrument']))
        @include('documents.lien._parts.service-affidavit')
    @endif

    @if (! empty($form['sections']['cancellation_block']))
        <div class="cancellation">
            <p><strong>CANCELLATION OF {{ strtoupper($form['title']) }}</strong> (to be completed when the claim has been satisfied)</p>
            <p>The claim of lien described above has been paid and satisfied in full. The undersigned claimant cancels it and authorizes and directs the clerk to cancel it of record.</p>
            <table class="sig-table">
                <tr>
                    <td style="width: 58%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Claimant, by (signature and printed name)</div></td>
                    <td style="width: 42%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Date</div></td>
                </tr>
            </table>
        </div>
    @endif

    @if (! empty($form['clauses']['after_execution']))
        @include('documents.lien._parts.clauses', ['only' => 'after_execution'])
    @endif
</body>
</html>
