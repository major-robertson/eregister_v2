{{--
    Waiver signature area, shared by every state body. Two modes:
      - Print/unsigned ($esign null): blank ruled lines for wet signing.
      - E-signed ($esign set): the adopted signature (image or italic-serif
        typed name) with the e-sign stamp, plus the signer identity fields.

    Vars: $waiver (payload), $esign (nullable)
--}}
<table class="sig-table">
    <tr>
        <td style="width: 55%;">
            @if (($esign ?? null) !== null)
                @if (!empty($esign['image']))
                    <img class="esign-signature-img" src="{{ $esign['image'] }}" alt="Signature of {{ $esign['name'] }}">
                @else
                    <div class="esign-signature-name">{{ $esign['name'] }}</div>
                @endif
                <div class="esign-signature-meta">
                    Electronically signed &middot; {{ $esign['signed_at_eastern'] }} ({{ $esign['signed_at_utc'] }})<br>
                    Signature ID: {{ $esign['signature_id'] }}
                </div>
            @else
                <div class="sig-line">&nbsp;</div>
            @endif
            <div class="sig-caption">Claimant's Signature</div>
        </td>
        <td style="width: 45%;">
            @if (($esign ?? null) !== null)
                <div>{{ $esign['signed_at_eastern'] }}</div>
            @else
                <div class="sig-line">&nbsp;</div>
            @endif
            <div class="sig-caption">Date</div>
        </td>
    </tr>
    <tr>
        <td>
            @if (($esign ?? null) !== null)
                <div>{{ $esign['name'] }}</div>
            @elseif (!empty($waiver['signer']['name']))
                <div>{{ $waiver['signer']['name'] }}</div>
            @else
                <div class="sig-line">&nbsp;</div>
            @endif
            <div class="sig-caption">Print Name</div>
        </td>
        <td>
            @if (!empty($waiver['signer']['title']))
                <div>{{ $waiver['signer']['title'] }}</div>
            @else
                <div class="sig-line">&nbsp;</div>
            @endif
            <div class="sig-caption">Title</div>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            @if (!empty($waiver['signer']['company']))
                <div>{{ $waiver['signer']['company'] }}</div>
            @else
                <div class="sig-line">&nbsp;</div>
            @endif
            <div class="sig-caption">Company (Claimant)</div>
        </td>
    </tr>
</table>
