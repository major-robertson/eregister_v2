{{--
    Florida conditional final waiver: the Fla. Stat. § 713.20(5) safe-harbor
    form (reproduced verbatim) PLUS the lienor-elected condition permitted by
    § 713.20(7): a lienor who executes a waiver in exchange for a check "may
    condition the waiver and release on payment of the check." Florida has
    no separate statutory conditional form; this modification is the
    lienor's election (enforceable per its terms under § 713.20(8)), and a
    payor may not require it or any other non-statutory form (§ 713.20(6)).
    Note: the final form has NO retention carve-out; once effective it
    waives the lien for everything furnished, including retainage.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of Florida: Fla. Stat. &sect; 713.20(5) form, conditioned as permitted by &sect; 713.20(7)</div>

@php
    $flProperty = implode('; ', array_filter([
        $waiver['project']['address_line'] ?? null,
        !empty($waiver['project']['county']) ? $waiver['project']['county'].' County, Florida' : null,
        $waiver['project']['legal_description'] ?? null,
        !empty($waiver['project']['apn']) ? 'Parcel ID '.$waiver['project']['apn'] : null,
    ]));
@endphp

<div class="waiver-body">
    <p>
        The undersigned lienor, in consideration of the final payment in the amount of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span>, hereby waives and releases its lien and
        right to claim a lien for labor, services, or materials furnished to
        <span class="fill-wide">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(insert the name of your customer)</span> on the job of
        <span class="fill-wide">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(insert the name of the owner)</span> to the following
        described property:
    </p>
    <p style="text-align: center; margin: 10px 0;">
        <span class="fill-wide">{{ $flProperty }}</span><br>
        <span style="font-size: 8pt; color: #444;">(description of property)</span>
    </p>
    <p>
        <strong>Condition of payment (Fla. Stat. &sect; 713.20(7)).</strong> The undersigned lienor
        executes this waiver and release in exchange for a check and, as permitted by section 713.20(7),
        Florida Statutes, conditions this waiver and release on payment of that check: check
        No. <span class="fill">{{ $waiver['check_number'] ?? '' }}</span> drawn by
        <span class="fill">{{ $waiver['check_maker'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(maker of check)</span> in the amount of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span> payable to
        <span class="fill-wide">{{ $waiver['claimant']['company'] ?? '' }}</span>. Notwithstanding any
        other language in this document, this waiver and release becomes effective only when the check
        identified above has been properly endorsed and has been paid by the bank on which it is drawn.
        Before any recipient relies on this document, that person should verify that the check has been
        paid.
    </p>
    @if (!empty($waiver['exceptions']))
        <p>
            <strong>Exceptions specified at the time of release (Fla. Stat. &sect; 713.20(3)):</strong>
            {{ $waiver['exceptions'] }}
        </p>
    @endif
    <p>
        DATED on <span class="fill">{{ $waiver['date'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(date, year)</span>.
    </p>
</div>

<p style="margin: 16px 0 0 0;">
    <span class="fill-wide">{{ $waiver['claimant']['company'] ?? '' }}</span><br>
    <span style="font-size: 8pt; color: #444;">(Lienor)</span>
</p>
@if (!empty($waiver['claimant']['address_lines']))
    <p style="margin: 2px 0 0 0; font-size: 9pt;">
        @foreach ($waiver['claimant']['address_lines'] as $line){{ $line }}<br>@endforeach
    </p>
@endif

<p style="margin: 12px 0 0 0;">By:</p>
@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<p class="waiver-foot">
    Fla. Stat. &sect; 713.20(5) safe-harbor form with the check-payment condition the lienor may elect
    under &sect; 713.20(7); enforceable in accordance with its terms (&sect; 713.20(8)). No notarization
    or witness is required. Once effective, this final waiver covers everything furnished, including
    retention; it does not release payment-bond rights (bond waivers use the separate forms in
    Fla. Stat. &sect; 713.235 and &sect; 255.05(2)).
    Form LW-FL-CF v{{ $waiver['form']['template_version'] }}.
</p>
