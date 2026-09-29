<!DOCTYPE html>
{{--
    Notice-letter shell: preliminary notices, notices to owner, notices of
    intent and the other served (not recorded) documents render through
    this page. The state body partial ($doc['form']['body']) supplies the
    notice text; the shell supplies the letter around it: the claimant's
    letterhead block and the date, the delivery line, the parties served
    (form.service.recipients), the Re line, the boxed statutory notice where
    the state puts it first, the title and statute, the signature block and
    page numbers.

    Body-partial contract: the same $doc as the instrument shell (see
    LienDocumentPayload and instruments/shell.blade.php). Letters keep 1in
    margins, DejaVu Serif 11pt, nothing under 10pt; the title and any
    .bold-statement are the largest type on the page (12pt) because
    Arizona wants two paragraphs "in type at least as large as the largest
    type otherwise on the document". Missing values print as ruled blanks,
    never "N/A". DOMPDF: plain HTML + inline CSS, no flex/grid.
--}}
@php
    $form = $doc['form'];
    $claimant = $doc['parties']['claimant'];
    $claimantName = $claimant['display_name'] ?? 'Claimant';
    $project = $doc['project'];

    // Everyone the state has this notice served on, without duplicates (the
    // hiring party is often the owner or the general contractor).
    $served = [];
    foreach ($form['service']['recipients'] ?? ['owner'] as $role) {
        $party = $role === 'customer' ? $doc['parties']['hiring'] : ($doc['parties'][$role] ?? null);
        $key = $party['display_name'] ?? null;

        if ($party === null || $key === null || isset($served[$key])) {
            continue;
        }

        $served[$key] = ['role' => $role, 'party' => $party];
    }

    $roleLabel = fn (string $role) => match ($role) {
        'owner' => 'Owner or reputed owner',
        'gc' => 'Original (direct) contractor',
        'lender' => 'Construction lender',
        'customer' => 'Person who contracted with the claimant',
        'subcontractor' => 'Subcontractor',
        default => ucfirst(str_replace('_', ' ', $role)),
    };

    $delivery = \App\Domains\Lien\Documents\LienDocumentPayload::deliveryLabel($form['service']['method'] ?? 'certified_mail') ?? 'certified mail';
@endphp
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $form['title'] }} - {{ $claimantName }}</title>
    <meta name="author" content="eRegister">
    <meta name="keywords" content="{{ $form['title'] }} ({{ $form['state'] }}); template v{{ $form['template_version'] }}; generated {{ $doc['generated_at'] }}; filing {{ $doc['filing']['public_id'] }}">
    <style>
        @page { margin: 1in; }
        @include('documents.lien._parts.base-styles')

        .letterhead { width: 100%; border-collapse: collapse; margin: 0 0 10pt 0; }
        .letterhead td { vertical-align: top; padding: 0; }
        .sender { font-size: 10.5pt; line-height: 1.3; }
        .sender .name { font-weight: bold; font-size: 11.5pt; }
        .date { text-align: right; }
        .delivery { font-size: 10pt; text-transform: uppercase; letter-spacing: 0.3pt; margin: 0 0 10pt 0; }
        .to { width: 100%; border-collapse: collapse; margin: 0 0 8pt 0; }
        .to td { vertical-align: top; padding: 0 0 6pt 0; line-height: 1.3; }
        .to td.k { width: 2.2in; font-size: 10pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3pt; padding-right: 8pt; }
        .re { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 4pt 0; margin: 0 0 12pt 0; font-size: 10.5pt; }
        .two-col { border-collapse: collapse; width: 100%; margin: 0 0 10pt 0; }
        .two-col td { vertical-align: top; padding: 0 0 8pt 0; width: 50%; }
        .two-col td.left { padding-right: 12pt; }
        .receipt { border-top: 1px solid #000; margin-top: 16pt; padding-top: 8pt; page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="page-number">{{ $form['title'] }} - Page <span class="n"></span></div>

    <table class="letterhead">
        <tr>
            <td class="sender">
                <div class="name">{{ $claimantName }}</div>
                @foreach (($claimant['address_lines'] ?? []) as $line)
                    <div>{{ $line }}</div>
                @endforeach
                @if ($claimant['phone'] ?? null)
                    <div>{{ $claimant['phone'] }}</div>
                @endif
                @if ($claimant['email'] ?? null)
                    <div>{{ $claimant['email'] }}</div>
                @endif
            </td>
            <td class="date">{{ $doc['date'] }}</td>
        </tr>
    </table>

    <p class="delivery">Via {{ $delivery }}</p>

    <table class="to">
        @forelse ($served as $entry)
            <tr>
                <td class="k">To: {{ $roleLabel($entry['role']) }}</td>
                <td>@include('documents.lien._parts.party', ['party' => $entry['party']])</td>
            </tr>
        @empty
            <tr>
                <td class="k">To: Owner or reputed owner</td>
                <td>@include('documents.lien._parts.party', ['party' => null])</td>
            </tr>
        @endforelse
    </table>

    <div class="re">
        <strong>Re:</strong> {{ $form['title'] }}@if ($project['name']) &middot; {{ $project['name'] }}@endif
        @if ($project['address']['single_line']) &middot; {{ $project['address']['single_line'] }}@endif
        @if ($project['county']) ({{ $project['county'] }} County, {{ $form['state_name'] }})@endif
        @if ($project['apn']) &middot; {{ $project['parcel_label'] }} {{ $project['apn'] }}@endif
    </div>

    @if (! empty($form['clauses']['notice_box']))
        @include('documents.lien._parts.clauses', ['only' => 'notice_box'])
    @endif

    <p class="doc-title">{{ mb_strtoupper($form['title']) }}</p>
    @if (! empty($form['statute']))
        <p class="doc-statute">{{ $form['statute'] }}</p>
    @endif

    @if (! empty($form['clauses']['bold_statement']))
        <p class="bold-statement">{{ $form['clauses']['bold_statement'] }}</p>
    @endif

    @include($form['body'])

    @include('documents.lien._parts.execution')

    @if (! empty($form['clauses']['after_execution']))
        @include('documents.lien._parts.clauses', ['only' => 'after_execution'])
    @endif
</body>
</html>
