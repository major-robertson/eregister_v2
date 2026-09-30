{{--
    The property: street address with the county named (Midland County TX
    wanted the county on the face and it was added by overlay after
    notarization), block and lot where the state indexes by them, the legal
    description, and the parcel number under the county's own label.
--}}
@php
    $project = $doc['project'];
    $sections = $doc['form']['sections'];
    $details = $doc['details'];
@endphp
<div class="block">
    <div>
        @if ($project['address']['single_line']){{ $project['address']['single_line'] }}@else<span class="fill fill-wide">&nbsp;</span>@endif
        @if ($project['county'])({{ $project['county'] }} County, {{ $doc['form']['state_name'] }})@endif
    </div>
    @if (! empty($sections['block_lot']))
        <div>
            Block: @if ($details['block']){{ $details['block'] }}@else<span class="fill fill-short">&nbsp;</span>@endif
            &nbsp; Lot: @if ($details['lot']){{ $details['lot'] }}@else<span class="fill fill-short">&nbsp;</span>@endif
        </div>
    @endif
    <div>
        <span class="lbl">Legal description:</span>
        @if ($project['legal_description']){!! nl2br(e($project['legal_description'])) !!}@else<span class="fill fill-wide">&nbsp;</span>@endif
    </div>
    <div>
        <span class="lbl">{{ $project['parcel_label'] }}:</span>
        @if ($project['apn']){{ $project['apn'] }}@else<span class="fill">&nbsp;</span>@endif
    </div>
</div>
