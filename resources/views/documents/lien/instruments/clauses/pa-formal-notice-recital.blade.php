{{--
    The two 49 P.S. § 1503 statements the generic claim body does not make in
    the act's own terms. (1) Whether the claimant files as a contractor (in
    contract with the owner, § 1201(4)) or as a subcontractor (§ 1201(5)); the
    body's "as a supplier of materials to the direct contractor" is a
    subcontractor in Pennsylvania. (4) For a subcontractor, the date the formal
    notice of intention to file was given to the owner, at least 30 days before
    filing (§ 1501(b.1)); the person it contracted with is the body's item for
    the hiring party. House wording. The notice date and method come from
    Document details only, never the project's preliminary notice date:
    Pennsylvania has had no preliminary notice since 2006. An unknown tier
    prints both choices with boxes and no recital; fix the tier and regenerate.
--}}
@php
    $inPrivity = $doc['project']['in_privity'];
    $details = $doc['details'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
@if ($inPrivity === true)
    <p>Claimant files this claim as a contractor (49 P.S. § 1503(1)).</p>
@elseif ($inPrivity === false)
    <p>Claimant files this claim as a subcontractor (49 P.S. § 1503(1)).</p>
    <p>Formal notice of Claimant's intention to file this claim was served on the owner on {!! $blank($details['notice_served_at'], 'fill fill-mid') !!} by {!! $blank($details['notice_served_method_label']) !!}, at least 30 days before this claim was filed (49 P.S. § 1501(b.1)).</p>
@else
    <p>Claimant files this claim as a <span class="box"></span> contractor <span class="box"></span> subcontractor (49 P.S. § 1503(1)).</p>
@endif
