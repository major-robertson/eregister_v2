{{--
    Redesigned generic-family header: two-line form title on the left with
    reserved logo space on the right, state line, then the heavy rule.

    Vars: $waiver, $line1, $line2, $kindLabel
--}}
<table class="gw-header">
    <tr>
        <td>
            <div class="gw-title">{{ $line1 }}<br>{{ $line2 }}</div>
            <div class="gw-kind">{{ $kindLabel }}</div>
            <div class="gw-state">State of {{ $waiver['form']['state_name'] }}</div>
        </td>
        <td class="gw-logo-space">&nbsp;</td>
    </tr>
</table>
<div class="gw-rule"></div>
