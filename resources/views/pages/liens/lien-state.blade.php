@extends('layouts.landing')

@section('title', $page->title())
@section('description', $page->metaDescription())
@section('canonical', $page->url())

@section('content')
@php
    /** @var \App\Domains\Lien\Seo\LienStatePage $page */
    $name = $page->name;
    $rule = $page->rule;
    $a = $page->article;
    $referral = $page->requiresAttorney();
    $deadlineSections = [
        'prelim_notice' => ['title' => 'Preliminary notice deadline', 'blurb' => 'The notice that preserves lien rights before any payment problem exists.'],
        'noi' => ['title' => 'Notice of intent to lien', 'blurb' => 'The final warning before a lien is recorded.'],
        'mechanics_lien' => ['title' => 'Mechanics lien filing deadline', 'blurb' => 'The last day the lien can be recorded against the property.'],
    ];
@endphp

<x-seo.service
    name="{{ $name }} Mechanics Lien Filing"
    description="Prepare and record {{ $a }} {{ $name }} mechanics lien with the correct deadlines, notices, and statutory form, filed with the {{ $page->filingLocationPhrase() }}."
    :url="$page->url()"
    :price="$referral ? null : $page->selfServePrice()"
    category="Mechanics lien filing" />

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-b from-zinc-900 via-zinc-900 to-zinc-800 py-20 lg:py-24">
    <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <x-seo.breadcrumbs class="text-zinc-400" :items="[
            ['name' => 'Home', 'url' => route('home')],
            ['name' => 'Mechanics Liens', 'url' => route('liens')],
            ['name' => $name],
        ]" />
        <h1 class="mt-8 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
            {{ $name }} Mechanics Lien<br>
            <span class="bg-gradient-to-r from-amber-400 to-orange-500 bg-clip-text text-transparent">Deadlines &amp; Filing Rules</span>
        </h1>
        <p class="mt-6 max-w-2xl text-lg text-zinc-400">
            Every notice, deadline, and recording requirement for {{ $a }} {{ $name }} construction lien, drawn from
            @if ($page->statutes) {{ $page->statutes[0] }} and @endif the same rule engine that calculates deadlines for our filing customers.
        </p>
        <div class="mt-10 flex flex-wrap gap-4">
            <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:scale-105 hover:bg-[#B91C1C]">
                @if ($referral)
                Get matched with {{ $a }} {{ $name }} lien attorney
                @else
                File {{ $a }} {{ $name }} lien from ${{ $page->selfServePrice() }}
                @endif
                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
            <a href="#deadlines" class="inline-flex items-center gap-2 rounded-lg border border-zinc-700 px-8 py-4 text-base font-semibold text-zinc-200 transition hover:bg-zinc-800">
                See the deadlines
            </a>
        </div>
    </div>
</section>

{{-- Key facts --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} lien rules at a glance</h2>
        <dl class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($page->keyFacts() as $fact)
            <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
                <dt class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ $fact['label'] }}</dt>
                <dd class="mt-2 text-lg font-semibold text-zinc-900">{{ $fact['value'] }}</dd>
                @if (! empty($fact['detail']))
                <dd class="mt-1 text-sm text-zinc-600">{{ $fact['detail'] }}</dd>
                @endif
            </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Who can file --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Who can file a mechanics lien in {{ $name }}</h2>
        <p class="mt-4 max-w-3xl text-zinc-600">
            Lien rights depend on where you sit in the contracting chain. {{ $page->tenantSentence() }}
            @if ($page->ownerOccupiedSentence()) {{ $page->ownerOccupiedSentence() }} @endif
        </p>
        <div class="mt-10 overflow-x-auto rounded-2xl border border-zinc-200 bg-white">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">Claimant</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Lien rights in {{ $name }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($page->lienRights() as $row)
                    <tr>
                        <td class="px-6 py-4 font-medium text-zinc-900">{{ $row['role'] }}</td>
                        <td class="px-6 py-4">
                            @if ($row['rights'])
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Yes</span>
                            @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 px-3 py-1 text-xs font-semibold text-zinc-600">No</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- Deadlines --}}
