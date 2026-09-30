{{--
    Alabama Verified Statement of Lien, the form of Ala. Code § 35-11-213
    ("Said verified statement may be in the following form, which shall be
    deemed sufficient"), verified against the official Code of Alabama on
    alison.legislature.state.al.us on 2026-09-30. The shell's STATE OF /
    COUNTY OF caption is the form's "State of Alabama, County of ___" venue.
    The form's sentences are verbatim, with the filing's data in the blanks:
    the claimant, the affiant (the signer), the county, the property (street
    address, legal description, parcel number), the indebtedness in figures
    and words with interest from the last furnishing date, the work, and the
    owner. The claimant signs in the shell's execution block (the form's
    "___, Claimant."; execution.statement and execution.notary are false),
    and the form's affidavit and jurat follow it
    (instruments/clauses/al-affidavit). Never edit the wording without
    re-checking the statute.
--}}
@php
    $form = $doc['form'];
    $clauses = $form['clauses'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $signer = $doc['signer'];
    $claimant = $doc['parties']['claimant'];
    $owner = $doc['parties']['owner'];
    $claimantName = $claimant['display_name'] ?? null;
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    // "verified by the oath of ___": the signer, with the office held when an officer swears for the claimant.
    $affiant = $signer['name'];
    if ($affiant !== null && $signer['title'] !== null && ($claimant['company'] ?? null) !== null && $affiant !== $claimantName) {
        $affiant .= ', its '.$signer['title'];
    }

    // "with interest, from to wit ___ day of ___, 19__": the last furnishing date ("the 10th day of July, 2026").
    $last = $project['dates']['last_furnish'] === null ? null : \Illuminate\Support\Carbon::parse($project['dates']['last_furnish']);

    // The indebtedness in figures, then in words (sections.amount_in_words).
    $words = ! empty($form['sections']['amount_in_words']) && $filing['amount_words'] ? ' ('.$filing['amount_words'].')' : '';

    // "for ___": the work on one line; the sentence supplies the closing period.
    $work = $filing['description_of_work'] === null ? null : rtrim((string) preg_replace('/\s+/', ' ', $filing['description_of_work']), ' .');
@endphp
<p>{!! $blank($claimantName, 'fill fill-wide') !!} files this statement in writing, verified by the oath of {!! $blank($affiant, 'fill fill-wide') !!}, who has personal knowledge of the facts herein set forth:</p>

<p>That said {!! $blank($claimantName, 'fill fill-wide') !!} claims a lien upon the following property, situated in {!! $blank($project['county'], 'fill fill-mid') !!} county, Alabama, to wit:</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

<p>This lien is claimed, separately and severally, as to both the buildings and improvements thereon, and the said land.</p>

<p>That said lien is claimed to secure an indebtedness of ${!! $blank($filing['amount'], 'fill fill-mid') !!}{{ $words }} with interest, from to wit the {!! $blank($last?->format('jS'), 'fill fill-short') !!} day of {!! $blank($last?->format('F'), 'fill fill-mid') !!}, {!! $blank($last?->format('Y'), 'fill fill-short') !!}, for {!! $blank($work, 'fill fill-wide') !!}.</p>

<p>The name of the owner or proprietor of the said property is {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}.</p>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

@foreach ((array) ($clauses['affirmations'] ?? []) as $affirmation)
    <p>{{ $affirmation }}</p>
@endforeach

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
