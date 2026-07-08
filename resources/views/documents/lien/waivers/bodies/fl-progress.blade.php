{{--
    Florida statutory safe-harbor form: Waiver and Release of Lien Upon
    Progress Payment, Fla. Stat. § 713.20(4) ("the waiver or release may be
    in substantially the following form"). Statutory text reproduced verbatim
    with only the blanks bound to payload fields, including the mandatory
    exclusion sentence for retention and after-date work. Do not remove it.
    UNCONDITIONAL on its face: it releases lien rights on execution even if
    the check is never paid. No notarization or witness is required.
--}}
<div class="waiver-title">{{ $waiver['form']['title'] }}</div>
<div class="waiver-statute">State of Florida: Statutory form, Fla. Stat. &sect; 713.20(4)</div>

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
        The undersigned lienor, in consideration of the sum of
        $<span class="fill">{{ $waiver['amount'] ?? '' }}</span>, hereby waives and releases its lien and
        right to claim a lien for labor, services, or materials furnished through
        <span class="fill">{{ $waiver['through_date'] ?? '' }}</span>
        <span style="font-size: 8pt; color: #444;">(insert date)</span> to
        <span class="fill-wide">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(insert the name of your customer)</span> on the job of
        <span class="fill-wide">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}</span>
        <span style="font-size: 8pt; color: #444;">(insert the name of the owner)</span> to the following property:
    </p>
    <p style="text-align: center; margin: 10px 0;">
        <span class="fill-wide">{{ $flProperty }}</span><br>
        <span style="font-size: 8pt; color: #444;">(description of property)</span>
    </p>
    <p>
        This waiver and release does not cover any retention or labor, services, or materials furnished
        after the date specified.
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
    Statutory safe-harbor form per Fla. Stat. &sect; 713.20(4) (substantial similarity). No notarization
    or witness is required. This waiver is unconditional on its face and does not release payment-bond
    rights (bond waivers use the separate forms in Fla. Stat. &sect; 713.235 and &sect; 255.05(2)).
    Form LW-FL-UP v{{ $waiver['form']['template_version'] }}.
</p>
