{{--
    Generic release of lien body. Identifies the recorded lien by its
    recording date, reference (instrument or file number) and book/page from
    Document details, or from the project's recorded lien filing, then
    releases it and directs the recording office to cancel it.
--}}
@php
    $form = $doc['form'];
    $clauses = $form['clauses'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $original = $doc['original_lien'];
    $office = $form['recording']['filing_office']['label'] ?? 'recording office';
    $county = $original['county'] ?? $form['county_name'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<p>
    KNOW ALL PERSONS BY THESE PRESENTS that {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}
    ("Claimant"), whose address is {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!},
    is the claimant under that certain {{ $original['title'] }} recorded on {!! $blank($original['recorded_at'], 'fill fill-mid') !!}{{ $original['recording_reference'] ? ' as '.$original['recording_reference'] : '' }}
    @if ($original['book'] || $original['page'])
        in Book {!! $blank($original['book'], 'fill fill-short') !!}, Page {!! $blank($original['page'], 'fill fill-short') !!}
    @endif
    in the official records of {!! $blank($county, 'fill fill-mid') !!} County, {{ $form['state_name'] }},
    {{ $original['amount'] ? 'in the amount of $'.$original['amount'].',' : '' }}
    against the real property described below, owned by {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}:
</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

<p>
    Claimant acknowledges {{ $original['amount_received'] ? 'receipt of $'.$original['amount_received'].' in ' : '' }}full satisfaction of the claim secured by that lien, and hereby releases, discharges and cancels the lien and the {{ $original['title'] }} of record, and authorizes and directs the {{ $office }} to cancel it of record.
</p>

@foreach ((array) ($clauses['affirmations'] ?? []) as $affirmation)
    <p>{{ $affirmation }}</p>
@endforeach

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
