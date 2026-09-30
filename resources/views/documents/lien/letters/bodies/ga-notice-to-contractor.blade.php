{{--
    Georgia Notice to Contractor, O.C.G.A. § 44-14-361.5(c): sent by a
    claimant without privity of contract with the contractor, to the owner
    and the contractor, within 30 days after first furnishing, when a Notice
    of Commencement was filed. The statute lists the contents (claimant's
    name, address and telephone; the person at whose instance the claimant
    furnished; the project's name and location as in the Notice of
    Commencement; a description of what was furnished and the contract price
    or anticipated value, or the amount claimed due) without prescribing a
    form. The Georgia code is not on a site that allows automated fetches;
    this list follows § 44-14-361.5(c) as reproduced by the Georgia Superior
    Court Clerks' Cooperative Authority and must be re-checked against the
    official code before the first Georgia notice goes out.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
@endphp
<p>To the owner and the contractor named above: pursuant to O.C.G.A. § 44-14-361.5, the undersigned gives notice that it is furnishing labor, services, materials, machinery or equipment for the improvement of the property described below, as follows:</p>

<table class="fields">
    <tr>
        <td class="k">Name, address and telephone number of the person furnishing labor, services or materials</td>
        <td>@include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])</td>
    </tr>
    <tr>
        <td class="k">Name and address of each person at whose instance the labor, services or materials are furnished</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['hiring']])</td>
    </tr>
    <tr>
        <td class="k">Name and location of the project (as set forth in the Notice of Commencement)</td>
        <td>
            @if ($project['name'])<div>{{ $project['name'] }}</div>@endif
            @include('documents.lien._parts.property')
        </td>
    </tr>
    <tr>
        <td class="k">Description of the labor, services, materials, machinery or equipment furnished or to be furnished</td>
        <td>@include('documents.lien._parts.work')</td>
    </tr>
    @if ($project['dates']['first_furnish'])
        <tr>
            <td class="k">First furnished</td>
            <td>{{ $project['dates']['first_furnish'] }}</td>
        </tr>
    @endif
    <tr>
        <td class="k">Contract price or anticipated value of the labor, services or materials to be furnished, or the amount claimed to be due</td>
        <td>$@if ($filing['estimate']){{ $filing['estimate'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
