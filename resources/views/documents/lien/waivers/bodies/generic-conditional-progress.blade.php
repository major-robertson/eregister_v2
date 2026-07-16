{{--
    Generic house form: Conditional Waiver and Release of Lien (Progress
    Payment). For states with no statutory waiver form. Conservative scope:
    effective only on payment, limited to the amount and through-date, and
    expressly reserves retention, disputed/extra work, and pending changes.

    Single-page layout: the operative text references the labeled fields above
    ("Amount stated above") so long values never reflow the paragraphs.
--}}
<div class="gw">
    @include('documents.lien.waivers._gw-header', [
        'waiver' => $waiver,
        'line1' => 'Conditional Waiver',
        'line2' => 'and Release of Lien',
        'kindLabel' => 'Progress Payment',
    ])

    @include('documents.lien.waivers._gw-fields', ['waiver' => $waiver, 'showThrough' => true])

    <div class="waiver-body">
        <p>
            Upon receipt by the undersigned claimant (the "Claimant") of a check in the Amount stated above,
            drawn by the maker identified above (or, if none is identified, by the Customer named above) and
            payable to the Claimant, and when that check has been properly endorsed and paid by the bank on
            which it is drawn, this document shall become effective to waive and release any mechanic's lien,
            construction lien, or similar claim or right the Claimant has against the property at the Job
            Location identified above (the "Property") arising from labor, services, equipment, or materials
            furnished by the Claimant to the Property.
        </p>
        <p>
            This release covers a <strong>progress payment only</strong>, for labor, services, equipment, or
            materials furnished through the Through Date stated above, and only to the extent of the Amount
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
        <p>
            Before any recipient of this document relies on it, that person should verify evidence of payment
            to the Claimant. This document is not effective until the payment identified above has actually
            been received.
        </p>
    </div>

    @include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])
</div>
