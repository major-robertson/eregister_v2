{{--
    Notary signature, printed name, commission expiry and seal lines. Every
    one of these was a rejection reason somewhere (Kansas K.S.A. 53-5a16,
    Oregon "incomplete notary acknowledgement"). Vars: $signatureCaption,
    $nameCaption, $sealCaption, $countyLine (all optional; $countyLine adds
    the county-of-commission line Indiana requires, IC 33-42-9-12(a)(5)(B)).
--}}
<table class="sig-table">
    <tr>
        <td style="width: 58%;">
            <div class="sig-line">&nbsp;</div>
            <div class="sig-caption">{{ $signatureCaption ?? 'Notary Public (signature)' }}</div>
        </td>
        <td style="width: 42%;">
            <div class="sig-line">&nbsp;</div>
            <div class="sig-caption">My commission expires</div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="sig-line">&nbsp;</div>
            <div class="sig-caption">{{ $nameCaption ?? "Notary's printed or typed name" }}</div>
        </td>
        <td>
            <div class="sig-caption" style="padding-top: 14pt;">{{ $sealCaption ?? '(Notary seal)' }}</div>
        </td>
    </tr>
    @if ($countyLine ?? false)
        <tr>
            <td>
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">Notary's county of commission</div>
            </td>
            <td>&nbsp;</td>
        </tr>
    @endif
</table>
