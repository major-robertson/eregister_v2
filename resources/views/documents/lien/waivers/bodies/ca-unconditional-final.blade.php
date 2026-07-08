{{--
    California statutory form: Unconditional Waiver and Release on Final
    Payment, Cal. Civ. Code § 8138. Statutory text reproduced verbatim from
    leginfo.legislature.ca.gov. Note the § 8138 wording differences: "for all
    labor and service provided", "The claimant has been paid in full." (no
    amount blank), and "does not affect the following" (no "any of").
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">California Civil Code § 8138</div>

<div class="waiver-notice">
    NOTICE TO CLAIMANT: THIS DOCUMENT WAIVES AND RELEASES LIEN, STOP PAYMENT NOTICE, AND PAYMENT BOND
    RIGHTS UNCONDITIONALLY AND STATES THAT YOU HAVE BEEN PAID FOR GIVING UP THOSE RIGHTS. THIS DOCUMENT IS
    ENFORCEABLE AGAINST YOU IF YOU SIGN IT, EVEN IF YOU HAVE NOT BEEN PAID. IF YOU HAVE NOT BEEN PAID, USE
    A CONDITIONAL WAIVER AND RELEASE FORM.
</div>

<div class="waiver-section-label">Identifying Information</div>
@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver, 'rows' => ['claimant', 'customer', 'job_location', 'owner']])

<div class="waiver-body">
    <div class="waiver-section-label">Unconditional Waiver and Release</div>
    <p>
        This document waives and releases lien, stop payment notice, and payment bond rights the claimant
        has for all labor and service provided, and equipment and material delivered, to the customer on
        this job. Rights based upon labor or service provided, or equipment or material delivered, pursuant
        to a written change order that has been fully executed by the parties prior to the date that this
        document is signed by the claimant, are waived and released by this document, unless listed as an
        Exception below. The claimant has been paid in full.
    </p>

    <div class="waiver-section-label">Exceptions</div>
    <p>This document does not affect the following:</p>
    <p>
        Disputed claims for extras in the amount of:
        $<span class="fill">{{ $waiver['exceptions'] ?? '' }}</span>
    </p>
</div>

<div class="waiver-section-label">Signature</div>
@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form per Cal. Civ. Code § 8138 (substantial compliance required). Form
    LW-CA-UF v{{ $waiver['form']['template_version'] }}.
</p>
