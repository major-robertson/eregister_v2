{{--
    South Carolina Verified Statement of Account: the sworn account page that
    follows the Notice and Certificate of Mechanic's Lien. S.C. Code Ann.
    § 29-5-90 asks for "a statement of a just and true account of the amount
    due him, with all just credits given", subscribed and sworn to by the
    claimant or someone on its behalf, and prescribes no form. The page takes
    the shape of the Berkeley County clerk's own sample ("Sample SC ML w
    statement of acct from courthouse", May 2026): caption, account balance
    as of the last furnishing, payments received, balance due with its
    footnote, the certification, the claimant's signature and a jurat of its
    own. The certification sentence is the clerk's sample wording, not
    statute; the labels are house wording. The account balance is the
    contract with its additions, less credits and work not completed, so that
    it less the payments equals the amount claimed.
--}}
@php
    $form = $doc['form'];
    $project = $doc['project'];
    $amounts = $project['amounts'];
    $claimantName = $doc['parties']['claimant']['display_name'] ?? null;
    $ownerName = $doc['parties']['owner']['display_name'] ?? null;
    $signerName = $doc['signer']['name'];
    $signerTitle = $doc['signer']['title'];
    $capacity = ($signerTitle ?: 'authorized representative').($claimantName ? ' of '.$claimantName : '');
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    $contract = $amounts['contract']['cents'];
    $balance = $contract === null ? null : number_format((
        $contract
        + ($amounts['change_orders']['cents'] ?? 0)
        - ($amounts['credits']['cents'] ?? 0)
        - ($amounts['uncompleted']['cents'] ?? 0)
    ) / 100, 2);
@endphp
<p class="doc-title" style="page-break-before: always;">VERIFIED STATEMENT OF ACCOUNT</p>

<div class="venue" style="margin-top: 8pt;">CLERK OF COURT/REGISTER OF DEEDS</div>
<table class="caption">
    <tr>
        <td style="width: 40%;" class="venue">
            STATE OF {{ mb_strtoupper($form['state_name']) }}<br>
            COUNTY OF {!! $blank($project['county'] ? mb_strtoupper($project['county']) : null, 'fill fill-mid') !!}
        </td>
        <td style="width: 60%;">
            <table class="index">
                <tr>
                    <td class="k">Claimant</td>
                    <td>{!! $blank($claimantName, 'fill fill-wide') !!}</td>
                </tr>
                <tr>
                    <td class="k">Owner</td>
                    <td>{!! $blank($ownerName, 'fill fill-wide') !!}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="amounts" style="width: 100%;">
    <tr>
        <td>Account balance as of {!! $blank($project['dates']['last_furnish'], 'fill fill-mid') !!}:</td>
        <td class="money" style="width: 1.7in;">${!! $blank($balance, 'fill fill-mid') !!}</td>
    </tr>
    <tr>
        <td>Payments received:</td>
        <td class="money">${!! $blank($amounts['payments']['formatted'], 'fill fill-mid') !!}</td>
    </tr>
    <tr class="total">
        <td>Balance due as of {{ $doc['date'] }}:</td>
        <td class="money">${!! $blank($doc['filing']['amount'], 'fill fill-mid') !!} *</td>
    </tr>
</table>
<p class="small">* Plus interest, attorney's fees and costs.</p>

<p>I HEREBY CERTIFY that the foregoing is a true and correct statement of account due to Petitioner in connection with this Mechanics' Lien.</p>

<div class="small" style="margin-top: 10pt;">CLAIMANT:@if ($claimantName) {{ $claimantName }}@endif</div>
<table class="sig-table" style="margin-top: 0;">
    <tr>
        <td style="width: 40%;">
            <div class="sig-line">&nbsp;</div>
            <div class="sig-caption">By (signature)</div>
        </td>
        <td style="width: 32%;">
            <div class="sig-line">@if ($signerName){{ $signerName }}@else&nbsp;@endif</div>
            <div class="sig-caption">Printed name</div>
        </td>
        <td style="width: 28%;">
            <div class="sig-line">@if ($signerTitle){{ $signerTitle }}@else&nbsp;@endif</div>
            <div class="sig-caption">Title</div>
        </td>
    </tr>
</table>

<div class="notary">
    <div class="venue-lines">STATE OF <span class="fill fill-mid">&nbsp;</span><br>COUNTY OF <span class="fill fill-mid">&nbsp;</span></div>
    <p>Subscribed and sworn to (or affirmed) before me on this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, 20<span class="fill fill-short">&nbsp;</span>, by {!! $blank($signerName, 'fill fill-wide') !!}, {{ $capacity }}, who is personally known to me or who produced <span class="fill fill-mid">&nbsp;</span> as identification.</p>
    @include('documents.lien._parts.notary-lines')
</div>
