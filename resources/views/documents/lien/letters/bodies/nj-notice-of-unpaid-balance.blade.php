{{--
    New Jersey Notice of Unpaid Balance and Right to File Lien, the form of
    N.J.S.A. 2A:44A-20(b) ("shall be filed in substantially the following
    form"), verbatim from the New Jersey Legislature's statutes database
    (lis.njleg.state.nj.us, updated through P.L.2026, c.30), verified
    2026-09-30, with live data in its blanks and the "(circle one)" choices
    resolved as on the lien claim (bodies/nj-construction-lien-claim). The
    letter shell prints the title before the form's "TO THE CLERK" line, the
    parties served (on residential construction it is served like a lien
    claim, N.J.S.A. 2A:44A-21(b)(2)), the signature and the acknowledgment
    the county clerk needs to lodge it (N.J.S.A. 46:26A-3(a)(3)); the form's
    verification is not under oath. Block and Lot come from Document details
    and print as blanks when missing. Never edit the wording without
    re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $details = $doc['details'];
    $signer = $doc['signer'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $amounts = $project['amounts'];
    $county = $project['county'];

    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    $money = fn (?int $cents) => $cents === null ? null : number_format($cents / 100, 2);

    // Item 1, "(Please circle one and fill in name as applicable)".
    $company = $claimant['company'] ?? null;
    $isEntity = $company !== null && $company !== ($claimant['name'] ?? null);
    $isPartnership = $isEntity && preg_match('/\b(partnership|l\.?l\.?l?\.?p|l\.?p)\.?$/i', trim($company)) === 1;
    $signerName = $signer['name'] ?? ($isEntity ? null : ($claimant['name'] ?? null));

    // Item 2, "between (claimant) and owner, unit owner, community association, contractor or subcontractor (circle one)".
    $hiringIsOwner = $hiring !== null && $owner !== null && $hiring['display_name'] !== null && $hiring['display_name'] === $owner['display_name'];
    $contractedWith = match (true) {
        $hiringIsOwner, $project['hired_by'] === 'owner' => 'owner',
        $project['hired_by'] === 'direct_contractor' => 'contractor',
        $project['hired_by'] === 'subcontractor' => 'subcontractor',
        in_array($project['claimant_type'], ['gc', 'supplier_to_owner'], true) => 'owner',
        in_array($project['claimant_type'], ['subcontractor', 'supplier_to_contractor'], true) => 'contractor',
        in_array($project['claimant_type'], ['sub_sub_contractor', 'supplier_to_subcontractor'], true) => 'subcontractor',
        default => null,
    };

    // Item 5: C = A + B; D, the value of the work completed, only when work was left uncompleted; E = D or C.
    $contract = $amounts['contract']['cents'];
    $totalPrice = $contract === null ? null : $contract + ($amounts['change_orders']['cents'] ?? 0);
    $uncompleted = $amounts['uncompleted']['cents'];
    $completedValue = $totalPrice !== null && $uncompleted !== null && $uncompleted > 0 ? $totalPrice - $uncompleted : null;
    $basis = $completedValue ?? $totalPrice;

    // Item 6, "(is) (is not) (cross out inapplicable portion)".
    $residential = match ($project['property_class']) {
        'residential' => true,
        'commercial', 'government' => false,
        default => null,
    };
@endphp
<p><strong>TO THE CLERK, COUNTY OF {!! $blank($county === null ? null : mb_strtoupper($county), 'fill fill-mid') !!}:</strong></p>

<p>In accordance with the "Construction Lien Law," P.L.1993, c.318 (C.2A:44A-1 et al.), notice is hereby given that:</p>

<table class="item">
    <tr>
        <td class="n">1.</td>
        <td>
            {!! $blank($signerName, 'fill fill-wide') !!},
            @if ($isPartnership)
                as a partner of the claimant known as {{ $company }},
            @elseif ($isEntity)
                an officer/member of the claimant known as {{ $company }},
            @else
                individually,
            @endif
            located at {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}, has on {{ $doc['date'] }} a potential construction lien against the real property of {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}, in that certain tract or parcel of land and premises described as Block {!! $blank($details['block'], 'fill fill-short') !!}, Lot {!! $blank($details['lot'], 'fill fill-short') !!}, on the tax map of the municipality of {!! $blank($project['address']['city'], 'fill fill-mid') !!}, County of {!! $blank($county, 'fill fill-mid') !!}, State of New Jersey, in the amount of (${!! $blank($filing['amount'], 'fill fill-mid') !!}), as calculated below for the value of the work, services, material or equipment provided. The lien is to be claimed against the interest of the owner{{ $details['owner_interest'] ? ' ('.$details['owner_interest'].')' : '' }}.
            <div class="block" style="margin-top: 4pt;">
                @if ($project['address']['single_line'])
                    <div><span class="lbl">Street address:</span> {{ $project['address']['single_line'] }}</div>
                @endif
                @if ($project['legal_description'])
                    <div><span class="lbl">Legal description:</span> {!! nl2br(e($project['legal_description'])) !!}</div>
                @endif
                @if ($project['apn'])
                    <div><span class="lbl">{{ $project['parcel_label'] }}:</span> {{ $project['apn'] }}</div>
                @endif
            </div>
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">2.</td>
        <td>
            The work, services, material or equipment was provided pursuant to the terms of a written contract (or, in the case of a supplier, a delivery or order slip signed by the owner, community association, contractor, or subcontractor having a direct contractual relation with a contractor, or an authorized agent of any of them), dated {!! $blank($details['contract_date'], 'fill fill-mid') !!}, between {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!} and
            @if ($contractedWith)
                {{ $contractedWith }},
            @else
                owner, unit owner, community association, contractor or subcontractor (circle one),
            @endif
            named or known as {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!} and located at {!! $blank($hiring['address_line'] ?? null, 'fill fill-wide') !!}, in the total contract amount of (${!! $blank($money($contract), 'fill fill-mid') !!}) together with (if applicable) amendments to the total contract amount aggregating (${!! $blank($money($amounts['change_orders']['cents']), 'fill fill-mid') !!}).
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">3.</td>
        <td>
            In accordance with the above contract, this claimant performed the following work or provided the following services, material or equipment:
            <div style="margin-top: 4pt;">@include('documents.lien._parts.work')</div>
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">4.</td>
        <td>The date of the provision of the last work, services, material or equipment for which payment is claimed is {!! $blank($project['dates']['last_furnish'], 'fill fill-mid') !!}.</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">5.</td>
        <td>
            The amount due for work, services, material or equipment provided by claimant in connection with the improvement of the real property, and upon which this lien claim is based is calculated as follows:
            <table class="amounts">
                <tr>
                    <td>A.</td>
                    <td>Initial Contract Price:</td>
                    <td class="money">${!! $blank($money($contract), 'fill fill-mid') !!}</td>
                </tr>
                <tr>
                    <td>B.</td>
                    <td>Executed Amendments to Contract Price/Change Orders:</td>
                    <td class="money">${!! $blank($money($amounts['change_orders']['cents']), 'fill fill-mid') !!}</td>
                </tr>
                <tr>
                    <td>C.</td>
                    <td>Total Contract Price (A + B) =</td>
                    <td class="money">${!! $blank($money($totalPrice), 'fill fill-mid') !!}</td>
                </tr>
                <tr>
                    <td>D.</td>
                    <td>If Contract Not Completed, Value Determined in Accordance with Contract of Work Completed or Services, Material or Equipment Provided:</td>
                    <td class="money">{!! $blank($completedValue === null ? null : '$'.$money($completedValue), 'fill fill-mid') !!}</td>
                </tr>
                <tr>
                    <td>E.</td>
                    <td>Total from C or D (whichever is applicable):</td>
                    <td class="money">${!! $blank($money($basis), 'fill fill-mid') !!}</td>
                </tr>
                <tr>
                    <td>F.</td>
                    <td>Agreed upon Credits:</td>
                    <td class="money">${!! $blank($money($amounts['credits']['cents']), 'fill fill-mid') !!}</td>
                </tr>
                <tr>
                    <td>G.</td>
                    <td>Amount Paid to Date:</td>
                    <td class="money">${!! $blank($money($amounts['payments']['cents']), 'fill fill-mid') !!}</td>
                </tr>
                <tr class="total">
                    <td></td>
                    <td>TOTAL LIEN CLAIM AMOUNT E - [F + G] =</td>
                    <td class="money">${!! $blank($filing['amount'], 'fill fill-mid') !!}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">6.</td>
        <td>
            The written contract
            @if ($residential === true)
                is
            @elseif ($residential === false)
                is not
            @else
                (is) (is not) (cross out inapplicable portion)
            @endif
            a residential construction contract as defined in section 2 of P.L.1993, c.318 (C.2A:44A-2).
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">7.</td>
        <td>This notification has been lodged for record prior or subsequent to completion of the work, services, material or equipment as described above. The purpose of this notification is to advise the owner or community association and any other person who is attempting to encumber or take transfer of said property described above that a potential construction lien may be lodged for record within the 90-day period, or in the case of a residential construction contract within the 120-day period, following the date of the provision of the last work, services, material or equipment as set forth in paragraph 4 of this notice.</td>
    </tr>
</table>

<div class="keep">
    <p class="center" style="margin-top: 10pt;"><strong>CLAIMANT'S REPRESENTATION AND VERIFICATION</strong></p>

    <p>Claimant represents and verifies that:</p>

    <table class="item">
        <tr><td class="n">1.</td><td>I have authority to file this Notice of Unpaid Balance and Right to File Lien.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">2.</td><td>The claimant is entitled to the amount claimed herein at the date this Notice is lodged for record, pursuant to claimant's contract described in the Notice of Unpaid Balance and Right to File Lien.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">3.</td><td>The work, services, material or equipment for which this Notice of Unpaid Balance and Right to File Lien is filed was provided exclusively in connection with the improvement of the real property which is the subject of this Notice of Unpaid Balance and Right to File Lien.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">4.</td><td>The Notice of Unpaid Balance and Right to File Lien has been lodged for record within 90 days, or in the case of a residential construction contract within 60 days, from the last date upon which the work, services, material or equipment for which payment is claimed was provided.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">5.</td><td>The foregoing statements made by me are true, to the best of my knowledge.</td></tr>
    </table>
</div>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
