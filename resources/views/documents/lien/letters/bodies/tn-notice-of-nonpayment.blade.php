{{--
    Tennessee Notice of Nonpayment, Tenn. Code Ann. § 66-11-145: a remote
    contractor's notice to the owner and the prime contractor, within 90 days
    of the last day of each month in which unpaid work or materials were
    furnished. The body is the § 66-11-145(d) form ("may be in substantially
    the following form") with its blanks filled, verbatim from the 2021
    Tennessee Code on law.justia.com and codes.findlaw.com (current as of
    2024-01-02); the section is unamended since 2007. Checked 2026-09-30.
    The form carries every item (a) requires: (1) the remote contractor's name
    and the address for communications, (2) a general description of what was
    provided, (3) the amount owed as of the date of the notice, (4) the last
    date of furnishing and (5) a description sufficient to identify the
    property, which prints in full (address, legal description, map and
    parcel) where the form says "located at". The form's "TO: [Owner] /
    [Contractor contracting w/ Owner]" block is the shell's addressee list
    (service.recipients owner and gc) and its "Lienor / Dated" lines are the
    shell's date and signature block. Never edit the wording without
    re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $claimant = $doc['parties']['claimant'];
    $work = $filing['description_of_work'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<p>
    Pursuant to Tennessee Code Annotated, § 66-11-145, notice is hereby given that {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!} has not been paid for certain labor, materials, services, equipment, or machinery it supplied in the {!! $blank($work, 'fill fill-wide') !!} of the {!! $blank($project['name'], 'fill fill-wide') !!}, located at:
</p>

<div class="indent keep">
    @include('documents.lien._parts.property')
</div>

{{-- The address for communications stays with the sentence that introduces it. --}}
<div class="keep">
    <p>
        The amount presently due and owing is ${!! $blank($filing['amount'], 'fill fill-mid') !!}. The last date labor, materials, services, equipment, or machinery were provided in connection with the improvements was {!! $blank($project['dates']['last_furnish']) !!}. You may send any communications regarding this matter to the following name and address:
    </p>

    <div class="indent">
        @include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])
    </div>
</div>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
