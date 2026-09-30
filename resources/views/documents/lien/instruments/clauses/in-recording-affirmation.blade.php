{{--
    Indiana recording statements, IC 36-2-11-15. The recorder may receive an
    instrument that creates, encumbers or disposes of a lien on property only
    if the name of the person that prepared it is printed at its conclusion
    and every Social Security number is redacted. Both forms are verbatim from
    the 2026 Indiana Code (iga.in.gov), verified 2026-09-30: the (d)
    affirmation, which goes at the conclusion of the instrument immediately
    before or after the preparer statement, then the (c) statement "This
    instrument was prepared by (name).". The name is the staff member in
    config('lien.documents.preparer.attention'), a ruled blank until that is
    set; the preparer statement adds the preparer's company (eRegister).
    Never edit the wording without re-checking the statute.
--}}
@php
    $preparer = $doc['preparer'];
    $blank = fn (?string $value, string $class = 'fill fill-mid') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    $individual = $blank($preparer['attention'] ?? null);
    $company = $preparer['name'] ?? null;
@endphp
<div class="keep" style="margin-top: 10pt;">
    {{-- One inline box, so a line break never separates "(" from the name. --}}
    <p>I affirm, under the penalties for perjury, that I have taken reasonable care to redact each Social Security number in this document, unless required by law <span style="display: inline-block;">({!! $individual !!}).</span></p>
    <p>This instrument was prepared by {!! $individual !!}{{ $company ? ', '.$company : '' }}.</p>
</div>
