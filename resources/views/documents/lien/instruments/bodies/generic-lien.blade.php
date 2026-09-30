{{--
    Generic mechanics lien body: the numbered statement every state without a
    prescribed form renders (Georgia, Texas, Arizona, California and the
    Phase B states). The state file switches sections on and off and slots
    its clauses in; the text here is house wording, not statute.
--}}
@php
    $form = $doc['form'];
    $sections = $form['sections'];
    $clauses = $form['clauses'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $details = $doc['details'];
    $signer = $doc['signer'];

    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    $gc = $parties['gc'];
    $lender = $parties['lender'];

    $claimantName = $claimant['display_name'] ?? null;
    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    $hiringIsOwner = $same($hiring, $owner);
    $showGc = ! empty($sections['gc']) && $gc !== null && ! $same($gc, $hiring) && ! $same($gc, $claimant);
    $noticeDate = $details['notice_served_at'] ?? $project['dates']['prelim_sent'];
    $showNotice = ! empty($sections['prior_notice']) && ($noticeDate !== null || $project['in_privity'] === false);
    $contractType = $project['contract_type'];
    $n = 0;
@endphp
<p>
    @if ($claimantName){{ $claimantName }}@else<span class="fill fill-wide">&nbsp;</span>@endif
    ("Claimant") claims a lien{{ ! empty($form['statute']) ? ' under '.$form['statute'] : '' }} upon the real property and improvements described below, in the amount stated below, for labor, services, equipment or materials furnished for the improvement of that property, and states:
</p>

<table class="item">
    <tr>
        <td class="n">{{ ++$n }}.</td>
        <td>
            <span class="lbl">Claimant.</span>
            @include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])
            @if (! empty($sections['license']))
                <div>Contractor's license number: @if ($signer['license_number']){{ $signer['license_number'] }}@else<span class="fill">&nbsp;</span>@endif</div>
            @endif
            @if ($project['claimant_type_phrase'])
                <div>Claimant furnished the labor, services, equipment or materials described below as a {{ $project['claimant_type_phrase'] }}.</div>
            @endif
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">{{ ++$n }}.</td>
        <td>
            <span class="lbl">Owner or reputed owner of the property.</span>
            @include('documents.lien._parts.party', ['party' => $owner])
            @if (! empty($sections['owner_interest']))
                <div>Interest of the owner in the property: @if ($details['owner_interest']){{ $details['owner_interest'] }}@else<span class="fill">&nbsp;</span>@endif</div>
            @endif
        </td>
    </tr>
</table>

@if ($sections['hiring_party'] ?? true)
    <table class="item">
        <tr>
            <td class="n">{{ ++$n }}.</td>
            <td>
                <span class="lbl">Person who contracted with Claimant.</span>
                @if ($hiringIsOwner)
                    <div>Claimant contracted directly with the owner named above.</div>
                @else
                    @include('documents.lien._parts.party', ['party' => $hiring])
                @endif
                @if (! empty($sections['contract_type']))
                    <div>
                        The contract was
                        @if ($contractType === 'written')written; a copy is attached.
                        @elseif ($contractType === 'oral')oral, on the following terms, time given and conditions: <span class="fill fill-wide">&nbsp;</span>
                        @else<span class="fill fill-short">&nbsp;</span> written (copy attached) &nbsp; <span class="fill fill-short">&nbsp;</span> oral, on the following terms, time given and conditions: <span class="fill fill-wide">&nbsp;</span>
                        @endif
                    </div>
                @endif
                @if (! empty($sections['contract_date']))
                    <div>Date of the contract: @if ($details['contract_date']){{ $details['contract_date'] }}@else<span class="fill">&nbsp;</span>@endif</div>
                @endif
            </td>
        </tr>
    </table>
@endif

@if ($showGc)
    <table class="item">
        <tr>
            <td class="n">{{ ++$n }}.</td>
            <td>
                <span class="lbl">Original (general) contractor.</span>
                @include('documents.lien._parts.party', ['party' => $gc])
            </td>
        </tr>
    </table>
@endif

@if (! empty($sections['lender']) && $lender !== null)
    <table class="item">
        <tr>
            <td class="n">{{ ++$n }}.</td>
            <td>
                <span class="lbl">Construction lender.</span>
                @include('documents.lien._parts.party', ['party' => $lender])
            </td>
        </tr>
    </table>
@endif

<table class="item">
    <tr>
        <td class="n">{{ ++$n }}.</td>
        <td>
            <span class="lbl">Property subject to the lien.</span>
            @include('documents.lien._parts.property')
        </td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<table class="item">
    <tr>
        <td class="n">{{ ++$n }}.</td>
        <td>
            <span class="lbl">Labor, services, equipment or materials furnished.</span>
            @include('documents.lien._parts.work')
            @include('documents.lien._parts.dates')
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">{{ ++$n }}.</td>
        <td>
            <span class="lbl">Amount claimed.</span>
            <div>After deducting all just credits and offsets, the amount claimed is $@if ($filing['amount']){{ $filing['amount'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif{{ ! empty($sections['amount_in_words']) && $filing['amount_words'] ? ' ('.$filing['amount_words'].')' : '' }}.</div>
            @include('documents.lien._parts.amounts')
        </td>
    </tr>
</table>

@if ($showNotice)
    <table class="item">
        <tr>
            <td class="n">{{ ++$n }}.</td>
            <td>
                <span class="lbl">Prior notice.</span>
                <div>Claimant served its {{ $doc['titles']['prelim_notice'] }} on @if ($noticeDate){{ $noticeDate }}@else<span class="fill">&nbsp;</span>@endif by @if ($details['notice_served_method_label']){{ $details['notice_served_method_label'] }}@else<span class="fill">&nbsp;</span>@endif.</div>
            </td>
        </tr>
    </table>
@endif

@foreach ((array) ($clauses['affirmations'] ?? []) as $affirmation)
    <table class="item">
        <tr>
            <td class="n">{{ ++$n }}.</td>
            <td>{{ $affirmation }}</td>
        </tr>
    </table>
@endforeach

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
