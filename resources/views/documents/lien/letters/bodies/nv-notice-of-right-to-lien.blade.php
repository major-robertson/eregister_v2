{{--
    Nevada Notice of Right to Lien, the form of NRS 108.245(1) ("deliver in
    person or by certified mail to the owner of the property a notice of
    right to lien in substantially the following form"), verbatim from
    leg.state.nv.us (NRS chapter 108, revised 2025), verified 2026-09-30.
    The shell's "To:" block is the form's "To: (Owner's name and address)"
    and also lists the prime contractor, who gets a copy for information
    only; the shell's signature block is the form's "(Claimant)". The notice
    need not be verified, sworn to or acknowledged (NRS 108.245(4)). Never
    edit the wording without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $hiring = $doc['parties']['hiring'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    // "(property description or street address)"
    $propertyParts = array_filter([
        $project['address']['single_line'],
        $project['legal_description'],
        $project['apn'] ? $project['parcel_label'].' '.$project['apn'] : null,
    ]);
    $property = $propertyParts === [] ? null : implode('; ', $propertyParts);
@endphp
<p>The undersigned notifies you that he or she has supplied materials or equipment or performed work or services as follows:</p>

<p class="indent">{!! $blank($filing['description_of_work'], 'fill fill-wide') !!}</p>

<p>for improvement of property identified as {!! $blank($property, 'fill fill-wide') !!} under contract with {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}. This is not a notice that the undersigned has not been or does not expect to be paid, but a notice required by law that the undersigned may, at a future date, record a notice of lien as provided by law against the property if the undersigned is not paid.</p>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
