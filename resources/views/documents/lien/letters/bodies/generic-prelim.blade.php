{{--
    Generic preliminary notice (notice of furnishing) for states without a
    prescribed form: who is furnishing what, to whom, on which property, for
    roughly how much, and that a lien may follow if the claimant is not paid.
    House wording; the state file adds any statutory clause.
--}}
@php
    $form = $doc['form'];
    $sections = $form['sections'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $hiring = $parties['hiring'];
    $gc = $parties['gc'];
    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    $amountMode = $sections['amount'] ?? 'estimate';
@endphp
<p><strong>THIS IS NOT A LIEN.</strong> This notice is given to preserve the lien rights of the claimant named below. It is not a reflection on the credit or integrity of any owner, contractor or subcontractor.</p>

<table class="fields">
    <tr>
        <td class="k">Claimant (person giving notice)</td>
        <td>@include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])</td>
    </tr>
    @if ($project['claimant_type_phrase'])
        <tr>
            <td class="k">Relationship to the project</td>
            <td>{{ ucfirst($project['claimant_type_phrase']) }}</td>
        </tr>
    @endif
    <tr>
        <td class="k">Person who contracted with the claimant</td>
        <td>@include('documents.lien._parts.party', ['party' => $hiring])</td>
    </tr>
    @if (! empty($sections['gc']) && $gc !== null && ! $same($gc, $hiring) && ! $same($gc, $claimant))
        <tr>
            <td class="k">Original (direct) contractor</td>
            <td>@include('documents.lien._parts.party', ['party' => $gc])</td>
        </tr>
    @endif
    <tr>
        <td class="k">Owner or reputed owner</td>
        <td>@include('documents.lien._parts.party', ['party' => $parties['owner']])</td>
    </tr>
    @if (! empty($sections['lender']) && $parties['lender'] !== null)
        <tr>
            <td class="k">Construction lender</td>
            <td>@include('documents.lien._parts.party', ['party' => $parties['lender']])</td>
        </tr>
    @endif
    <tr>
        <td class="k">Property (jobsite)</td>
        <td>@include('documents.lien._parts.property')</td>
    </tr>
    <tr>
        <td class="k">Labor, services, equipment or materials furnished or to be furnished</td>
        <td>@include('documents.lien._parts.work')</td>
    </tr>
    @if ($sections['first_furnish'] ?? true)
        <tr>
            <td class="k">First furnished</td>
            <td>@if ($project['dates']['first_furnish']){{ $project['dates']['first_furnish'] }}@else<span class="fill">&nbsp;</span>@endif</td>
        </tr>
    @endif
    @if (! empty($sections['last_furnish']))
        <tr>
            <td class="k">Last furnished</td>
            <td>@if ($project['dates']['last_furnish']){{ $project['dates']['last_furnish'] }}@else<span class="fill">&nbsp;</span>@endif</td>
        </tr>
    @endif
    @if ($amountMode === 'estimate')
        <tr>
            <td class="k">Estimated total price</td>
            <td>$@if ($filing['estimate']){{ $filing['estimate'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</td>
        </tr>
    @elseif ($amountMode !== 'none')
        <tr>
            <td class="k">Amount unpaid</td>
            <td>$@if ($filing['amount']){{ $filing['amount'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</td>
        </tr>
    @endif
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<p>You are hereby notified that the claimant has furnished or will furnish the labor, services, equipment or materials described above for the improvement of the property described above. If the claimant is not paid in full for them, the claimant may claim a lien against the property and its improvements to the extent the law allows. Please direct any question about this notice to the claimant at the address above.</p>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
