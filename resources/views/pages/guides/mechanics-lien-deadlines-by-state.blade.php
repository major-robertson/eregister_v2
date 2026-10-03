@extends('layouts.landing')

@section('title', $guide['page_title'])
@section('description', $guide['description'])
@section('canonical', \App\Support\Seo\Guides::url($guide['slug']))
@section('og_type', 'article')

@php
    $rows = \App\Domains\Lien\Seo\LienStateGuide::rows();
    $attorneyStates = array_column(array_filter($rows, fn (array $row) => $row['attorney']), 'name');
    $faq = \App\Domains\Lien\Seo\LienStateGuide::deadlineFaq($rows);
    $sources = array_values(array_map(
        fn (array $row) => ['name' => "{$row['name']}: {$row['statute']}", 'url' => $row['statute_url']],
        array_filter($rows, fn (array $row) => $row['statute'] && $row['statute_url']),
    ));
@endphp

@section('content')
<x-guides.article :guide="$guide" :faq="$faq" :sources="$sources"
    sources-note="Each row comes from that state's page on this site, which shows the full deadline schedule. These are the statutes those pages cite.">
    <p>
        A mechanics lien deadline is the last day you can record a lien against the property you improved.
        Miss it and the lien right is gone, even if the work was done and the invoice is fair.
    </p>
    <p>
        In most states the clock starts when you last furnish labor or materials to the project. Some states count
        from completion of the whole project instead, and Texas counts to the 15th day of a later month. The deadline
        can also depend on who you are. A general contractor hired by the owner may have a different deadline than a
        subcontractor or supplier further down the chain. Some states also set one deadline for homes and another for
        commercial work.
    </p>
    <p>
        Many states require earlier steps to keep the right alive. A preliminary notice may be due soon after you
        start, and some states require a notice of intent to lien before the lien is recorded. Once the lien is
        recorded, you must enforce it with a lawsuit within a set time, or it expires.
    </p>
    <p>
        The table shows each state's headline lien deadline with its notice and enforcement rules. Where the deadline
        varies, the table says by what. Each state page has the full schedule for every role and project type, and the
        statute behind it.
    </p>

    <h2>Mechanics lien deadlines in all 50 states</h2>
    <x-guides.table id="lien-deadlines-table">
        <caption class="sr-only">Mechanics lien deadline, preliminary notice, notice of intent and enforcement deadline by state</caption>
        <thead>
            <tr>
                <th scope="col">State</th>
                <th scope="col">Lien deadline</th>
                <th scope="col">Preliminary notice</th>
                <th scope="col">Notice of intent</th>
                <th scope="col">Enforcement</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100">
            @foreach ($rows as $row)
            <tr>
                <th scope="row">
                    <a href="{{ route('liens.state', ['state' => $row['slug']]) }}">{{ $row['name'] }}</a>@if ($row['attorney'])<sup class="ml-0.5"><a href="#attorney-filing" aria-label="Attorney filing">†</a></sup>@endif
                </th>
                <td class="min-w-56 text-zinc-900">{{ $row['lien'] ? ucfirst($row['lien']) : 'See the state page' }}</td>
                <td class="min-w-48">{{ $row['prelim_summary'] }}</td>
                <td class="min-w-40">{{ $row['noi'] }}</td>
                <td class="min-w-44">
                    @if ($row['enforcement'])
                    {{ $row['enforcement'] }}
                    @elseif ($row['statute_url'])
                    <a href="{{ $row['statute_url'] }}" rel="noopener" target="_blank">See the statute</a>
                    @else
                    See the state page
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </x-guides.table>
    @if ($attorneyStates)
    <p id="attorney-filing" class="text-sm text-zinc-500">
        † Attorney filing. In {{ \App\Domains\Lien\Seo\LienStateGuide::listNames($attorneyStates) }}, the lien is filed through the courts by an attorney.
    </p>
    @endif

    <h2>How to read this table</h2>
    <p>
        "Last furnishing" means the last day you performed work or delivered materials under your contract. Small
        corrections and warranty work often do not count, so use the last day of real work on the job.
    </p>
    <p>
        Where the deadline varies by project type, the table shows the residential deadline. Where it varies by role,
        it shows the first claimant group in the state's schedule, usually the general contractor. The state page lists
        every combination.
    </p>
    <p>
        If a deadline falls on a weekend or holiday, the statute decides whether it moves to the next business day.
        Some states extend it and some do not. Plan to record before the date the count lands on.
    </p>
    <p>
        To turn these rules into calendar dates for your project, use the free
        <a href="{{ route('liens.deadline-calculator') }}">mechanics lien deadline calculator</a>. It applies your
        state's rule for your role and project type to the dates you enter. For the notice that comes first, see
        <a href="{{ route('guides.show', ['slug' => 'preliminary-notice-requirements-by-state']) }}">preliminary notice requirements by state</a>.
    </p>
</x-guides.article>
@endsection
