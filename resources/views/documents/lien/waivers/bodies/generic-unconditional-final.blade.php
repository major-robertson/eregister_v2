{{--
    Generic house form: Unconditional Waiver and Release of Lien (Final
    Payment). The most consequential form: waives everything on signing, so it
    carries the caution box and keeps the exceptions reservation line.

    Single-page layout: the operative text references the labeled fields above
    ("Amount stated above") so long values never reflow the paragraphs.
--}}
<div class="gw">
    @include('documents.lien.waivers._gw-header', [
        'waiver' => $waiver,
        'line1' => 'Unconditional Waiver',
        'line2' => 'and Release of Lien',
        'kindLabel' => 'Final Payment',
    ])

    <div class="waiver-notice">
        Notice: This document waives rights unconditionally and states that you have been paid for giving up
        those rights. It is enforceable against you if you sign it, even if you have not been paid. If you have
        not been paid, use a conditional waiver and release form.
    </div>

    @include('documents.lien.waivers._gw-fields', ['waiver' => $waiver, 'showThrough' => false])

    <div class="waiver-body">
        <p>
            The undersigned claimant (the "Claimant") has been paid in full for all labor, services, equipment,
            or materials furnished to the property at the Job Location identified above (the "Property") and
            does hereby unconditionally waive and release any mechanic's lien, construction lien, or similar
            claim or right the Claimant has against the Property.
        </p>
        <p>
            This release covers the <strong>final payment</strong> in the Amount stated above to the Claimant
            for the Property.
            @if (!empty($waiver['exceptions']))
                This document does not cover, and the Claimant expressly reserves, the Exceptions noted above.
            @endif
        </p>
    </div>

    @include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])
</div>
