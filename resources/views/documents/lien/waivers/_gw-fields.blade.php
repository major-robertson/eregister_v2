{{--
    Labeled underline field grid for the redesigned generic family. The
    required rows always render (blank lines are legally normal); the optional
    identification fields (check maker/number, invoice) and the exceptions
    line render ONLY when they carry a value, and disappear entirely when
    blank — the operative text conditions its references to match.

    Vars: $waiver, $showThrough (progress kinds carry a through date)
--}}
@php
    $optional = array_filter([
        'Maker of Check' => $waiver['check_maker'] ?? null,
        'Invoice / Application No.' => $waiver['invoice_number'] ?? null,
        'Check No.' => $waiver['check_number'] ?? null,
    ]);
    // Maker of check is usually the longest value; give it the wider column.
    $optionalWidths = match (count($optional)) {
        3 => [40, 33, 27],
        2 => [55, 45],
        default => [100],
    };
@endphp
<table class="gw-fields">
    <tr>
        <td style="width: 50%;">
            <div class="gw-label">Name of Claimant</div>
            <div class="gw-value">{{ $waiver['claimant']['company'] ?? '' }}&nbsp;</div>
        </td>
        <td class="last" style="width: 50%;">
            <div class="gw-label">Name of Customer</div>
            <div class="gw-value">{{ $waiver['customer']['company'] ?? ($waiver['customer']['name'] ?? '') }}&nbsp;</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="last">
            <div class="gw-label">Job Location</div>
            <div class="gw-value">{{ $waiver['project']['address_line'] ?? '' }}&nbsp;</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="last">
            <div class="gw-label">Property Owner</div>
            <div class="gw-value">{{ $waiver['owner']['company'] ?? ($waiver['owner']['name'] ?? '') }}&nbsp;</div>
        </td>
    </tr>
    <tr>
        @if ($showThrough ?? true)
            <td style="width: 50%;">
                <div class="gw-label">Amount</div>
                <div class="gw-value">{{ ($waiver['amount'] ?? null) !== null ? '$'.$waiver['amount'] : '' }}&nbsp;</div>
            </td>
            <td class="last" style="width: 50%;">
                <div class="gw-label">Through Date</div>
                <div class="gw-value">{{ $waiver['through_date'] ?? '' }}&nbsp;</div>
            </td>
        @else
            <td style="width: 50%;">
                <div class="gw-label">Amount</div>
                <div class="gw-value">{{ ($waiver['amount'] ?? null) !== null ? '$'.$waiver['amount'] : '' }}&nbsp;</div>
            </td>
            <td class="last">&nbsp;</td>
        @endif
    </tr>
</table>

@if (count($optional) > 0)
    <table class="gw-fields">
        <tr>
            @foreach ($optional as $label => $value)
                <td style="width: {{ $optionalWidths[$loop->index] }}%;" @if ($loop->last) class="last" @endif>
                    <div class="gw-label">{{ $label }}</div>
                    <div class="gw-value">{{ $value }}&nbsp;</div>
                </td>
            @endforeach
        </tr>
    </table>
@endif

@if (!empty($waiver['exceptions']))
    <table class="gw-fields">
        <tr>
            <td class="last">
                <div class="gw-label">Exceptions</div>
                <div class="gw-value">{{ $waiver['exceptions'] }}&nbsp;</div>
            </td>
        </tr>
    </table>
@endif
