<!DOCTYPE html>
{{--
    Cover letter that travels with the served copy, one per recipient. Two
    variants: the notice-of-recording letter once the instrument has a
    recording date (recording date, reference, county), and the plain
    enclosure letter for notices and unrecorded copies. Sent by the
    preparer (eRegister) on the claimant's behalf; the recipient's address is
    the snapshot that was mailed.

    Vars: $doc (LienDocumentPayload), $recipient (one of $doc['recipients'])
--}}
@php
    $form = $doc['form'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $preparer = $doc['preparer'];
    $claimant = $doc['parties']['claimant'];
    $recorded = $form['family'] === 'instrument' && $filing['recording']['recorded_at'] !== null;
    $delivery = $recipient['delivery_method_label'] ?? \App\Domains\Lien\Documents\LienDocumentPayload::deliveryLabel($form['service']['method'] ?? 'certified_mail') ?? 'certified mail';
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    $roleLabel = match ($recipient['role']) {
        'owner' => 'the owner of the property',
        'gc' => 'the original contractor',
        'lender' => 'the construction lender',
        'customer' => 'the party that contracted with the claimant',
        'subcontractor' => 'a subcontractor on the project',
        default => 'an interested party',
    };
@endphp
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Cover Letter - {{ $form['title'] }} - {{ $recipient['display_name'] }}</title>
    <meta name="author" content="eRegister">
    <meta name="keywords" content="Cover letter; {{ $form['title'] }} ({{ $form['state'] }}); generated {{ $doc['generated_at'] }}; filing {{ $filing['public_id'] }}">
    <style>
        @page { margin: 1in; }
        @include('documents.lien._parts.base-styles')
        .letterhead { width: 100%; border-collapse: collapse; margin: 0 0 14pt 0; }
        .letterhead td { vertical-align: top; padding: 0; }
        .sender { font-size: 10.5pt; line-height: 1.3; }
        .sender .name { font-weight: bold; font-size: 11.5pt; }
        .date { text-align: right; }
        .delivery { font-size: 10pt; text-transform: uppercase; letter-spacing: 0.3pt; margin: 0 0 10pt 0; }
        .to { margin: 0 0 12pt 0; line-height: 1.3; }
        .re { margin: 0 0 12pt 0; }
        .signature { margin-top: 18pt; line-height: 1.3; }
    </style>
</head>
<body>
    <table class="letterhead">
        <tr>
            <td class="sender">
                <div class="name">{{ $preparer['name'] }}</div>
                @foreach ($preparer['address_lines'] as $line)
                    <div>{{ $line }}</div>
                @endforeach
                @if ($preparer['phone'])<div>{{ $preparer['phone'] }}</div>@endif
                @if ($preparer['email'])<div>{{ $preparer['email'] }}</div>@endif
            </td>
            <td class="date">{{ $doc['date'] }}</td>
        </tr>
    </table>

    <p class="delivery">Via {{ $delivery }}</p>

    <div class="to">
        <div>{!! $blank($recipient['display_name'], 'fill fill-wide') !!}</div>
        @if ($recipient['company'] && $recipient['name'] && $recipient['name'] !== $recipient['company'])
            <div>Attn: {{ $recipient['name'] }}</div>
        @endif
        @foreach ($recipient['address_lines'] as $line)
            <div>{{ $line }}</div>
        @endforeach
    </div>

    <p class="re">
        <strong>Re:</strong>
        @if ($recorded)Notice of recording: {{ $form['title'] }}@else{{ $form['title'] }}@endif
        @if ($project['name']) &middot; {{ $project['name'] }}@endif
        @if ($project['address']['single_line']) &middot; {{ $project['address']['single_line'] }}@endif
        @if ($project['county']) ({{ $project['county'] }} County, {{ $form['state_name'] }})@endif
        <br><strong>Claimant:</strong> {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}
    </p>

    <p>Dear {!! $blank($recipient['display_name'], 'fill fill-wide') !!}:</p>

    @if ($recorded)
        <p>
            Enclosed is a true and correct copy of the {{ $form['title'] }} recorded on {{ $filing['recording']['recorded_at'] }} in the official records of {!! $blank($project['county'], 'fill fill-mid') !!} County, {{ $form['state_name'] }}@if ($filing['recording']['reference']), as {{ $filing['recording']['reference'] }}@endif, against the property described above.
        </p>
    @else
        <p>
            Enclosed is a true and correct copy of the {{ $form['title'] }} concerning the property described above, served on you on behalf of {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}.
        </p>
    @endif

    <p>You are receiving this copy as {{ $roleLabel }}@if (! empty($form['statute'])), as provided by {{ $form['statute'] }}@endif. Please keep it with your records for this project.</p>

    <p>Questions about the enclosed document should be directed to the claimant, {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}{{ ($claimant['address_line'] ?? null) ? ', '.$claimant['address_line'] : '' }}{{ ($claimant['phone'] ?? null) ? ', '.$claimant['phone'] : '' }}. This office prepared and mailed the document at the claimant's request and does not give legal advice.</p>

    <p>Sincerely,</p>

    <div class="signature">
        <div>{{ $preparer['name'] }}</div>
        <div>for {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}</div>
    </div>

    <p class="small" style="margin-top: 16pt;">Enclosure: {{ $form['title'] }}</p>
</body>
</html>
