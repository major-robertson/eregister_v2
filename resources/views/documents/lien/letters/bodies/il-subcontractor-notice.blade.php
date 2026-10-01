{{--
    Illinois subcontractor's notice of claim, 770 ILCS 60/24(a), as amended by
    P.A. 103-827 (eff. 2025-01-01): "The form of such notice may be as
    follows". The form is verbatim from ilga.gov, verified 2026-09-30, with
    its blanks filled from the filing: the owner; the party that employed the
    claimant (below a subcontractor, also the original contractor that party
    works under, since the form's "his or her contract with you" is the
    original contractor's); what was furnished; the property (street address
    and county, legal description, PIN); and the amount due or to become due.
    A missing value prints as a ruled blank. The signer completes "Dated at
    .... this .... day of ....., ....."; the form's "(Signature)" is the
    shell's signature block. Never edit the form's words without re-checking
    the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $gc = $parties['gc'];
    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    // "(the name of contractor)": whoever employed the claimant; for a sub-subcontractor or a
    // supplier to a subcontractor, also the original contractor that party works under.
    $belowSubcontractor = $project['hired_by'] === 'subcontractor'
        || in_array($project['claimant_type'], ['sub_sub_contractor', 'supplier_to_subcontractor'], true);
    $original = $belowSubcontractor && ($gc['display_name'] ?? null) !== null
        && ! $same($gc, $hiring) && ! $same($gc, $claimant) && ! $same($gc, $owner) ? $gc : null;
    $employer = $blank($hiring['display_name'] ?? null, 'fill fill-wide')
        .($original !== null ? ', a subcontractor of '.e($original['display_name']).',' : '');

    // "(here give substantial description of the property)".
    $address = $project['address']['single_line'];
    $legal = $project['legal_description'] === null ? null : trim((string) preg_replace('/\s+/', ' ', $project['legal_description']));
    $property = implode('; ', array_filter([
        $address === null ? null : $address.($project['county'] ? ' ('.$project['county'].' County, '.$doc['form']['state_name'].')' : ''),
        $legal ? 'legal description: '.$legal : null,
        $project['apn'] ? $project['parcel_label'].' '.$project['apn'] : null,
    ]));
@endphp
<p>To {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}: You are hereby notified that I have been employed by {!! $employer !!} to furnish labor, services or materials for {!! $blank($filing['description_of_work'], 'fill fill-wide') !!} under his or her contract with you, on your property at {!! $blank($property === '' ? null : $property, 'fill fill-wide') !!} and that there was due to me, or is to become due (as the case may be) therefor, the sum of ${!! $blank($filing['amount'], 'fill fill-mid') !!}.</p>

<p>Dated at <span class="fill fill-mid">&nbsp;</span> this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, <span class="fill fill-short">&nbsp;</span>.</p>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
