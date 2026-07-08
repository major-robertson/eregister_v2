{{--
    Standard identification field table at the top of a waiver. Statutory
    bodies with their own header layout (e.g. WY, MA) skip this partial.

    Vars: $waiver, $rows (optional list limiting which rows render)
--}}
@php
    $all = [
        'claimant' => ['Name of Claimant', $waiver['claimant']['company'] ?? null],
        'customer' => ['Name of Customer', $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? null)],
        'job_location' => ['Job Location', $waiver['project']['address_line'] ?? null],
        'owner' => ['Owner', $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? null)],
        'invoice' => ['Invoice / Application No.', $waiver['invoice_number'] ?? null],
        'through_date' => ['Through Date', $waiver['through_date'] ?? null],
        'amount' => ['Amount', ($waiver['amount'] ?? null) !== null ? '$'.$waiver['amount'] : null],
        'check_maker' => ['Maker of Check', $waiver['check_maker'] ?? null],
        'check_number' => ['Check No.', $waiver['check_number'] ?? null],
    ];
    $keys = $rows ?? array_keys($all);
@endphp
<table class="waiver-fields">
    @foreach ($keys as $key)
        @if (isset($all[$key]))
            <tr>
                <td class="label">{{ $all[$key][0] }}:</td>
                <td class="value">{{ $all[$key][1] ?? '' }}&nbsp;</td>
            </tr>
        @endif
    @endforeach
</table>
