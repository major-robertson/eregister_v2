{{--
    California statutory form: Unconditional Waiver and Release on Progress
    Payment, Cal. Civ. Code § 8134. Statutory text reproduced verbatim from
    leginfo.legislature.ca.gov. The "Notice to Claimant" must be in at least as
    large a type as the largest type otherwise in the form; the shell renders
    .waiver-notice at 12pt bold and caps all other type at 12pt.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">California Civil Code § 8134</div>

<div class="waiver-notice">
    NOTICE TO CLAIMANT: THIS DOCUMENT WAIVES AND RELEASES LIEN, STOP PAYMENT NOTICE, AND PAYMENT BOND
    RIGHTS UNCONDITIONALLY AND STATES THAT YOU HAVE BEEN PAID FOR GIVING UP THOSE RIGHTS. THIS DOCUMENT IS
    ENFORCEABLE AGAINST YOU IF YOU SIGN IT, EVEN IF YOU HAVE NOT BEEN PAID. IF YOU HAVE NOT BEEN PAID, USE
    A CONDITIONAL WAIVER AND RELEASE FORM.
</div>

<div class="waiver-section-label">Identifying Information</div>
@include('documents.lien.waivers._identification-fields', ['waiver' => $waiver, 'rows' => ['claimant', 'customer', 'job_location', 'owner', 'through_date']])

<div class="waiver-body">
    <div class="waiver-section-label">Unconditional Waiver and Release</div>
    <p>
        This document waives and releases lien, stop payment notice, and payment bond rights the claimant
        has for labor and service provided, and equipment and material delivered, to the customer on this
        job through the Through Date of this document. Rights based upon labor or service provided, or
        equipment or material delivered, pursuant to a written change order that has been fully executed by
        the parties prior to the date that this document is signed by the claimant, are waived and released
        by this document, unless listed as an Exception below. The claimant has received the following
        progress payment: $<span class="fill">{{ $waiver['amount'] ?? '' }}</span>
    </p>

    <div class="waiver-section-label">Exceptions</div>
    <p>This document does not affect any of the following:</p>
    <p>(1) Retentions.</p>
    <p>(2) Extras for which the claimant has not received payment.</p>
    <p>
        (3) Contract rights, including (A) a right based on rescission, abandonment, or breach of contract,
        and (B) the right to recover compensation for work not compensated by the payment.
    </p>
    @if (!empty($waiver['exceptions']))
        <p><strong>Additional exceptions reserved by the claimant:</strong> {{ $waiver['exceptions'] }}</p>
    @endif
</div>

<div class="waiver-section-label">Signature</div>
@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Statutory form per Cal. Civ. Code § 8134 (substantial compliance required). Form
    LW-CA-UP v{{ $waiver['form']['template_version'] }}.
</p>
