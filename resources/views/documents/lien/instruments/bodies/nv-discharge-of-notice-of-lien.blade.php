{{--
    Nevada Discharge or Release of Notice of Lien, the form of NRS
    108.2437(1) ("the lien claimant shall cause to be recorded a discharge or
    release of the notice of lien in substantially the following form"),
    verified against leg.state.nv.us (NRS chapter 108, revised 2025) on
    2026-09-30. Verbatim: "NOTICE IS HEREBY GIVEN THAT:", the recital and the
    "NOW, THEREFORE" release. The claimant records it no later than 10 days
    after the lien is fully satisfied or discharged, or owes the owner actual
    damages or $100, whichever is greater, plus attorney's fees and costs
    (NRS 108.2437(2)).

    The form's "Assessor's Parcel Numbers" line prints above the title
    (clauses.notice_box). Its "(Signature of Lien Claimant)" is the shell's
    signature block, acknowledged before a notary because NRS 108.2433(2)
    wants an acknowledged discharge for a notice of lien recorded by a
    photographic process. The recording date, book and document number come
    from Document details (original lien) or the project's recorded notice
    of lien; "in Book" drops out when only a document number is on file,
    since most Nevada recorders number documents without a book. Never edit
    the wording without re-checking the statute.
--}}
@php
    $form = $doc['form'];
    $project = $doc['project'];
    $owner = $doc['parties']['owner'];
    $original = $doc['original_lien'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    $recorded = $original['recorded_at'] ? \Illuminate\Support\Carbon::parse($original['recorded_at']) : null;
    $recordedCounty = $original['county'] ?? $form['county_name'];
    $propertyCounty = $project['county'] ?? $original['county'];
    $showBook = $original['book'] !== null || $original['recording_reference'] === null;
@endphp
<p>NOTICE IS HEREBY GIVEN THAT:</p>

<p>
    The undersigned did, on the {!! $blank($recorded?->format('jS'), 'fill fill-short') !!} day of the month of {!! $blank($recorded?->format('F'), 'fill fill-mid') !!} of the year {!! $blank($recorded?->format('Y'), 'fill fill-short') !!}, record
    @if ($showBook)
        in Book {!! $blank($original['book'], 'fill fill-short') !!},
    @endif
    as Document No. {!! $blank($original['recording_reference'], 'fill fill-mid') !!}, in the office of the county recorder of {!! $blank($recordedCounty, 'fill fill-mid') !!} County, Nevada, its Notice of Lien, or has otherwise given notice of his or her intention to hold a lien upon the following described property or improvements, owned or purportedly owned by {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}, located in the County of {!! $blank($propertyCounty, 'fill fill-mid') !!}, State of Nevada, to wit:
</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<p>NOW, THEREFORE, for valuable consideration the undersigned does release, satisfy and discharge this notice of lien on the property or improvements described above by reason of this Notice of Lien.</p>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
