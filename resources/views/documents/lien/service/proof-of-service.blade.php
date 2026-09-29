<!DOCTYPE html>
{{--
    Proof of service for one recipient: a declaration under penalty of
    perjury (the default), or a sworn affidavit with a notary jurat where the
    state wants one (form.service.proof 'affidavit'). Prints the recipient's
    address snapshot (what was actually mailed), the method, the tracking
    number and the sent date, and declares under the law of the state the
    state file names (California) or the state where staff sign it.

    Vars: $doc (LienDocumentPayload), $recipient (one of $doc['recipients']),
          $affidavit (bool), $perjuryState (state name)
--}}
@php
    $form = $doc['form'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $preparer = $doc['preparer'];
    $methods = [
        'certified_mail' => 'Certified mail, return receipt requested',
        'registered_mail' => 'Registered mail',
        'first_class_mail' => 'First-class mail with a certificate of mailing',
        'personal_delivery' => 'Personal delivery',
        'overnight_delivery' => 'Overnight delivery service',
    ];
    $selected = $recipient['delivery_method'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $affidavit ? 'Affidavit' : 'Proof' }} of Service - {{ $form['title'] }} - {{ $recipient['display_name'] }}</title>
    <meta name="author" content="eRegister">
    <meta name="keywords" content="Proof of service; {{ $form['title'] }} ({{ $form['state'] }}); generated {{ $doc['generated_at'] }}; filing {{ $filing['public_id'] }}">
    <style>
        @page { margin: 1in; }
        @include('documents.lien._parts.base-styles')
        .attachments div { margin-bottom: 3pt; }
    </style>
</head>
<body>
    <div class="page-number">{{ $affidavit ? 'Affidavit' : 'Proof' }} of Service - Page <span class="n"></span></div>

    <p class="doc-title">{{ $affidavit ? 'AFFIDAVIT OF SERVICE' : 'PROOF OF SERVICE' }}</p>
    <p class="doc-statute">
        {{ $form['title'] }}@if ($project['name']) &middot; {{ $project['name'] }}@endif
        @if ($project['address']['single_line']) &middot; {{ $project['address']['single_line'] }}@endif
        @if ($project['county']) ({{ $project['county'] }} County, {{ $form['state_name'] }})@endif
    </p>

    @if ($affidavit)
        <div class="venue-lines">STATE OF <span class="fill fill-mid">&nbsp;</span><br>COUNTY OF <span class="fill fill-mid">&nbsp;</span></div>
        <p><span class="fill fill-wide">&nbsp;</span>, being first duly sworn, deposes and says:</p>
        <p>I am over the age of eighteen years and not a party to this matter. My business address is {!! $blank(implode(', ', array_filter(array_merge([$preparer['name']], $preparer['address_lines']))) ?: null, 'fill fill-wide') !!}.</p>
    @else
        <p>I, <span class="fill fill-wide">&nbsp;</span>, declare that I am over the age of eighteen years and not a party to this matter, and that my business address is {!! $blank(implode(', ', array_filter(array_merge([$preparer['name']], $preparer['address_lines']))) ?: null, 'fill fill-wide') !!}.</p>
    @endif

    <p>
        On {!! $blank($recipient['sent_at'], 'fill fill-mid') !!}, I served a true and correct copy of the {{ $form['title'] }}@if ($filing['recording']['recorded_at']) recorded {{ $filing['recording']['recorded_at'] }}@if ($filing['recording']['reference']) as {{ $filing['recording']['reference'] }}@endif @endif
        on the person named below, at the address shown, by the method marked:
    </p>

    <table class="fields">
        <tr>
            <td class="k">Served on</td>
            <td>
                <div class="block">
                    <div>{!! $blank($recipient['display_name'], 'fill fill-wide') !!}@if ($recipient['role_label']) ({{ $recipient['role_label'] }})@endif</div>
                    @if ($recipient['company'] && $recipient['name'] && $recipient['name'] !== $recipient['company'])
                        <div>Attn: {{ $recipient['name'] }}</div>
                    @endif
                    @forelse ($recipient['address_lines'] as $line)
                        <div>{{ $line }}</div>
                    @empty
                        <div><span class="fill fill-wide">&nbsp;</span></div>
                    @endforelse
                </div>
            </td>
        </tr>
        <tr>
            <td class="k">Method of service</td>
            <td class="attachments">
                @foreach ($methods as $key => $label)
                    <div><span class="box">{{ $selected === $key ? 'X' : '' }}</span> {{ $label }}</div>
                @endforeach
                @if ($selected !== null && ! isset($methods[$selected]))
                    <div><span class="box">X</span> {{ ucfirst($recipient['delivery_method_label']) }}</div>
                @endif
            </td>
        </tr>
        <tr>
            <td class="k">Tracking or article number</td>
            <td>{!! $blank($recipient['tracking_number'], 'fill fill-wide') !!}</td>
        </tr>
        <tr>
            <td class="k">Attached</td>
            <td class="attachments">
                <div><span class="box"></span> USPS receipt or certificate of mailing</div>
                <div><span class="box"></span> Return receipt (PS Form 3811) or electronic return receipt</div>
                <div><span class="box"></span> Delivery confirmation</div>
            </td>
        </tr>
    </table>

    <p>I declare under penalty of perjury under the laws of the State of {{ $perjuryState }} that the foregoing is true and correct.</p>

    <p>Executed on <span class="fill fill-mid">&nbsp;</span>, at <span class="fill fill-wide">&nbsp;</span>.</p>

    <table class="sig-table">
        <tr>
            <td style="width: 58%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Signature of person making service</div></td>
            <td style="width: 42%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Printed name</div></td>
        </tr>
    </table>

    @if ($affidavit)
        <div class="notary">
            <p>Subscribed and sworn to (or affirmed) before me on this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, 20<span class="fill fill-short">&nbsp;</span>, by <span class="fill fill-wide">&nbsp;</span>, who is personally known to me or who produced <span class="fill fill-mid">&nbsp;</span> as identification.</p>
            @include('documents.lien._parts.notary-lines')
        </div>
    @endif
</body>
</html>
