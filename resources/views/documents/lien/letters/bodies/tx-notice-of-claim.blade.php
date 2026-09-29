{{--
    Texas Notice of Claim for Unpaid Labor or Materials, Tex. Prop. Code
    § 53.056(a-2): "The notice must be in substantially the following form",
    verified against texas.public.law on 2026-09-29. The form's fields in the
    statutory order; the claimant's contact person and address close it
    (the shell's signature block carries the printed name). Never edit the
    labels without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $hiring = $parties['hiring'];
    $gc = $parties['gc'];
    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    $projectLine = implode(' / ', array_filter([$project['name'], $project['address']['single_line']]));
@endphp
<table class="fields">
    <tr>
        <td class="k">Date:</td>
        <td>{{ $doc['date'] }}</td>
    </tr>
    <tr>
        <td class="k">Project description and/or address:</td>
        <td>{!! $blank($projectLine ?: null, 'fill fill-wide') !!}</td>
    </tr>
    <tr>
        <td class="k">Claimant's name:</td>
        <td>{!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
    <tr>
        <td class="k">Type of labor or materials provided:</td>
        <td>@include('documents.lien._parts.work')</td>
    </tr>
    @if (! empty($doc['form']['sections']['months_of_work']))
        <tr>
            <td class="k">Month(s) in which the labor or materials were provided:</td>
            <td>{!! $blank($doc['details']['months_of_work'], 'fill fill-wide') !!}</td>
        </tr>
    @endif
    <tr>
        <td class="k">Original contractor's name:</td>
        <td>{!! $blank($gc['display_name'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
    <tr>
        <td class="k">Party with whom claimant contracted if different from original contractor:</td>
        <td>
            @if ($hiring !== null && $gc !== null && $same($hiring, $gc))
                Same as the original contractor
            @else
                {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}
            @endif
        </td>
    </tr>
    <tr>
        <td class="k">Claim amount:</td>
        <td>${!! $blank($filing['amount'], 'fill fill-mid') !!}</td>
    </tr>
    <tr>
        <td class="k">Claimant's contact person:</td>
        <td>{!! $blank($doc['signer']['name'] ?? $claimant['name'] ?? null, 'fill fill-wide') !!}@if ($claimant['phone'] ?? null), {{ $claimant['phone'] }}@endif</td>
    </tr>
    <tr>
        <td class="k">Claimant's address:</td>
        <td>{!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