<section id="deadlines" class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} mechanics lien deadlines</h2>
        <p class="mt-4 max-w-3xl text-zinc-600">
            {{ $page->prelimSummary() }}.
            @if ($rule->pre_notice_required && $page->prelimRecipientsLabel())
            Preliminary notices go to {{ $page->prelimRecipientsLabel() }}@if ($page->prelimDeliveryLabel()) by {{ $page->prelimDeliveryLabel() }}@endif.
            @endif
            @if ($page->noiSentence()) {{ $page->noiSentence() }} @endif
        </p>

        @if ($calculatorRules)
        <div class="mt-10">
            <h3 class="text-xl font-semibold text-zinc-900">Calculate your {{ $name }} deadlines</h3>
            <p class="mt-1 text-sm text-zinc-500">Enter your role and project dates to turn the rules below into calendar dates. For other states, use the <a href="{{ route('liens.deadline-calculator') }}" class="font-medium text-zinc-700 underline">mechanics lien deadline calculator</a>.</p>
            <x-seo.lien-deadline-calculator class="mt-4" :exports="$calculatorRules" :state="$page->code" />
        </div>
        @endif

        <div class="mt-10 space-y-10">
            @foreach ($deadlineSections as $slug => $section)
                @continue(empty($page->deadlines[$slug]))
                <div>
                    <h3 class="text-xl font-semibold text-zinc-900">{{ $section['title'] }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ $section['blurb'] }}</p>
                    <div class="mt-4 overflow-x-auto rounded-2xl border border-zinc-200">
                        <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                            <thead class="bg-zinc-50 text-xs uppercase tracking-wider text-zinc-500">
                                <tr>
                                    <th scope="col" class="px-6 py-3 font-semibold">Who</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Project type</th>
                                    <th scope="col" class="px-6 py-3 font-semibold">Deadline</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100">
                                @foreach ($page->deadlines[$slug] as $row)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-zinc-900">{{ $row['who'] }}</td>
                                    <td class="px-6 py-4 text-zinc-600">{{ $row['scope'] }}</td>
                                    <td class="px-6 py-4 text-zinc-900">{{ ucfirst($row['when']) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            @if (empty($page->deadlines['noi']) && ! $page->noiSentence())
            <p class="text-sm text-zinc-500">{{ $name }} does not require a notice of intent before recording a lien, but sending one is often what gets the invoice paid.</p>
            @endif
        </div>
    </div>
</section>

@if ($page->depth && $page->howToFile())
{{-- How to file, step by step (researched states only) --}}
<section id="how-to-file" class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">How to file {{ $a }} {{ $name }} mechanics lien</h2>
        <p class="mt-4 text-zinc-600">
            The steps in order, each with the statute behind it. Some steps apply only to certain claimants, and each says so.
        </p>
        <ol class="mt-10 space-y-6">
            @foreach ($page->howToFile() as $step)
            <li class="rounded-2xl border border-zinc-200 bg-white p-6">
                <div class="flex gap-4">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white">{{ $step['step'] }}</span>
                    <div>
                        <h3 class="font-semibold text-zinc-900">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-600">{{ $step['text'] }}</p>
                        @if (! empty($step['cite']))
                        <p class="mt-2 text-xs text-zinc-500">{{ $step['cite'] }}</p>
                        @endif
                        @if (! empty($step['residential_note']))
                        <aside class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            <strong class="font-semibold">Residential projects:</strong> {{ $step['residential_note'] }}
                        </aside>
                        @endif
                    </div>
                </div>
            </li>
            @endforeach
        </ol>
    </div>
</section>
@endif

@if ($page->depth && $page->claimContents())
@php $contents = $page->claimContents(); @endphp
{{-- What the claim must contain (researched states only) --}}
<section id="claim-contents" class="bg-white py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">What the {{ $page->claimDocumentLabel() }} must contain</h2>
        <p class="mt-4 text-zinc-600">
            {{ $name }} law requires the {{ mb_strtolower($contents['document_name']) }} to include the following{{ ! empty($contents['cite']) ? ' ('.$contents['cite'].')' : '' }}.
        </p>
        <ul class="mt-8 space-y-3">
            @foreach ($contents['required'] as $item)
            <li class="flex gap-3 text-zinc-700">
                <svg class="mt-1 h-4 w-4 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ $item }}</span>
            </li>
            @endforeach
        </ul>
        @if (! empty($contents['notes']))
        <p class="mt-8 text-sm leading-relaxed text-zinc-600">{{ $contents['notes'] }}</p>
        @endif
        @if (! empty($contents['official_form_url']))
        <p class="mt-4 text-sm text-zinc-600">
            <a href="{{ $contents['official_form_url'] }}" rel="noopener" target="_blank" class="font-medium text-zinc-700 underline">See the official {{ $name }} form wording</a>.
        </p>
        @endif
        @if ($blankClaim)
        <div class="mt-8 rounded-2xl border border-zinc-200 bg-zinc-50 p-6">
            <a href="{{ $blankClaim['url'] }}" class="font-semibold text-zinc-900 underline" download>Download a blank {{ $name }} lien claim form</a>
            <p class="mt-2 text-sm text-zinc-600">
                The {{ $blankClaim['title'] }} our filing service prepares, with every field left blank. PDF, free, no sign-up.
                Fill in your own facts and check them against the list above before you sign.
            </p>
        </div>
        @endif
    </div>
</section>
@endif

{{-- Recording, enforcement, penalties --}}
<section class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Recording and enforcing {{ $a }} {{ $name }} lien</h2>
        <div class="mt-10 grid gap-8 lg:grid-cols-3">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <h3 class="font-semibold text-zinc-900">Recording the lien</h3>
                <p class="mt-3 text-sm text-zinc-600">
                    Record with the {{ $page->filingLocationPhrase() }}.
                    {{ $rule->efile_allowed ? 'Electronic recording is available in participating counties.' : 'Whether electronic recording is accepted varies by county, so confirm with the recording office and build mailing time into the deadline.' }}
                    {{ $rule->notarization_required ? 'The lien must be notarized.' : 'No notary is required on the lien itself.' }}
                    @if ($page->verificationLabel()) {{ $page->verificationLabel() }}. @endif
                </p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <h3 class="font-semibold text-zinc-900">After recording</h3>
                <p class="mt-3 text-sm text-zinc-600">
                    {{ $page->postLienNoticeSentence() ?? "{$name} does not require a separate notice after the lien is recorded, though most claimants send one anyway to prompt payment." }}
                    @if ($page->enforcementSentence())
                    To keep the lien alive it must be enforced by lawsuit {{ $page->enforcementSentence() }}.
                    @endif
                </p>
            </div>
            <div class="rounded-2xl border border-zinc-200 bg-white p-6">
                <h3 class="font-semibold text-zinc-900">Wrongful lien exposure</h3>
                <p class="mt-3 text-sm text-zinc-600">
                    {{ $page->penaltySentence() ?? "We found no dedicated wrongful-lien penalty in {$name}'s lien statute. An owner can still ask a court to remove an inflated or baseless lien, so claim only what you are owed." }}
                </p>
            </div>
        </div>

        @if ($page->publicNotes)
        <div class="mt-10 rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h3 class="font-semibold text-amber-900">Practitioner notes for {{ $name }}</h3>
            <p class="mt-3 text-sm leading-relaxed text-amber-900/80">{{ $page->publicNotes }}</p>
            <p class="mt-3 text-xs text-amber-900">These notes summarize the statute as we read it.</p>
        </div>
        @endif

        {{-- The disclaimer prints on every state page, with or without notes. --}}
        <p class="mt-8 text-sm text-zinc-500">
            @if ($page->statutes)
            Statutory sources: {{ implode('; ', $page->statutes) }}.
            @if ($rule->statute_url)
            <a href="{{ $rule->statute_url }}" rel="noopener" target="_blank" class="font-medium text-zinc-700 underline">Read the {{ $name }} lien statute</a>.
            @endif
            @endif
            Deadlines turn on facts specific to your project. Confirm with counsel before relying on them.
        </p>
    </div>
</section>

@if ($page->depth && $page->counties())
@php
    $verb = $page->filingVerb();
    $electronic = $verb === 'file' ? 'E-filing' : 'E-recording';
@endphp
{{-- County offices (researched states only) --}}
<section id="county-offices" class="bg-white py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Where to {{ $verb }} in {{ $name }}</h2>
        @if ($page->countyIntro())
        <p class="mt-4 max-w-3xl text-zinc-600">{{ $page->countyIntro() }}</p>
        @endif
        <p class="mt-4 max-w-3xl text-zinc-600">
            The {{ count($page->counties()) }} most populous {{ $name }} counties, the office that takes liens in each, whether it accepts {{ mb_strtolower($electronic) }}, and the fee it publishes.
            Liens on property in any other county go to that county's office.
        </p>
        <div class="mt-10 overflow-x-auto rounded-2xl border border-zinc-200">
            <table class="min-w-full divide-y divide-zinc-200 text-left text-sm">
                <thead class="bg-zinc-50 text-xs uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th scope="col" class="px-6 py-3 font-semibold">County</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Office</th>
                        <th scope="col" class="px-6 py-3 font-semibold">{{ $electronic }}</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Fee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach ($page->counties() as $county)
                    <tr class="align-top">
                        <td class="px-6 py-4 font-medium text-zinc-900">{{ $county['county'] }}</td>
                        <td class="px-6 py-4 text-zinc-700">
                            @if (! empty($county['url']))
                            <a href="{{ $county['url'] }}" rel="noopener" target="_blank" class="font-medium text-zinc-900 underline">{{ $county['office'] }}</a>
                            @else
                            <span class="font-medium text-zinc-900">{{ $county['office'] }}</span>
                            @endif
                            @if (! empty($county['mailing_address']))
                            <div class="mt-1 text-xs text-zinc-500">{{ $county['mailing_address'] }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-zinc-700">
                            {{ \App\Domains\Lien\Seo\LienStatePage::erecordingLabel($county) }}
                            @if (($county['erecording'] ?? null) === true)
                            <div class="mt-1 text-xs text-zinc-500">
                                {{ ! empty($county['erecording_vendors']) ? 'Through '.implode(', ', $county['erecording_vendors']) : 'Vendors not listed online' }}
                            </div>
                            @elseif (($county['erecording'] ?? null) === null)
                            <div class="mt-1 text-xs text-zinc-500">Ask the office</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-zinc-700">
                            @if (! empty($county['fee']['summary']))
                            {{ $county['fee']['summary'] }}
                            @if (! empty($county['fee']['url']))
                            <a href="{{ $county['fee']['url'] }}" rel="noopener" target="_blank" class="mt-1 block text-xs font-medium text-zinc-700 underline">Fee schedule</a>
                            @endif
                            @else
                            <span class="text-zinc-500">Fee not published online; ask the office.</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="mt-6 text-sm text-zinc-500">
            @if ($page->researchedOn()) Collected from each office's website on {{ $page->researchedOn() }}. @endif
            Fees and filing methods change. Confirm them with the office before you {{ $verb }}.
        </p>
    </div>
</section>
@endif

@if ($page->depth && $page->recentChanges())
@php $enacted = array_filter($page->recentChanges(), fn (array $change) => ! empty($change['effective'])); @endphp
{{-- Recent law changes (researched states only) --}}
<section id="recent-changes" class="border-y border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Recent changes to {{ $name }} lien law</h2>
        <p class="mt-4 text-zinc-600">
            @if ($enacted)
            Changes since 2022 that affect lien deadlines, notices, forms or filing.
            @if (count($enacted) < count($page->recentChanges())) Items marked pending are not law yet. @endif
            @else
            We found no enacted change to the {{ $name }} lien statute since 2022. The items below are pending and are not law yet.
            @endif
        </p>
        <div class="mt-10 space-y-6">
            @foreach ($page->recentChanges() as $change)
            <article class="rounded-2xl border border-zinc-200 bg-white p-6">
                <h3 class="font-semibold text-zinc-900">{{ $change['title'] }}</h3>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wider text-zinc-500">
                    {{ ! empty($change['effective']) ? 'Effective '.\Illuminate\Support\Carbon::parse($change['effective'])->format('F j, Y') : 'Pending, not law' }}
                </p>
                <p class="mt-3 text-sm leading-relaxed text-zinc-600">{{ $change['summary'] }}</p>
                @if (! empty($change['cite']))
                <p class="mt-2 text-xs text-zinc-500">{{ $change['cite'] }}</p>
                @endif
            </article>
            @endforeach
        </div>
        @if (! empty($page->depth['sources']))
        <details class="mt-10 rounded-2xl border border-zinc-200 bg-white p-6">
            <summary class="cursor-pointer font-semibold text-zinc-900">Sources for this page ({{ count($page->depth['sources']) }})</summary>
            <ul class="mt-4 space-y-2 text-sm">
                @foreach ($page->depth['sources'] as $source)
                <li><a href="{{ $source['url'] }}" rel="noopener nofollow" target="_blank" class="text-zinc-700 underline hover:text-zinc-900">{{ $source['title'] }}</a></li>
                @endforeach
            </ul>
        </details>
        @endif
    </div>
</section>
@endif

{{-- Pricing / CTA --}}
<section class="bg-white py-20">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        @if ($referral)
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">Filing {{ $a }} {{ $name }} mechanics lien</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            {{ $name }} liens are filed through the courts by an attorney, so we do not sell a self-serve {{ $name }} filing.
            Tell us about the project and we connect you with {{ $a }} {{ $name }} attorney who handles construction lien filings.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Request {{ $a }} {{ $name }} lien attorney</a>
            <a href="{{ route('liens.preliminary-notice') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">Send a preliminary notice instead</a>
        </div>
        @else
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900">File your {{ $name }} mechanics lien</h2>
        <p class="mx-auto mt-4 max-w-2xl text-zinc-600">
            We calculate the {{ $name }} deadlines from your project dates, prepare the lien on the correct form, and handle recording.
            Self-serve from ${{ $page->selfServePrice() }}, or full service with recording and notices handled for you from ${{ $page->fullServicePrice() }}.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#DC2626] px-8 py-4 text-base font-semibold text-white shadow-lg transition hover:bg-[#B91C1C]">Start {{ $a }} {{ $name }} lien</a>
            <a href="{{ route('liens.pricing') }}" class="inline-flex items-center gap-2 rounded-lg border border-zinc-300 px-8 py-4 text-base font-semibold text-zinc-800 transition hover:bg-zinc-50">See lien pricing</a>
        </div>
        @endif
    </div>
</section>

{{-- FAQ --}}
<section class="border-t border-zinc-200 bg-zinc-50 py-20">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight text-zinc-900">{{ $name }} mechanics lien FAQ</h2>
        <x-seo.faq class="mt-10" :items="$page->faq()" />
    </div>
</section>

{{-- Related --}}
<section class="bg-white py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">More {{ $name }} lien tools</h2>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('liens.lien-waivers.state', ['state' => strtolower($page->code)]) }}" class="text-zinc-700 underline hover:text-zinc-900">Free {{ $name }} lien waiver forms</a></li>
                    @if (\App\Domains\Lien\Seo\NoticeOfIntentStatePage::has($page->code))
                    <li><a href="{{ route('liens.notice-of-intent-to-lien.state', ['state' => $page->slug]) }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $name }} notice of intent to lien</a></li>
                    @endif
                    @if (\App\Domains\Lien\Seo\LienReleaseStatePage::has($page->code))
                    <li><a href="{{ route('liens.lien-release.state', ['state' => $page->slug]) }}" class="text-zinc-700 underline hover:text-zinc-900">{{ $name }} mechanics lien release</a></li>
                    @endif
                    @if ($blankClaim)
                    <li><a href="{{ $blankClaim['url'] }}" class="text-zinc-700 underline hover:text-zinc-900" download>Download a blank {{ $name }} lien claim form</a></li>
                    @endif
                    <li><a href="{{ route('liens.deadline-calculator') }}" class="text-zinc-700 underline hover:text-zinc-900">Mechanics lien deadline calculator</a></li>
                    <li><a href="{{ route('liens.preliminary-notice') }}" class="text-zinc-700 underline hover:text-zinc-900">Preliminary notice service</a></li>
                    <li><a href="{{ route('liens.notice-of-intent-to-lien') }}" class="text-zinc-700 underline hover:text-zinc-900">Notice of intent to lien</a></li>
                    <li><a href="{{ route('liens.payment-demand-letter') }}" class="text-zinc-700 underline hover:text-zinc-900">Payment demand letter</a></li>
                    <li><a href="{{ route('liens.lien-release') }}" class="text-zinc-700 underline hover:text-zinc-900">Lien release</a></li>
                    <li><a href="{{ route('liens') }}" class="text-zinc-700 underline hover:text-zinc-900">Mechanics lien rules for every state</a></li>
                </ul>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-zinc-900">Bordering states</h2>
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($nearbyStates as $nearbyCode => $nearbyName)
                    <li>
                        <a href="{{ route('liens.state', ['state' => \App\Support\Seo\States::slug($nearbyName)]) }}" class="inline-flex rounded-full border border-zinc-200 px-4 py-2 text-sm font-medium text-zinc-700 transition hover:border-zinc-300 hover:bg-zinc-50">
                            {{ $nearbyName }} mechanics lien
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <p class="mt-10 text-xs text-zinc-500">
            This page summarizes {{ $name }} lien law for general information and is not legal advice. Deadlines depend on your role, project type, and dates; confirm them against the statute or with counsel before relying on them.
        </p>
    </div>
</section>
@endsection
