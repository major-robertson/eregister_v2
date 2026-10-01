{{--
    Certificate of compliance with the Case Records Public Access Policy of
    the Unified Judicial System of Pennsylvania, which must accompany every
    filing with the prothonotary: Sections 7.0(D) and 8.0(D) of the Policy
    (204 Pa. Code § 213.81; "The certification that shall accompany each
    filing shall be substantially in the following form") and Pa.R.C.P. 205.6.
    The certification sentence is verbatim, checked against the Policy and
    Rule 205.6 on pacodeandbulletin.gov and the AOPC Confidential Information
    Form on pacourts.us on 2026-09-30; never edit it without re-checking.

    It prints as its own last page, after the execution block, with a short
    caption so it stays with the filing. The claimant is an unrepresented
    party, so its signer signs for it; like the AOPC form, the page asks for
    the signature, date, printed name, address, telephone and email.
--}}
@php
    $form = $doc['form'];
    $signer = $doc['signer'];
    $claimant = $doc['parties']['claimant'];
    $owner = $doc['parties']['owner'];
    $county = $form['county_name'] ? mb_strtoupper($form['county_name']) : null;
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<div class="keep" style="page-break-before: always;">
    <p class="center">
        IN THE COURT OF COMMON PLEAS OF {!! $blank($county, 'fill fill-mid') !!} COUNTY, {{ mb_strtoupper($form['state_name']) }}<br>
        {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}, Claimant, v. {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}, Owner<br>
        {{ $form['title'] }}, No. <span class="fill fill-short">&nbsp;</span> of 20<span class="fill fill-short">&nbsp;</span>
    </p>

    <p class="doc-title" style="margin-top: 14pt;">CERTIFICATE OF COMPLIANCE</p>
    <p class="doc-statute">Case Records Public Access Policy of the Unified Judicial System of Pennsylvania, Sections 7.0 and 8.0</p>

    <p>I certify that this filing complies with the provisions of the Case Records Public Access Policy of the Unified Judicial System of Pennsylvania that require filing confidential information and documents differently than non-confidential information and documents.</p>

    <table class="sig-table">
        <tr>
            <td style="width: 58%;">
                <div class="small">SUBMITTED BY: {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}, Claimant</div>
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">By (signature)</div>
            </td>
            <td style="width: 42%;">
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">Date</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="sig-line">@if ($signer['name']){{ $signer['name'] }}@else&nbsp;@endif</div>
                <div class="sig-caption">Printed name</div>
            </td>
            <td>
                <div class="sig-line">@if ($signer['title']){{ $signer['title'] }}@else&nbsp;@endif</div>
                <div class="sig-caption">Title</div>
            </td>
        </tr>
    </table>

    <div class="block" style="margin-top: 12pt;">
        <div><span class="lbl">Address:</span> {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}</div>
        <div><span class="lbl">Telephone:</span> {!! $blank($claimant['phone'] ?? null) !!}</div>
        <div><span class="lbl">Email:</span> {!! $blank($claimant['email'] ?? null, 'fill fill-wide') !!}</div>
    </div>
</div>
