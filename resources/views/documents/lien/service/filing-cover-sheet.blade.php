<!DOCTYPE html>
{{--
    Mail-in filing cover sheet for offices that take instruments on paper
    (North Carolina clerks of superior court, New York county clerks):
    where it goes, who sent it, what is enclosed, and how to return the
    file-stamped copy. Printed only when the state or county file sets
    recording.cover_sheet.

    Vars: $doc (LienDocumentPayload)
--}}
@php
    $form = $doc['form'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $preparer = $doc['preparer'];
    $office = $form['recording']['filing_office'] ?? [];
    $claimant = $doc['parties']['claimant'];
    $owner = $doc['parties']['owner'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Filing Cover Sheet - {{ $form['title'] }}</title>
    <meta name="author" content="eRegister">
    <style>
        @page { margin: 1in; }
        @include('documents.lien._parts.base-styles')
        .fields td.k { width: 1.6in; }
        .checklist div { margin-bottom: 4pt; }
    </style>
</head>
<body>
    <p class="doc-title">FILING COVER SHEET</p>
    <p class="doc-statute">{{ $form['title'] }} for filing</p>

    <table class="fields">
        <tr>
            <td class="k">To</td>
            <td>
                <div class="block">
                    <div>{{ $office['label'] ?? 'Recording office' }}@if ($project['county']), {{ $project['county'] }} County, {{ $form['state_name'] }}@endif</div>
                    @forelse (($office['address_lines'] ?? []) as $line)
                        <div>{{ $line }}</div>
                    @empty
                        <div><span class="fill fill-wide">&nbsp;</span></div>
                    @endforelse
                </div>
            </td>
        </tr>
        <tr>
            <td class="k">From</td>
            <td>
                <div class="block">
                    <div>{{ $preparer['name'] }}@if ($preparer['attention']), Attn: {{ $preparer['attention'] }}@endif</div>
                    @foreach ($preparer['address_lines'] as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                    @if ($preparer['phone'])<div>{{ $preparer['phone'] }}</div>@endif
                    @if ($preparer['email'])<div>{{ $preparer['email'] }}</div>@endif
                </div>
            </td>
        </tr>
        <tr>
            <td class="k">Date</td>
            <td>{{ $doc['date'] }}</td>
        </tr>
        <tr>
            <td class="k">Instrument</td>
            <td>{{ $form['title'] }}@if (! empty($form['statute'])) ({{ $form['statute'] }})@endif</td>
        </tr>
        <tr>
            <td class="k">Claimant</td>
            <td>{!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}</td>
        </tr>
        <tr>
            <td class="k">Owner</td>
            <td>{!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}</td>
        </tr>
        <tr>
            <td class="k">Property</td>
            <td>
                {!! $blank($project['address']['single_line'], 'fill fill-wide') !!}
                @if ($project['apn'])<br>{{ $project['parcel_label'] }}: {{ $project['apn'] }}@endif
            </td>
        </tr>
        <tr>
            <td class="k">Reference</td>
            <td>eRegister filing {{ $filing['public_id'] }}</td>
        </tr>
    </table>

    <p><strong>Enclosed:</strong></p>
    <div class="checklist">
        <div><span class="box"></span> Original {{ $form['title'] }}, signed{{ ($form['execution']['notary'] ?? false) ? ' and notarized' : '' }}, <span class="fill fill-short">&nbsp;</span> pages</div>
        <div><span class="box"></span> Filing fee{{ ! empty($form['recording']['fee_note']) ? ' ('.$form['recording']['fee_note'].')' : '' }}: check or money order no. <span class="fill fill-mid">&nbsp;</span> for $<span class="fill fill-short">&nbsp;</span></div>
        <div><span class="box"></span> One copy to be file-stamped and returned</div>
        <div><span class="box"></span> Self-addressed, postage-paid return envelope</div>
        <div><span class="box"></span> Other: <span class="fill fill-wide">&nbsp;</span></div>
    </div>

    <p>Please file the enclosed {{ $form['title'] }} and return a file-stamped copy in the enclosed envelope. If anything prevents filing, please call or email the sender above before returning the document so it can be corrected without losing the filing date.</p>

    <p>Thank you.</p>

    <table class="sig-table">
        <tr>
            <td style="width: 58%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">{{ $preparer['name'] }}, by (signature and printed name)</div></td>
            <td style="width: 42%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Telephone</div></td>
        </tr>
    </table>
</body>
</html>
