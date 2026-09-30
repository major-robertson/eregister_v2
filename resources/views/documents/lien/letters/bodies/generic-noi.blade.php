{{--
    Generic notice of intent to lien: the unpaid claim, the property, and a
    demand to pay within sections.demand_days before the claimant records
    its lien. House wording; clauses.demand replaces the demand paragraph
    where a state prescribes one.
--}}
@php
    $form = $doc['form'];
    $sections = $form['sections'];
    $clauses = $form['clauses'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $hiring = $parties['hiring'];
    $days = (int) ($sections['demand_days'] ?? 10);
    $lienTitle = $doc['titles']['mechanics_lien'] ?? 'Claim of Lien';
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<p>
    You are hereby notified that {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!} ("Claimant") furnished labor, services, equipment or materials for the improvement of the property described below under a contract with {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}, and that ${!! $blank($filing['amount'], 'fill fill-mid') !!} remains unpaid.
</p>

<table class="fields">
    <tr>
        <td class="k">Property</td>
        <td>@include('documents.lien._parts.property')</td>
    </tr>
    <tr>
        <td class="k">Labor, services, equipment or materials furnished</td>
        <td>
            @include('documents.lien._parts.work')
            @include('documents.lien._parts.dates')
        </td>
    </tr>
    <tr>
        <td class="k">Amount unpaid</td>
        <td>
            <div>$@if ($filing['amount']){{ $filing['amount'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</div>
            @include('documents.lien._parts.amounts')
        </td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

@if (! empty($clauses['demand']))
    <p>{{ $clauses['demand'] }}</p>
@else
    <p>
        Unless payment in full of the amount stated above is received within {{ $days }} days after the date of this notice, Claimant intends to record a {{ $lienTitle }} against the property and its improvements and to pursue every other remedy the law allows, including the recovery of interest, costs and attorney's fees where permitted. This notice is given without waiver of any right or remedy.
    </p>
@endif

<p>Please contact Claimant at the address above to arrange payment or to discuss this notice.</p>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
