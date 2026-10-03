@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $data = \App\Domains\SalesTax\Seo\NexusGuide::data();
    $rows = $data['rows'];
    $transactionStates = \App\Domains\SalesTax\Seo\NexusGuide::transactionStates($data);
    $bothStates = \App\Domains\SalesTax\Seo\NexusGuide::bothTestStates($data);
    $faq = \App\Domains\SalesTax\Seo\NexusGuide::faq($data);
    $sources = array_merge(
        [['name' => 'South Dakota v. Wayfair, Inc., No. 17-494 (U.S. June 21, 2018), opinion of the Court', 'url' => 'https://www.supremecourt.gov/opinions/17pdf/17-494_j4el.pdf']],
        array_values(array_map(
            fn (array $row) => ['name' => $row['agency'], 'url' => $row['agency_url']],
            array_filter($rows, fn (array $row) => $row['agency_url']),
        )),
    );
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources"
    sources-note="Each row comes from that state's page on this site, which cites the statute or agency guidance for the threshold. The agency pages are listed here.">
    <p>
        Until 2018, a state could make a seller collect its sales tax only if the seller had a physical presence there,
        such as a store, an office or a warehouse. In South Dakota v. Wayfair, Inc., the U.S. Supreme Court ended that
        rule. A state can now require a seller with no physical presence to register and collect once its sales into
        the state pass a set level. That level is the economic nexus threshold.
    </p>
    <p>
        Most states set the threshold as a dollar amount of sales into the state. Some also count separate transactions.
        Each state measures over its own period, such as the previous or current calendar year, or the last 12 months.
        Crossing the threshold means you must register with that state's tax agency and collect its tax.
    </p>
    <p>
        The table lists the threshold, the period, the date the current threshold took effect and the agency for
        {{ count($rows) }} jurisdictions: every state with a statewide sales tax, plus the District of Columbia. Each
        state page has the registration details.
    </p>

    <h2>Economic nexus thresholds by state</h2>
    <x-guides.table id="nexus-thresholds-table">
        <caption class="sr-only">Economic nexus threshold, measurement period, effective date and tax agency by state</caption>
        <thead>
            <tr>
                <th scope="col">State</th>
                <th scope="col">Threshold</th>
                <th scope="col">Period</th>
                <th scope="col">Effective</th>
                <th scope="col">Agency</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            @foreach ($rows as $row)
            <tr>
                <th scope="row"><a href="{{ route('sales-tax-registration.state', ['state' => $row['slug']]) }}">{{ $row['name'] }}</a></th>
                <td class="min-w-48 text-zinc-900">{{ ucfirst($row['threshold']) }}</td>
                <td class="min-w-56">{{ $row['period'] ? ucfirst($row['period']) : 'See the state page' }}</td>
                <td class="whitespace-nowrap">{{ $row['effective'] ?? 'See the state page' }}</td>
                <td class="min-w-56">{{ $row['agency'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </x-guides.table>

    <h2>States with no general sales tax</h2>
    <p>These states have no statewide sales tax, so there is no state threshold to cross.</p>
    <ul>
        @foreach ($data['no_tax'] as $state)
        <li>
            @if ($state['resale_slug'])
            <a href="{{ route('resale-certificates.state', ['state' => $state['resale_slug']]) }}">{{ $state['name'] }}</a>:
            @else
            {{ $state['name'] }}:
            @endif
            {{ $state['sentence'] }}
        </li>
        @endforeach
    </ul>

    <h2>Transaction thresholds</h2>
    <p>
        {{ count($transactionStates) }} of the {{ count($rows) }} jurisdictions in the table also count transactions:
        {{ \App\Domains\SalesTax\Seo\NexusGuide::list($transactionStates) }}.
        @if ($bothStates)
        In {{ \App\Domains\SalesTax\Seo\NexusGuide::list($bothStates) }}, you must cross both the dollar amount and the
        transaction count. In the others, crossing either one is enough.
        @else
        Crossing either the dollar amount or the transaction count is enough.
        @endif
        The other {{ count($rows) - count($transactionStates) }} use a dollar amount alone.
    </p>

    <h2>What happens when you cross it</h2>
    <p>
        Once you cross a state's threshold, register with its tax agency and start collecting tax on taxable sales into
        the state. Some states give you a short window before collection must start, and the state pages show it where
        the state sets one.
    </p>
    <p>
        After you register, you file returns on the schedule the agency assigns. We prepare and file
        <a href="{{ route('sales-tax-registration') }}">sales tax registrations</a> for sellers who have crossed a
        threshold.
    </p>

    <h2>Marketplace sales</h2>
    <p>
        If you sell through an online marketplace, the marketplace usually collects and remits the tax on those sales.
        States differ on whether those sales count toward your own threshold. Each state page notes what that state says
        about marketplace sales.
    </p>
</x-guides.article>
@endsection
