{{--
    California Preliminary Notice, Cal. Civ. Code §§ 8102 and 8202: the
    owner, direct contractor and construction lender, a description of the
    site, the person giving notice and its relationship to the parties, a
    general description of the work, the person to or for whom it is
    provided, and an estimate of the total price; the boxed NOTICE TO
    PROPERTY OWNER of § 8202(a)(3) prints above the title (the shell's
    notice box). Contents verified against leginfo.legislature.ca.gov on
    2026-09-29.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $hiring = $parties['hiring'];
@endphp
<p><strong>THIS IS NOT A LIEN.</strong> This is a preliminary notice under Civil Code sections 8200 through 8216, given to preserve the right of the person giving it to record a claim of mechanics lien or give a stop payment notice. It is not a reflection on the integrity of any contractor or subcontractor.</p>

<table class="fields">
    <tr>
        <td class="k">Owner or reputed owner</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['owner']])</td>
    </tr>
    <tr>
        <td class="k">Direct contractor</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['gc'] ?? ($project['in_privity'] ? $claimant : null)])</td>
    </tr>
    <tr>
        <td class="k">Construction lender, if any</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['lender']])</td>
    </tr>
    <tr>
        <td class="k">Description of the site</td>
        <td>@include('documents.lien._parts.property')</td>
    </tr>
    <tr>
        <td class="k">Person giving this notice (claimant)</td>
        <td>
            @include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])
            @if ($project['claimant_type_phrase'])
                <div>Relationship to the parties: {{ $project['claimant_type_phrase'] }}</div>
            @endif
        </td>
    </tr>
    <tr>
        <td class="k">General description of the work provided or to be provided</td>
        <td>@include('documents.lien._parts.work')</td>
    </tr>
    <tr>
        <td class="k">Person to or for whom the work is provided</td>
        <td>@include('documents.lien._parts.party', ['party' => $hiring])</td>
    </tr>
    @if ($project['dates']['first_furnish'])
        <tr>
            <td class="k">First furnished</td>
            <td>{{ $project['dates']['first_furnish'] }}</td>
        </tr>
    @endif
    <tr>
        <td class="k">Estimate of the total price of the work provided and to be provided</td>
        <td>$@if ($filing['estimate']){{ $filing['estimate'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
