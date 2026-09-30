{{--
    Florida Notice to Owner, Fla. Stat. § 713.06(2)(c): "The notice may be in
    substantially the following form and must include the information and
    the warning contained in the following form", verified against
    flsenate.gov on 2026-09-29. The WARNING prints above the title (the
    shell's notice box), the "Florida law prescribes…" sentence and the
    IMPORTANT INFORMATION / PROTECT YOURSELF paragraphs come from the state
    file's clauses, and the signature block follows. Never edit the wording
    without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $hiring = $doc['parties']['hiring'];
    $clauses = $doc['form']['clauses'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    $propertyParts = array_filter([
        $project['address']['single_line'],
        $project['legal_description'],
        $project['apn'] ? $project['parcel_label'].' '.$project['apn'] : null,
    ]);
    $property = $propertyParts === [] ? null : implode('; ', $propertyParts);
@endphp
<p>The undersigned hereby informs you that he or she has furnished or is furnishing services or materials as follows:</p>

<p class="indent">
    {!! $blank($filing['description_of_work'], 'fill fill-wide') !!} for the improvement of the real property identified as {!! $blank($property, 'fill fill-wide') !!} under an order given by {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}.
</p>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

@foreach ((array) ($clauses['before_signature'] ?? []) as $paragraph)
    @if (mb_strtoupper($paragraph) === $paragraph)
        <p class="center"><strong>{{ $paragraph }}</strong></p>
    @else
        <p>{{ $paragraph }}</p>
    @endif
@endforeach
