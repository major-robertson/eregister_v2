{{--
    Generic house form: Unconditional Waiver and Release of Lien (Progress
    Payment). Effective on signing even if payment fails, so it carries a
    prominent caution box (best practice mirroring the statutory-state notices).

    Single-page layout: the operative text references the labeled fields above
    ("Amount stated above") so long values never reflow the paragraphs.
--}}
<div class="gw">
    @include('documents.lien.waivers._gw-header', [
        'waiver' => $waiver,
        'line1' => 'Unconditional Waiver',
        'line2' => 'and Release of Lien',
        'kindLabel' => 'Progress Payment',
    ])

    <div class="waiver-notice">
        Notice: This document waives rights unconditionally and states that you have been paid for giving up
        those rights. It is enforceable against you if you sign it, even if you have not been paid. If you have
        not been paid, use a conditional waiver and release form.
    </div>

    @include('documents.lien.waivers._gw-fields', ['waiver' => $waiver, 'showThrough' => true])

    <div class="waiver-body">
        <p>
            The undersigned claimant (the "Claimant") has been paid and has received a progress payment in the
            Amount stated above for labor, services, equipment, or materials furnished to the property at the
            Job Location identified above (the "Property"), and does hereby unconditionally waive and release
            any mechanic's lien, construction lien, or similar claim or right the Claimant has against the
            Property arising from labor, services, equipment, or materials furnished by the Claimant through
            the Through Date stated above.
        </p>
        <p>
            This release covers a <strong>progress payment only</strong>, and only to the extent of the Amount
            stated above. This document does not cover, and the Claimant expressly reserves: (1)&nbsp;any
            retention withheld; (2)&nbsp;amounts for extra work or materials, or for disputed claims;
            (3)&nbsp;pending or unapproved change orders or modifications;
            @if (!empty($waiver['exceptions']))
                (4)&nbsp;labor, services, equipment, or materials furnished after the Through Date; and
                (5)&nbsp;the Exceptions noted above.
            @else
                and (4)&nbsp;labor, services, equipment, or materials furnished after the Through Date.
            @endif
        </p>
        @include('documents.lien.waivers._extra-clauses', ['waiver' => $waiver])
    </div>

    @include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])
</div>
