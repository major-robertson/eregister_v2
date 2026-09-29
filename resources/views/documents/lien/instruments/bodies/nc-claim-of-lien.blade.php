{{--
    North Carolina Claim of Lien on Real Property, the statutory form of
    N.C. Gen. Stat. § 44A-12(c) ("must be filed using a form substantially as
    follows"), items (1) to (6) verbatim, verified against ncleg.gov on
    2026-09-29. The G.S. 44A-11 certification comes from the state file's
    affirmations; the "Filed this ___ day of ___ / Clerk of Superior Court"
    lines follow the execution block. Never edit the wording without
    re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $clauses = $doc['form']['clauses'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $gc = $parties['gc'];
    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    $subrogationContractor = $project['in_privity'] === false && $gc !== null && ! $same($gc, $claimant) ? $gc : null;
@endphp
<table class="item">
    <tr>
        <td class="n">(1)</td>
        <td>
            Name and address of the person claiming the claim of lien on real property:
            @include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(2)</td>
        <td>
            Name and address of the record owner of the real property claimed to be subject to the claim of lien on real property at the time the claim of lien on real property is filed and, if the claim of lien on real property is being asserted pursuant to G.S. 44A-23, the name of the contractor through which subrogation is being asserted:
            @include('documents.lien._parts.party', ['party' => $owner])
            @if ($subrogationContractor)
                <div style="margin-top: 4pt;">Contractor through which subrogation is asserted: {{ $subrogationContractor['display_name'] }}</div>
            @endif
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(3)</td>
        <td>
            Description of the real property upon which the claim of lien on real property is claimed: (Street address, tax lot and block number, reference to recorded instrument, or any other description of real property is sufficient, whether or not it is specific, if it reasonably identifies what is described.)
            @include('documents.lien._parts.property')
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(4)</td>
        <td>
            Name and address of the person with whom the claimant contracted for the furnishing of labor or materials:
            @if ($same($hiring, $owner))
                <div>The record owner named in (2) above.</div>
            @else
                @include('documents.lien._parts.party', ['party' => $hiring])
            @endif
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(5)</td>
        <td>
            Date upon which labor or materials were first furnished upon said property by the claimant:
            @if ($project['dates']['first_furnish']){{ $project['dates']['first_furnish'] }}@else<span class="fill">&nbsp;</span>@endif
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(5a)</td>
        <td>
            Date upon which labor or materials were last furnished upon said property by the claimant:
            @if ($project['dates']['last_furnish']){{ $project['dates']['last_furnish'] }}@else<span class="fill">&nbsp;</span>@endif
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(6)</td>
        <td>
            General description of the labor performed or materials furnished and the amount claimed therefor:
            @include('documents.lien._parts.work')
            <div>Amount claimed: $@if ($filing['amount']){{ $filing['amount'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</div>
            @include('documents.lien._parts.amounts')
        </td>
    </tr>
</table>

@foreach ((array) ($clauses['affirmations'] ?? []) as $affirmation)
    <p>{{ $affirmation }}</p>
@endforeach

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
