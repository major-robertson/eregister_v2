{{--
    N.Y. Lien Law § 9(7): the notice of lien shall state "whether the property
    subject to the lien is real property improved or to be improved with a
    single family dwelling or not" (verified against nysenate.gov on
    2026-09-30). The answer also sets the § 10 filing window (four months, not
    eight) and whether § 17 allows an extension without a court order. A
    project classed as commercial is not a dwelling, so "is not" prints for
    it; otherwise staff mark one box before the notice is signed. Prints
    under the generic body's property item (clauses.after_property).
--}}
@php
    $commercial = ($doc['project']['property_class'] ?? null) === 'commercial';
@endphp
@if ($commercial)
    <p style="margin: 0 0 7pt 26pt;">The property subject to the lien is not real property improved or to be improved with a single family dwelling.</p>
@else
    <p style="margin: 0 0 7pt 26pt;">The property subject to the lien <span class="box"></span> is <span class="box"></span> is not real property improved or to be improved with a single family dwelling.</p>
@endif
