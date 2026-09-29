{{--
    Florida Claim of Lien, the single-paragraph statutory form of Fla. Stat.
    § 713.08(3) ("sufficient if it is in substantially the following form"),
    verified against flsenate.gov on 2026-09-29. The WARNING prints above the
    title (the shell's notice box) and the § 117.05(13)(a) jurat follows the
    signature (execution.notary_variant 'fl'). The privity sentence prints
    only when the claimant did not contract with the owner (§ 713.08(1)(h));
    the contractor-copy sentence only for a sub-subcontractor or a
    materialman to a subcontractor. Never edit the wording without
    re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $details = $doc['details'];
    $signer = $doc['signer'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $claimantName = $claimant['display_name'] ?? null;
    $amounts = $project['amounts'];

    $totalCents = $amounts['contract']['cents'] === null
        ? $filing['amount_cents']
        : $amounts['contract']['cents'] + ($amounts['change_orders']['cents'] ?? 0);
    $total = $totalCents === null ? null : number_format($totalCents / 100, 2);
    $totalWords = $totalCents === null ? null : \App\Domains\Lien\Documents\MoneyWords::dollars($totalCents);

    $noticeDate = $details['notice_served_at'] ?? $project['dates']['prelim_sent'];
    $noticeMethod = $details['notice_served_method_label'];
    $notInPrivity = $project['in_privity'] !== true;
    $servesContractor = in_array($project['claimant_type'], ['sub_sub_contractor', 'supplier_to_subcontractor'], true);

    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    $wide = fn (?string $value) => $blank($value, 'fill fill-wide');

    // "(if the lien is claimed by one not in privity with the owner)" and "(if required)" sentences.
    $privitySentence = $notInPrivity
        ? '; and that the lienor served her or his notice to owner on '.$blank($noticeDate).', by '.$blank($noticeMethod)
        : '';
    $contractorSentence = $servesContractor
        ? '; and that the lienor served copies of the notice on the contractor on '.$blank(null).', by '.$blank(null).' and on the subcontractor, '.$wide(null).', on '.$blank(null).', by '.$blank(null)
        : '';
@endphp
<p>
    Before me, the undersigned notary public, personally appeared {!! $wide($signer['name']) !!}, who was duly sworn and says that he or she is
    @if ($signer['title'])
        the {{ $signer['title'] }} and agent of the lienor herein, {!! $wide($claimantName) !!},
    @else
        the lienor herein{{ $claimantName ? ', '.$claimantName.',' : '' }}
    @endif
    whose address is {!! $wide($claimant['address_line'] ?? null) !!}; and that in accordance with a contract with {!! $wide($hiring['display_name'] ?? null) !!}, lienor furnished labor, services, or materials consisting of {!! $wide($filing['description_of_work']) !!} on the following described real property in {!! $blank($project['county'], 'fill fill-mid') !!} County, Florida:
</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

<p>
    owned by {!! $wide($owner['display_name'] ?? null) !!} of a total value of {{ $totalWords ? $totalWords.' ' : '' }}(${!! $blank($total, 'fill fill-mid') !!}), of which there remains unpaid ${!! $blank($filing['amount'], 'fill fill-mid') !!}, and furnished the first of the items on {!! $blank($project['dates']['first_furnish']) !!}, and the last of the items on {!! $blank($project['dates']['last_furnish']) !!}{!! $privitySentence !!}{!! $contractorSentence !!}.
</p>

{{-- The statutory paragraph already states the total value and the unpaid balance, so no amount table. --}}
@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
