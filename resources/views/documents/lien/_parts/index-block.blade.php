{{--
    Missouri-style first-page index block (RSMo 59.310): title, date,
    grantor and grantee with mailing addresses, legal description, and the
    prior instrument for a release. recording.index_roles says which party
    is which; a release swaps them (the claimant grants the release).
--}}
@php
    $form = $doc['form'];
    $roles = $form['recording']['index_roles'] ?? ['grantor' => 'owner', 'grantee' => 'claimant'];

    if ($form['kind'] === 'lien_release') {
        $roles = ['grantor' => $roles['grantee'] ?? 'claimant', 'grantee' => $roles['grantor'] ?? 'owner'];
    }

    $grantor = $doc['parties'][$roles['grantor']] ?? null;
    $grantee = $doc['parties'][$roles['grantee']] ?? null;
    $original = $doc['original_lien'];
@endphp
<table class="index-block">
    <tr><td class="k">Title of document</td><td>{{ $form['title'] }}</td></tr>
    <tr><td class="k">Date of document</td><td>{{ $doc['date'] }}</td></tr>
    <tr>
        <td class="k">Grantor (mailing address)</td>
        <td>
            @if ($grantor && $grantor['display_name']){{ $grantor['display_name'] }}@if ($grantor['address_line']), {{ $grantor['address_line'] }}@endif
            @else<span class="fill fill-wide">&nbsp;</span>@endif
        </td>
    </tr>
    <tr>
        <td class="k">Grantee (mailing address)</td>
        <td>
            @if ($grantee && $grantee['display_name']){{ $grantee['display_name'] }}@if ($grantee['address_line']), {{ $grantee['address_line'] }}@endif
            @else<span class="fill fill-wide">&nbsp;</span>@endif
        </td>
    </tr>
    <tr>
        <td class="k">Legal description</td>
        <td>@if ($doc['project']['legal_description']){!! nl2br(e($doc['project']['legal_description'])) !!}@else<span class="fill fill-wide">&nbsp;</span>@endif</td>
    </tr>
    @if ($form['kind'] === 'lien_release' && ($original['recording_reference'] || $original['recorded_at']))
        <tr>
            <td class="k">Reference</td>
            <td>{{ $original['title'] }}@if ($original['recorded_at']) recorded {{ $original['recorded_at'] }}@endif @if ($original['recording_reference'])as {{ $original['recording_reference'] }}@endif</td>
        </tr>
    @endif
</table>
