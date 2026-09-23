<x-mail::message>
# Sales tax funnel, {{ $periodLabel }}

Compared with {{ $previousLabel }}.

<x-mail::table>
| Metric | This period | Previous |
|:-------|------------:|---------:|
@foreach ($rows as $row)
| {{ $row['label'] }} | {{ number_format($row['current']) }} | {{ number_format($row['previous']) }} |
@endforeach
</x-mail::table>

@if ($byCampaign !== [])
**Paid registrations by Ads campaign (this period)**

<x-mail::table>
| Campaign id | Paid |
|:------------|-----:|
@foreach ($byCampaign as $campaign => $count)
| {{ $campaign }} | {{ $count }} |
@endforeach
</x-mail::table>
@else
No paid registration this period came from a Google Ads click.
@endif

Ads spend is not in this report. Cost per paid registration = spend ÷ paid registrations.

<x-mail::button :url="$adsUrl">
Open the Ads campaign table for {{ $periodLabel }}
</x-mail::button>

[Previous period in Ads]({{ $previousAdsUrl }})

eRegister
</x-mail::message>
