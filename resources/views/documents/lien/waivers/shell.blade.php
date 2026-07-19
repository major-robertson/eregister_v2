<!DOCTYPE html>
{{--
    Lien waiver document shell. Every state form (statutory or generic) is a
    BODY PARTIAL included by this shell via $waiver['form']['template'].

    Body-partial contract. Partials receive:
      $waiver  the frozen render payload (see WaiverGenerator::data()):
               form.{title,kind,state,state_name,statute,notarization_required,
               witness_required,deemed_effective_days,extra_clauses}, date, claimant.{company,
               name,address_lines[]}, customer.{...}, owner.{...}, project.{name,
               job_number,address_line,county,legal_description,apn}, amount,
               through_date, invoice_number, check_maker, check_number,
               exceptions, signer.{name,title,email,company}
      $esign   null for print/unsigned output, or {name,image,signed_at_eastern,
               signed_at_utc,signature_id}. Partials render the signature area
               via documents.lien.waivers._signature-lines which handles both.

    Typography constraints (legal, do not "fix"):
      - Statutory warning notices (.waiver-notice) MUST be at least as large as
        the largest type on the document (AZ/NV/CA) and bold ≥10pt at top (TX).
        The shell therefore caps ALL text at 12pt: form title = 12pt bold,
        notices = 12pt bold caps. Never add text larger than 12pt to a body.
      - DOMPDF: plain HTML + inline CSS only. No flex/grid.

    Vars: $waiver (required), $esign (optional), $certificate (optional).
--}}
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $waiver['form']['title'] }}</title>
    <style>
        {{-- The redesigned generic family targets a single page, so it runs
             tighter margins; statutory bodies keep the original 0.9in. --}}
        @page { margin: {{ str_contains($waiver['form']['template'], '.generic-') ? '0.45in 0.75in' : '0.9in' }}; }
        body { font-family: 'DejaVu Sans', Helvetica, sans-serif; font-size: 10pt; line-height: 1.45; color: #000; }
        p { margin: 0 0 9px 0; }

        .waiver-title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin: 0 0 4px 0; }
        .waiver-statute { font-size: 8pt; text-align: center; color: #444; margin: 0 0 14px 0; }

        /* Statutory warning notice: must be >= the largest type on the page. */
        .waiver-notice { font-size: 12pt; font-weight: bold; text-transform: uppercase; border: 1.5px solid #000; padding: 8px 10px; margin: 0 0 14px 0; line-height: 1.35; }

        .waiver-fields { border-collapse: collapse; width: 100%; margin: 0 0 12px 0; }
        .waiver-fields td { padding: 2px 0; vertical-align: bottom; }
        .waiver-fields td.label { width: 34%; white-space: nowrap; padding-right: 10px; }
        .waiver-fields td.value { border-bottom: 1px solid #000; }

        .waiver-body p { text-align: justify; }
        .fill { display: inline-block; min-width: 140px; border-bottom: 1px solid #000; padding: 0 4px; }
        .fill-wide { display: inline-block; min-width: 260px; border-bottom: 1px solid #000; padding: 0 4px; }

        .waiver-section-label { font-weight: bold; margin: 12px 0 4px 0; }

        /* Signature area */
        .sig-table { border-collapse: collapse; width: 100%; margin-top: 32px; }
        .sig-table td { padding: 12px 18px 2px 0; vertical-align: bottom; }
        /* height + padding-bottom = 30px to the rule. Printed values (name,
           company, date) ride 3px above the border, hovering like the labeled
           field rows above; blank lines keep the same wet-sign height. */
        .sig-line { border-bottom: 1px solid #000; height: 27px; line-height: 27px; padding-bottom: 3px; }
        .sig-caption { font-size: 8pt; color: #333; padding-top: 2px; }

        .esign-signature-name { font-family: 'DejaVu Serif', serif; font-style: italic; font-size: 12pt; color: #1a1a1a; }
        .esign-signature-meta { font-size: 7.5pt; color: #555; line-height: 1.3; }
        .esign-signature-img { height: 34px; }

        /* Notary / witness blocks (print execution) */
        .execution-block { border: 1px solid #000; padding: 10px 12px; margin-top: 18px; font-size: 9.5pt; }
        .execution-block .sig-line { height: 23px; line-height: 23px; }

        /* Certificate of Completion (signed output only) */
        .esign-cert { page-break-before: always; font-size: 9.5pt; }
        .esign-cert-title { font-size: 12pt; margin: 0 0 4px 0; }
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

        .waiver-foot { margin-top: 18px; font-size: 7.5pt; color: #666; }

        /* ============ Redesigned generic (house-form) family ============
           Serif single-page layout: left-aligned two-line title with reserved
           logo space, labeled underline fields, operative text that references
           the fields ("Amount stated above") so long values never reflow it.
           Title is 12pt so the unconditional caution notice stays >= the
           largest type on the page. Statutory bodies keep the classes above. */
        .gw { font-family: 'DejaVu Serif', Georgia, serif; font-size: 9.25pt; line-height: 1.4; color: #1a1a1a; }
        .gw-header { width: 100%; border-collapse: collapse; }
        .gw-header td { vertical-align: top; }
        .gw-title { font-size: 12pt; font-weight: bold; letter-spacing: 1px; line-height: 1.3; text-transform: uppercase; }
        .gw-kind { font-size: 9.5pt; font-weight: bold; letter-spacing: 2.5px; margin-top: 2px; text-transform: uppercase; }
        .gw-state { font-size: 9pt; font-style: italic; color: #444; margin-top: 3px; }
        .gw-logo-space { width: 150px; height: 48px; }
        .gw-rule { border-top: 2px solid #1a1a1a; margin: 6px 0 11px 0; }
        .gw-fields { width: 100%; border-collapse: collapse; }
        .gw-fields td { vertical-align: top; padding: 0 18px 7px 0; }
        .gw-fields td.last { padding-right: 0; }
        .gw-label { font-size: 7pt; letter-spacing: 1.5px; color: #555; text-transform: uppercase; margin-bottom: 2px; }
        .gw-value { border-bottom: 1px solid #8a8a8a; font-size: 9.75pt; padding: 0 2px 2px 2px; min-height: 13px; }
        .gw .waiver-body { margin-top: 5px; }
        .gw .waiver-body p { text-align: justify; margin: 0 0 7px 0; }
        .gw .waiver-notice { margin: 0 0 9px 0; padding: 5px 8px; line-height: 1.25; }
        .gw .sig-table { margin-top: 22px; }
        .gw .sig-table td { padding: 6px 24px 2px 0; }
        .gw .sig-line { border-bottom: 1px solid #1a1a1a; height: 15px; line-height: 15px; padding-bottom: 3px; }
        .gw .sig-caption { font-size: 7pt; letter-spacing: 1.5px; color: #555; text-transform: uppercase; padding-top: 3px; }
    </style>
</head>
<body>
    @include($waiver['form']['template'], ['waiver' => $waiver, 'esign' => $esign ?? null])

    @isset($certificate)
        @include('documents.lien._esign-certificate', ['certificate' => $certificate])
    @endisset
</body>
</html>
