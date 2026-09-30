{{--
    New Jersey Construction Lien Claim, the form of N.J.S.A. 2A:44A-8 ("The
    lien claim shall be filed in substantially the following form"), verbatim
    from the New Jersey Legislature's statutes database (lis.njleg.state.nj.us,
    updated through P.L.2026, c.30), verified 2026-09-30, with live data in
    its blanks. The form's "(circle one)" choices print resolved from the
    project (the signer's capacity, the contracting party, residential or
    not) and fall back to the form's own words or boxes when the data cannot
    tell. The community association alternatives are left out: eRegister
    files against the owner's interest. Block and Lot and the owner's
    interest come from Document details, the municipality from the jobsite
    city; items 5 and 6 (the Notice of Unpaid Balance and the arbitrator's
    award) are filled by hand on residential work.

    N.J.S.A. 2A:44A-6(a)(1) wants the claim "signed, acknowledged and
    verified by oath", so the shell's execution block prints the signature
    and a jurat, and clauses.after_execution adds the form's suggested
    notarial (nj-lien-claim-notarial, an acknowledgment) and its Notice to
    Owner of Real Property (nj-notice-to-owner). Never edit the wording
    without re-checking the statute.
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

    // Item 1, "(circle one and fill in name as applicable)": an entity claimant signs through
    // an officer or member (a partner for a partnership); without a company name, individually.
    $company = $claimant['company'] ?? null;
    $isEntity = $company !== null && $company !== ($claimant['name'] ?? null);
    $isPartnership = $isEntity && preg_match('/\b(partnership|l\.?l\.?l?\.?p|l\.?p)\.?$/i', trim($company)) === 1;
    $signerName = $signer['name'] ?? ($isEntity ? null : ($claimant['name'] ?? null));

    // Item 2, "with the property owner, community association, contractor, or subcontractor (circle one)".
    $hiringIsOwner = $hiring !== null && $owner !== null && $hiring['display_name'] !== null && $hiring['display_name'] === $owner['display_name'];
    $contractedWith = match (true) {
        $hiringIsOwner, $project['hired_by'] === 'owner' => 'property owner',
        $project['hired_by'] === 'direct_contractor' => 'contractor',
        $project['hired_by'] === 'subcontractor' => 'subcontractor',
        in_array($project['claimant_type'], ['gc', 'supplier_to_owner'], true) => 'property owner',
        in_array($project['claimant_type'], ['subcontractor', 'supplier_to_contractor'], true) => 'contractor',
        in_array($project['claimant_type'], ['sub_sub_contractor', 'supplier_to_subcontractor'], true) => 'subcontractor',
        default => null,
    };

    // Item 4: C = A + B; D, the value of the work completed, only when work was left uncompleted; E = D or C.
    $contract = $amounts['contract']['cents'];
    $totalPrice = $contract === null ? null : $contract + ($amounts['change_orders']['cents'] ?? 0);
    $uncompleted = $amounts['uncompleted']['cents'];
    $completedValue = $totalPrice !== null && $uncompleted !== null && $uncompleted > 0 ? $totalPrice - $uncompleted : null;
    $basis = $completedValue ?? $totalPrice;

    // "This claim (check one) does ___ does not ___ arise from a Residential Construction Contract."
    $residential = match ($project['property_class']) {
        'residential' => true,
        'commercial', 'government' => false,
        default => null,
    };
@endphp
<p><strong>TO THE CLERK, COUNTY OF {!! $blank($county === null ? null : mb_strtoupper($county), 'fill fill-mid') !!}:</strong></p>

<p>In accordance with the "Construction Lien Law," P.L.1993, c.318 (C.2A:44A-1 et al.), notice is hereby given that (only complete those sections that apply):</p>

<table class="item">
    <tr>
        <td class="n">1.</td>
        <td>
            On {{ $doc['date'] }}, I, {!! $blank($signerName, 'fill fill-wide') !!},
            @if ($isPartnership)
                as a partner of the claimant known as {{ $company }},
            @elseif ($isEntity)
                an officer/member of the claimant known as {{ $company }},
            @else
                individually,
            @endif
            located at {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}, claim a construction lien against the real property of {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}, in that certain tract or parcel of land and premises described as Block {!! $blank($details['block'], 'fill fill-short') !!}, Lot {!! $blank($details['lot'], 'fill fill-short') !!}, on the tax map of the municipality of {!! $blank($project['address']['city'], 'fill fill-mid') !!}, County of {!! $blank($county, 'fill fill-mid') !!}, State of New Jersey, in the amount of ${!! $blank($filing['amount'], 'fill fill-mid') !!}, as calculated below for the value of the work, services, material or equipment provided. The lien is claimed against the interest of the owner{{ $details['owner_interest'] ? ' ('.$details['owner_interest'].')' : '' }}.
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
            In accordance with a written contract for improvement of the above property, dated {!! $blank($details['contract_date'], 'fill fill-mid') !!}, with the
            @if ($contractedWith)
                {{ $contractedWith }},
            @else
                property owner, community association, contractor, or subcontractor (circle one),
            @endif
            named or known as {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}, and located at {!! $blank($hiring['address_line'] ?? null, 'fill fill-wide') !!}, this claimant performed the following work or provided the following services, material or equipment:
            <div style="margin-top: 4pt;">@include('documents.lien._parts.work')</div>
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">3.</td>
        <td>The date of the provision of the last work, services, material or equipment for which payment is claimed is {!! $blank($project['dates']['last_furnish'], 'fill fill-mid') !!}.</td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">4.</td>
        <td>
            The amount due for work, services, material or equipment delivery provided by claimant in connection with the improvement of the real property, and upon which this lien claim is based, is calculated as follows:
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
                    <td>If Contract Not Completed, Value Determined in Accordance with the Contract of Work Completed or Services, Material, Equipment Provided:</td>
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

<div class="keep">
    <p class="center" style="margin-top: 10pt;"><strong>NOTICE OF UNPAID BALANCE AND ARBITRATION AWARD</strong></p>

    @if ($residential === true)
        <p>This claim does arise from a Residential Construction Contract.</p>
    @elseif ($residential === false)
        <p>This claim does not arise from a Residential Construction Contract.</p>
    @else
        <p>This claim (check one) <span class="box"></span> does <span class="box"></span> does not arise from a Residential Construction Contract. If it does, complete 5 and 6 below; if not residential, complete 5 below, only if applicable. If not residential and 5 is not applicable, skip to Claimant's Representation and Verification.</p>
    @endif

    <table class="item">
        <tr>
            <td class="n">5.</td>
            <td>A Notice of Unpaid Balance and Right to File Lien (if any) was previously filed with the County Clerk of {!! $blank($county, 'fill fill-mid') !!} County on <span class="fill fill-mid">&nbsp;</span>, 20<span class="fill fill-short">&nbsp;</span> as No. <span class="fill fill-short">&nbsp;</span>, in Book <span class="fill fill-short">&nbsp;</span> and Page <span class="fill fill-short">&nbsp;</span>.</td>
        </tr>
    </table>

    @if ($residential !== false)
        <table class="item">
            <tr>
                <td class="n">6.</td>
                <td>An award of the arbitrator (if residential) was issued on <span class="fill fill-mid">&nbsp;</span> in the amount of $<span class="fill fill-mid">&nbsp;</span>.</td>
            </tr>
        </table>
    @endif
</div>

<div class="keep">
    <p class="center" style="margin-top: 10pt;"><strong>CLAIMANT'S REPRESENTATION AND VERIFICATION</strong></p>

    <p>Claimant represents and verifies under oath that:</p>

    <table class="item">
        <tr><td class="n">1.</td><td>I have authority to file this claim.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">2.</td><td>The claimant is entitled to the amount claimed at the date of lodging for record of the claim, pursuant to claimant's contract described above.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">3.</td><td>The work, services, material or equipment for which this lien claim is filed was provided exclusively in connection with the improvement of the real property which is the subject of this claim.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">4.</td><td>This claim form has been lodged for record with the County Clerk where the property is located within 90 or, if residential construction, 120 days from the last date upon which the work, services, material or equipment for which payment is claimed was provided.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">5.</td><td>This claim form has been completed in its entirety to the best of my ability and I understand that if I do not complete this form in its entirety, the form may be deemed invalid by a court of law.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">6.</td><td>This claim form will be served as required by statute upon the owner or community association, and upon the contractor or subcontractor against whom this claim has been asserted, if any.</td></tr>
    </table>
    <table class="item">
        <tr><td class="n">7.</td><td>The foregoing statements made by me in this claim form are true, to the best of my knowledge. I am aware that if any of the foregoing statements made by me in this claim form are willfully false, this construction lien claim will be void and that I will be liable for damages to the owner or any other person injured as a consequence of the filing of this lien claim.</td></tr>
    </table>
</div>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
