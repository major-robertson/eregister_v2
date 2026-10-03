@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $rows = \App\Domains\Lien\Seo\LienStateGuide::rows();
    $none = array_values(array_filter($rows, fn (array $row) => ! $row['prelim_required']));
    $everyone = array_values(array_filter($rows, fn (array $row) => $row['prelim_everyone']));
    $faq = \App\Domains\Lien\Seo\LienStateGuide::prelimFaq($rows);
    $sources = array_values(array_map(
        fn (array $row) => ['name' => "{$row['name']}: {$row['statute']}", 'url' => $row['statute_url']],
        array_filter($rows, fn (array $row) => $row['statute'] && $row['statute_url']),
    ));
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources"
    sources-note="Each row comes from that state's page on this site, which shows the full notice rules. These are the statutes those pages cite.">
    <p>
        A preliminary notice tells the property owner, and often the general contractor and the lender, that you are
        working on the project and may claim a lien if you are not paid. States call it different things: preliminary
        notice, notice to owner, notice of furnishing, or pre-lien notice.
    </p>
    <p>
        The notice mainly protects subcontractors and suppliers, who have no contract with the owner. It puts the owner
        on notice that they are on the job, so the owner can make sure they are paid before paying the general
        contractor. In states that require it, missing the notice usually forfeits lien rights for the parties it applies
        to. In some, such as New Hampshire, a late notice still works but covers less of your work.
    </p>
    <p>
        The table shows who must send one in each state, the deadline, who receives it and how to deliver it. Each state
        page has the full rules and the statute behind them.
    </p>

    <h2>Preliminary notice rules in all 50 states</h2>
    <x-guides.table id="prelim-notice-table">
        <caption class="sr-only">Preliminary notice requirement, deadline, recipients and delivery method by state</caption>
        <thead>
            <tr>
                <th scope="col">State</th>
                <th scope="col">Required for</th>
                <th scope="col">Deadline</th>
                <th scope="col">Sent to</th>
                <th scope="col">How</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            @foreach ($rows as $row)
            <tr>
                <th scope="row"><a href="{{ route('liens.state', ['state' => $row['slug']]) }}">{{ $row['name'] }}</a></th>
                <td class="min-w-48">{{ $row['prelim_summary'] }}</td>
                <td class="min-w-72 text-zinc-900">
                    @foreach ($row['prelim_deadline'] as $line)
                    <span @class(['block', 'mt-2' => ! $loop->first])>{{ $line }}</span>
                    @endforeach
                </td>
                <td class="min-w-48">{{ $row['prelim_to'] ? ucfirst($row['prelim_to']) : 'Not applicable' }}</td>
                <td class="min-w-40">{{ $row['prelim_how'] ? ucfirst($row['prelim_how']) : 'Not applicable' }}</td>
            </tr>
            @endforeach
        </tbody>
    </x-guides.table>

    <h2>States with no preliminary notice</h2>
    <p>
        These {{ count($none) }} states do not require a preliminary notice to preserve lien rights:
        @foreach ($none as $row)<a href="{{ route('liens.state', ['state' => $row['slug']]) }}">{{ $row['name'] }}</a>{{ $loop->remaining > 1 ? ', ' : ($loop->remaining === 1 ? ' and ' : '.') }}@endforeach
    </p>
    <p>
        Sending one anyway can still help you get paid. Some of these states require a notice of intent before the lien
        is recorded, which the <a href="{{ route('guides.show', ['slug' => 'mechanics-lien-deadlines-by-state']) }}">mechanics lien deadlines guide</a> lists.
    </p>

    <h2>States where everyone must send one</h2>
    <p>
        In these {{ count($everyone) }} states, every claimant must send a preliminary notice, including the general contractor:
        @foreach ($everyone as $row)<a href="{{ route('liens.state', ['state' => $row['slug']]) }}">{{ $row['name'] }}</a>{{ $loop->remaining > 1 ? ', ' : ($loop->remaining === 1 ? ' and ' : '.') }}@endforeach
    </p>
    <p>
        In the other states that require a notice, it applies to subcontractors and suppliers, or only to those further
        down the chain, as the table shows. In several of the states above, the general contractor's notice is a disclosure
        given before work starts or with the contract, so read the deadline column closely.
    </p>

    <h2>What the notice must say</h2>
    <p>Most lien statutes list what the notice must contain. Common elements are:</p>
    <ul>
        <li>Your name and address, and the name of the party that hired you.</li>
        <li>The owner's name and the property's address or legal description.</li>
        <li>The general contractor and the lender, where known.</li>
        <li>A description of the labor or materials you are furnishing.</li>
        <li>In some states, an estimate of their value.</li>
        <li>Warning language, where the statute prescribes the exact wording.</li>
    </ul>
    <p>
        Some states publish a required form. Use the state's wording where it has one, and confirm with counsel when the
        statute is unclear.
    </p>

    <h2>Send it early</h2>
    <p>
        The safest time to send a preliminary notice is when you start work or make your first delivery. Many deadlines
        count from first furnishing, so the clock may already be running on day one. Sending it early also tells the owner
        who you are before any payment problem exists.
    </p>
    <p>
        Keep proof of delivery. The table lists the method each state requires. Our
        <a href="{{ route('liens.preliminary-notice') }}">preliminary notice service</a> prepares and sends the notice for
        you, and each state page in the table has the full rules.
    </p>
</x-guides.article>
@endsection
