{{--
    Georgia Claim of Lien, O.C.G.A. § 44-14-361.1(a)(2): "The claim shall be
    in substance as follows: 'A. B., a mechanic, contractor, subcontractor,
    materialman, … or other person (as the case may be) claims a lien in the
    amount of (specify the amount claimed) on the house, factory, mill,
    machinery, or railroad (as the case may be) and the premises or real
    estate on which it is erected or built, of C. D. (describing the houses,
    premises, real estate, or railroad), for satisfaction of a claim which
    became due on (specify the date the claim was due, which is the same as
    the last date the labor, services, or materials were supplied to the
    premises) for building, repairing, improving, or furnishing material (or
    whatever the claim may be).'" The § 44-14-367 expiration statement (12
    point bold, printed by the shell) and the notice of the owner's right to
    contest (state file clause) are required on the face; their absence
    invalidates the lien. Verified against the 2025 Code of Georgia on
    law.justia.com on 2026-09-29. Never edit the wording without re-checking
    the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $clauses = $doc['form']['clauses'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    // "a mechanic, contractor, subcontractor, materialman, … or other person (as the case may be)"
    $capacity = match ($project['claimant_type']) {
        'gc' => 'a contractor',
        'subcontractor', 'sub_sub_contractor' => 'a subcontractor',
        'supplier_to_owner', 'supplier_to_contractor', 'supplier_to_subcontractor' => 'a materialman',
        default => 'a person entitled to a lien under O.C.G.A. § 44-14-361',
    };
@endphp
<p>
    {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}, {{ $capacity }}, claims a lien in the amount of ${!! $blank($filing['amount'], 'fill fill-mid') !!} on the building, structure and improvements and the premises or real estate on which they are erected or built, of {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}, described as follows:
</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

<p>
    for satisfaction of a claim which became due on {!! $blank($project['dates']['last_furnish']) !!} (the last date the labor, services or materials were supplied to the premises) for {!! $blank($filing['description_of_work'], 'fill fill-wide') !!}.
</p>

@if ($project['in_privity'] === false && $hiring !== null)
    <p>The labor, services or materials were furnished at the instance of {{ $hiring['display_name'] }}@if ($hiring['address_line']), {{ $hiring['address_line'] }}@endif.</p>
@endif

@include('documents.lien._parts.amounts')
@include('documents.lien._parts.clauses', ['only' => 'after_property'])

@foreach ((array) ($clauses['affirmations'] ?? []) as $affirmation)
    <p>{{ $affirmation }}</p>
@endforeach

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
