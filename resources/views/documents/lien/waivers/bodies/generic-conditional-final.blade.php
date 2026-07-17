{{--
    Generic house form: Conditional Waiver and Release of Lien (Final
    Payment). Effective only when the final payment actually clears; reserves
    listed exceptions.

    Single-page layout: the operative text references the labeled fields above
    ("Amount stated above") so long values never reflow the paragraphs.
--}}
<div class="gw">
    @include('documents.lien.waivers._gw-header', [
        'waiver' => $waiver,
        'line1' => 'Conditional Waiver',
        'line2' => 'and Release of Lien',
        'kindLabel' => 'Final Payment',
    ])

    @include('documents.lien.waivers._gw-fields', ['waiver' => $waiver, 'showThrough' => false])

    <div class="waiver-body">
        <p>
            Upon receipt by the undersigned claimant (the "Claimant") of a check in the Amount stated above,
            drawn by the maker identified above (or, if none is identified, by the Customer named above) and
            payable to the Claimant, and when that check has been properly endorsed and paid by the bank on
            which it is drawn, this document shall become effective to waive and release any mechanic's lien,
            construction lien, or similar claim or right the Claimant has against the property at the Job
            Location identified above (the "Property") arising from all labor, services, equipment, or
            materials furnished by the Claimant to the Property.
        </p>
        <p>
            This release covers the <strong>final payment</strong> to the Claimant for the Property.
            @if (!empty($waiver['exceptions']))
                This document does not cover, and the Claimant expressly reserves, the Exceptions noted above.
            @endif
        </p>
        <p>
            Before any recipient of this document relies on it, that person should verify evidence of payment
            to the Claimant. This document is not effective until the payment identified above has actually
            been received.
        </p>
        @include('documents.lien.waivers._extra-clauses', ['waiver' => $waiver])
    </div>

    @include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])
</div>
