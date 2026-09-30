{{--
    STATE OF / COUNTY OF caption with the page-1 index line beside it, so the
    parties, the amount and the parcel always sit on the first page (Oregon
    rejected a lien whose amount started on page 2; Berkeley County SC
    indexed the wrong party when the roles were not labelled). Pennsylvania
    files with a court docket caption instead (recording.caption 'docket').
--}}
@php
    $form = $doc['form'];
    $rec = $form['recording'];
    $stateName = strtoupper($form['state_name']);
    $county = $form['county_name'] ? strtoupper($form['county_name']) : null;
    $claimant = $doc['parties']['claimant'];
    $owner = $doc['parties']['owner'];
    $project = $doc['project'];
    $original = $doc['original_lien'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
@if (($rec['caption'] ?? 'state_county') === 'docket')
    <table class="caption docket">
        <tr>
            <td colspan="2" class="center"><strong>IN THE COURT OF COMMON PLEAS OF @if ($county){{ $county }}@else<span class="fill fill-mid">&nbsp;</span>@endif COUNTY, {{ $stateName }}</strong></td>
        </tr>
        <tr>
            <td style="width: 55%; border-right: 1px solid #000;">
                @if ($claimant && $claimant['display_name']){{ $claimant['display_name'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif,<br>
                Claimant<br><br>
                v.<br><br>
                @if ($owner && $owner['display_name']){{ $owner['display_name'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif,<br>
                Owner
            </td>
            <td style="width: 45%; padding-left: 12pt;">
                No. <span class="fill fill-short">&nbsp;</span> of 20<span class="fill fill-short">&nbsp;</span><br>
                {{ $form['title'] }}<br>
                Property subject to lien: @if ($project['address']['single_line']){{ $project['address']['single_line'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif<br>
                {{ $project['parcel_label'] }}: @if ($project['apn']){{ $project['apn'] }}@else<span class="fill">&nbsp;</span>@endif
            </td>
        </tr>
    </table>
@else
    <table class="caption">
        <tr>
            <td style="width: 40%;" class="venue">
                STATE OF {{ $stateName }}<br>
                COUNTY OF @if ($county){{ $county }}@else<span class="fill fill-mid">&nbsp;</span>@endif
            </td>
            <td style="width: 60%;">
                @if ($rec['index_line'] ?? true)
                    <table class="index">
                        <tr>
                            <td class="k">Claimant / Lienor</td>
                            <td>@if ($claimant && $claimant['display_name']){{ $claimant['display_name'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif</td>
                        </tr>
                        <tr>
                            <td class="k">Owner</td>
                            <td>@if ($owner && $owner['display_name']){{ $owner['display_name'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif</td>
                        </tr>
                        @if ($form['kind'] === 'lien_release')
                            <tr>
                                <td class="k">Releases</td>
                                <td>{{ $original['title'] }} recorded {!! $blank($original['recorded_at'], 'fill fill-mid') !!}{{ $original['recording_reference'] ? ' as '.$original['recording_reference'] : '' }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="k">Amount claimed</td>
                                <td>$@if ($doc['filing']['amount']){{ $doc['filing']['amount'] }}@else<span class="fill">&nbsp;</span>@endif</td>
                            </tr>
                        @endif
                        <tr>
                            <td class="k">{{ $project['parcel_label'] }}</td>
                            <td>@if ($project['apn']){{ $project['apn'] }}@else<span class="fill">&nbsp;</span>@endif</td>
                        </tr>
                    </table>
                @endif
            </td>
        </tr>
    </table>
@endif
