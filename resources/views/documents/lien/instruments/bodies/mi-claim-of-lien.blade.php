{{--
    Michigan Claim of Lien, the form of MCL 570.1111(2) ("A claim of lien
    shall be in substantially the following form"), verified against
    legislature.mi.gov (Michigan Compiled Laws complete through PA 103 of
    2026) on 2026-09-30. The form's words are verbatim, including the
    statute's "and therefor claims", with the filing's data in its blanks:
    the first and last furnishing dates, the claimant's name and address, the
    property (the form asks for the legal description from the notice of
    commencement), the owner, and the contract amount including extras (the
    contract plus its change orders), the payments received and the lien
    amount. The contractor, subcontractor or supplier block prints; the
    laborer block (hourly rate and wages owed) does not, because the app has
    no laborer claimant type. The form's "(address of party signing claim of
    lien)" closes the body with the claimant's mailing address (the signer
    is the claimant's own officer). The signature and the jurat come from
    the shared execution block (execution.statement false: the form has no
    sworn paragraph of its own), and the form's "Prepared by:" is the
    shell's preparer block. Never edit the wording without re-checking the
    statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $clauses = $doc['form']['clauses'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $amounts = $project['amounts'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    // "on the ... day of ..., 19 ..." with the furnishing dates: "the 30th day of June, 2026".
    $date = fn (?string $value) => $value === null ? null : \Illuminate\Support\Carbon::parse($value);
    $first = $date($project['dates']['first_furnish']);
    $last = $date($project['dates']['last_furnish']);

    // "The lien claimant's contract amount, including extras": the contract plus its change orders.
    $contractCents = $amounts['contract']['cents'] === null
        ? null
        : $amounts['contract']['cents'] + ($amounts['change_orders']['cents'] ?? 0);
    $contract = $contractCents === null ? null : number_format($contractCents / 100, 2);
@endphp
<p>
    Notice is hereby given that on the {!! $blank($first?->format('jS'), 'fill fill-short') !!} day of {!! $blank($first?->format('F'), 'fill fill-mid') !!}, {!! $blank($first?->format('Y'), 'fill fill-short') !!},
    {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}, {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!},
    first provided labor or material for an improvement to:
</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

<p>the owner of which property is {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}.</p>

<p>The last day of providing the labor or material was the {!! $blank($last?->format('jS'), 'fill fill-short') !!} day of {!! $blank($last?->format('F'), 'fill fill-mid') !!}, {!! $blank($last?->format('Y'), 'fill fill-short') !!}.</p>

<div class="keep" style="margin-top: 10pt;">
    <p><strong>TO BE COMPLETED BY A LIEN CLAIMANT WHO IS A CONTRACTOR, SUBCONTRACTOR, OR SUPPLIER:</strong></p>
    <p>The lien claimant's contract amount, including extras, is ${!! $blank($contract, 'fill fill-mid') !!}. The lien claimant has received payment thereon in the total amount of ${!! $blank($amounts['payments']['formatted'], 'fill fill-mid') !!}, and therefor claims a construction lien upon the above-described real property in the amount of ${!! $blank($filing['amount'], 'fill fill-mid') !!}.</p>
</div>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

@foreach ((array) ($clauses['affirmations'] ?? []) as $affirmation)
    <p>{{ $affirmation }}</p>
@endforeach

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])

<p style="margin-top: 10pt;">Address of party signing claim of lien: {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}</p>
