{{--
    The amount block. sections.amount = 'breakdown' (or 'itemized', which
    Phase B states extend) prints contract / additions / credits / payments
    / uncompleted work / retainage down to the amount claimed; 'single'
    leaves the sentence to the body. amount_in_words adds the spelled-out
    amount that Florida and Utah instruments carry.
--}}
@php
    $sections = $doc['form']['sections'];
    $mode = $sections['amount'] ?? 'breakdown';
    $amounts = $doc['project']['amounts'];
    $filing = $doc['filing'];
    $nonZero = fn (array $row) => $row['cents'] !== null && $row['cents'] !== 0;
@endphp
@if (in_array($mode, ['breakdown', 'itemized'], true))
    <table class="amounts">
        <tr>
            <td>Contract amount (agreed price or reasonable value of the labor and materials)</td>
            <td class="money">$ @if ($amounts['contract']['formatted']){{ $amounts['contract']['formatted'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</td>
        </tr>
        @if ($nonZero($amounts['change_orders']))
            <tr><td>Additions (change orders and extras)</td><td class="money">$ {{ $amounts['change_orders']['formatted'] }}</td></tr>
        @endif
        @if ($nonZero($amounts['credits']))
            <tr><td>Less credits and offsets</td><td class="money">$ {{ $amounts['credits']['formatted'] }}</td></tr>
        @endif
        <tr>
            <td>Less payments received</td>
            <td class="money">$ @if ($amounts['payments']['formatted']){{ $amounts['payments']['formatted'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</td>
        </tr>
        @if ($nonZero($amounts['uncompleted']))
            <tr><td>Less work not completed</td><td class="money">$ {{ $amounts['uncompleted']['formatted'] }}</td></tr>
        @endif
        @if (! empty($sections['retainage']))
            <tr><td>Less retainage withheld</td><td class="money">$ <span class="fill fill-mid">&nbsp;</span></td></tr>
        @endif
        <tr class="total">
            <td>Amount claimed, after deducting all just credits and offsets</td>
            <td class="money">$ @if ($filing['amount']){{ $filing['amount'] }}@else<span class="fill fill-mid">&nbsp;</span>@endif</td>
        </tr>
    </table>
@endif
@if (! empty($sections['amount_in_words']) && $filing['amount_words'])
    <div>Amount claimed in words: {{ $filing['amount_words'] }} (${{ $filing['amount'] }}).</div>
@endif
