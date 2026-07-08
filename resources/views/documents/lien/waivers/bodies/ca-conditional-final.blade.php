{{--
    California statutory form: Conditional Waiver and Release on Final
    Payment, Cal. Civ. Code § 8136. Statutory text reproduced verbatim from
    leginfo.legislature.ca.gov. No Through Date on the final forms; the only
    statutory exception is the disputed-claims-for-extras amount.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">California Civil Code § 8136</div>

<div class="waiver-notice">
    NOTICE: THIS DOCUMENT WAIVES THE CLAIMANT'S LIEN, STOP PAYMENT NOTICE, AND PAYMENT BOND RIGHTS
    EFFECTIVE ON RECEIPT OF PAYMENT. A PERSON SHOULD NOT RELY ON THIS DOCUMENT UNLESS SATISFIED THAT THE
    CLAIMANT HAS RECEIVED PAYMENT.
</div>

<div class="waiver-section-label">Identifying Information</div>
@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver, 'rows' => ['claimant', 'customer', 'job_location', 'owner']])

<div class="waiver-body">
    <div class="waiver-section-label">Conditional Waiver and Release</div>
    <p>
        This document waives and releases lien, stop payment notice, and payment bond rights the claimant
        has for labor and service provided, and equipment and material delivered, to the customer on this
        job. Rights based upon labor or service provided, or equipment or material delivered, pursuant to a
        written change order that has been fully executed by the parties prior to the date that this
        document is signed by the claimant, are waived and released by this document, unless listed as an
        Exception below. This document is effective only on the claimant's receipt of payment from the
        financial institution on which the following check is drawn:
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
    <p>
        Disputed claims for extras in the amount of:
        $<span class="fill">{{ $waiver['exceptions'] ?? '' }}</span>
    </p>
</div>

<div class="waiver-section-label">Signature</div>
@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form per Cal. Civ. Code § 8136 (substantial compliance required). Form
    LW-CA-CF v{{ $waiver['form']['template_version'] }}.
</p>
