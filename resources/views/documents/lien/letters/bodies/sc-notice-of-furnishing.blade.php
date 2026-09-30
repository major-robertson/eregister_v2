{{--
    South Carolina Notice of Furnishing Labor or Materials. Two sections ask
    for it and neither prescribes wording, so the text is house wording:
      S.C. Code Ann. § 29-5-40: a claimant employed by someone other than the
        owner notifies the owner in writing "of the furnishing of such labor
        or material and the amount or value thereof".
      § 29-5-20(B): a sub-subcontractor or supplier sends the contractor
        "notice of furnishing labor or materials by certified or registered
        mail", which "shall include" (1) the claimant's name, (2) the person
        it contracted with or was employed by, (3) the labor, services or
        materials and their contract price or value, (4) the project, (5) the
        first and last furnishing dates, actual or scheduled, and (6) the
        amount claimed to be due, if any.
    The rows follow (1) to (6) in order; the price or value row also answers
    § 29-5-40. Specially fabricated materials that § 29-5-20(B)(3) wants
    stated separately go in the description of work (see the state file's
    notes). Verified against scstatehouse.gov on 2026-09-30.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $dates = $project['dates'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<p>The claimant named below gives notice that it has furnished or will furnish labor, services or materials for the improvement of the real estate described below, in the amount or value stated below.</p>

<table class="fields">
    <tr>
        <td class="k">Claimant (person giving notice and claiming payment)</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['claimant'], 'contact' => true])</td>
    </tr>
    <tr>
        <td class="k">Person with whom the claimant contracted or by whom it was employed</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['hiring']])</td>
    </tr>
    <tr>
        <td class="k">Labor, services or materials furnished</td>
        <td>@include('documents.lien._parts.work')</td>
    </tr>
    <tr>
        <td class="k">Contract price or value of the labor, services or materials</td>
        <td>${!! $blank($filing['estimate'], 'fill fill-mid') !!}</td>
    </tr>
    <tr>
        <td class="k">Project where the labor, services or materials are used</td>
        <td>
            @if ($project['name'])<div>{{ $project['name'] }}</div>@endif
            @include('documents.lien._parts.property')
        </td>
    </tr>
    <tr>
        <td class="k">First furnished (or scheduled to be furnished)</td>
        <td>{!! $blank($dates['first_furnish']) !!}</td>
    </tr>
    <tr>
        <td class="k">Last furnished (or scheduled to be furnished)</td>
        <td>{!! $blank($dates['last_furnish']) !!}</td>
    </tr>
    <tr>
        <td class="k">Amount claimed to be due, if any</td>
        <td>${!! $blank($filing['amount'], 'fill fill-mid') !!}</td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<p>This notice is given under S.C. Code Ann. §§ 29-5-20(B) and 29-5-40. It is not a lien. If the claimant is not paid in full, it may claim a lien against the property to the extent the law allows. Please direct any question about this notice to the claimant at the address above.</p>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
