{{--
    California statutory form: Conditional Waiver and Release on Progress
    Payment, Cal. Civ. Code § 8132. Statutory text reproduced verbatim from
    leginfo.legislature.ca.gov ("null, void, and unenforceable unless it is in
    substantially the following form"); only blanks are bound to payload
    fields. Do not add a notary block, conditions, or scope changes.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">California Civil Code § 8132</div>

<div class="waiver-notice">
    NOTICE: THIS DOCUMENT WAIVES THE CLAIMANT'S LIEN, STOP PAYMENT NOTICE, AND PAYMENT BOND RIGHTS
    EFFECTIVE ON RECEIPT OF PAYMENT. A PERSON SHOULD NOT RELY ON THIS DOCUMENT UNLESS SATISFIED THAT THE
    CLAIMANT HAS RECEIVED PAYMENT.
</div>

<div class="waiver-section-label">Identifying Information</div>
@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver, 'rows' => ['claimant', 'customer', 'job_location', 'owner', 'through_date']])

<div class="waiver-body">
    <div class="waiver-section-label">Conditional Waiver and Release</div>
    <p>
        This document waives and releases lien, stop payment notice, and payment bond rights the claimant
        has for labor and service provided, and equipment and material delivered, to the customer on this
        job through the Through Date of this document. Rights based upon labor or service provided, or
        equipment or material delivered, pursuant to a written change order that has been fully executed by
        the parties prior to the date that this document is signed by the claimant, are waived and released
        by this document, unless listed as an Exception below. This document is effective only on the
        claimant's receipt of payment from the financial institution on which the following check is drawn:
    </p>
</div>

<table class="waiver-fields">
    <tr>
        <td class="label">Maker of Check:</td>
        <td class="value">{{ $waiver['check_maker'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Amount of Check:</td>
        <td class="value">${{ $waiver['amount'] ?? '' }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Check Payable to:</td>
        <td class="value">{{ $waiver['claimant']['company'] ?? '' }}&nbsp;</td>
    </tr>
</table>

<div class="waiver-body">
    <div class="waiver-section-label">Exceptions</div>
    <p>This document does not affect any of the following:</p>
    <p>(1) Retentions.</p>
    <p>(2) Extras for which the claimant has not received payment.</p>
    <p>
        (3) The following progress payments for which the claimant has previously given a conditional
        waiver and release but has not received payment:
    </p>
    <p>
        Date(s) of waiver and release: <span class="fill-wide">&nbsp;</span><br>
        Amount(s) of unpaid progress payment(s): $<span class="fill">&nbsp;</span>
    </p>
    <p>
        (4) Contract rights, including (A) a right based on rescission, abandonment, or breach of contract,
        and (B) the right to recover compensation for work not compensated by the payment.
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>Additional exceptions reserved by the claimant:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
</div>

<div class="waiver-section-label">Signature</div>
@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form per Cal. Civ. Code § 8132 (substantial compliance required). Form
    LW-CA-CP v{{ $waiver['form']['template_version'] }}.
</p>
