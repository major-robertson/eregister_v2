<x-mail::message>
# Lien filings waiting on us

These cards are red on the lien board. Each one has had no action from us for more than {{ \App\Domains\Lien\Admin\Support\BoardCard::IDLE_BUSINESS_DAYS }} business days, or has a customer answer or email with nothing from us since.

@foreach ($cards as $card)
**{{ $card['business'] }}** · {{ $card['document'] }} · {{ $card['column'] }}<br>
{{ $card['alert'] }}. [Open filing]({{ $card['url'] }})

@endforeach
<x-mail::button :url="$boardUrl">
Open the board
</x-mail::button>

Thanks,<br>
eRegister
</x-mail::message>
