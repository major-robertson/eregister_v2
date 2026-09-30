{{--
    Execution block: the sworn or verified statement (unless the body is
    itself the sworn statement), the claimant's signature with printed name
    and title under it (recorders reject signatures without printed names),
    optional witness lines, and the notary certificate.

    Certificate wording by execution.notary_variant:
      fl  Fla. Stat. § 117.05(13)(a) jurat / (13)(c) representative acknowledgment
          ("by means of physical presence or online notarization")
      ca  Cal. Civ. Code § 1189(a)(3) acknowledgment / Gov. Code § 8202(b) jurat,
          each under the boxed § 1189(a)(1) notice
      nc  N.C.G.S. § 10B-41 acknowledgment / § 10B-43 jurat
      —   a generic certificate with blank venue lines (the client notarizes in
          their own county, never the property's)
    execution.notary_in_body (AL): the body or a clause prints the certificate, so notary is false here.
    Statutory certificates are verbatim; never edit them without re-checking
    the statute (verified 2026-09-29).
--}}
@php
    $form = $doc['form'];
    $execution = $form['execution'];
    $signer = $doc['signer'];
    $company = $doc['parties']['claimant']['display_name'] ?? null;
    $verification = $execution['verification'] ?? 'sworn';
    $notary = (bool) ($execution['notary'] ?? false);
    $notaryForm = $execution['notary_form'] ?? ($verification === 'acknowledged' ? 'acknowledgment' : 'jurat');
    $variant = $execution['notary_variant'] ?? null;
    $statement = $execution['statement'] ?? true;
    $title = $form['title'];
    $signerName = $signer['name'];
    $signerTitle = $signer['title'];
    $capacity = ($signerTitle ?: 'authorized representative').($company ? ' of '.$company : '');
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    // "Steven Roser, President of Roser Construction LLC" for the North Carolina acknowledgment.
    $signerAndCapacity = $blank($signerName, 'fill fill-wide').($signerTitle && $company ? ', '.e($signerTitle).' of '.e($company) : '');
@endphp
<div class="execution keep">
    @if ($statement && $verification === 'sworn')
        <p>The undersigned, being first duly sworn, states that he or she is the {{ $capacity }}, the claimant named above; that he or she is authorized to make this {{ $title }} on its behalf; that he or she has read it and knows its contents; and that the statements in it are true of his or her own knowledge.</p>
    @elseif ($statement && $verification === 'verified')
        <p>I, @if ($signerName){{ $signerName }}@else<span class="fill">&nbsp;</span>@endif, declare that I am the {{ $capacity }}, the claimant named above, and am authorized to make this verification on its behalf; that I have read the foregoing {{ $title }} and know its contents; and that the same is true of my own knowledge. I declare under penalty of perjury under the laws of the State of {{ $form['state_name'] }} that the foregoing is true and correct.</p>
        <p>Executed on <span class="fill fill-mid">&nbsp;</span>, at <span class="fill fill-wide">&nbsp;</span>.</p>
    @endif

    <table class="sig-table">
        <tr>
            <td style="width: 58%;">
                <div class="small">CLAIMANT:@if ($company) {{ $company }}@endif</div>
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">By (signature)</div>
            </td>
            <td style="width: 42%;">
                <div class="sig-line">&nbsp;</div>
                <div class="sig-caption">Date</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="sig-line">@if ($signerName){{ $signerName }}@else&nbsp;@endif</div>
                <div class="sig-caption">Printed name</div>
            </td>
            <td>
                <div class="sig-line">@if ($signerTitle){{ $signerTitle }}@else&nbsp;@endif</div>
                <div class="sig-caption">Title</div>
            </td>
        </tr>
    </table>

    @if (! empty($execution['witness']))
        <table class="sig-table">
            <tr>
                <td style="width: 58%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Witness (signature)</div></td>
                <td style="width: 42%;"><div class="sig-line">&nbsp;</div><div class="sig-caption">Witness (printed name)</div></td>
            </tr>
        </table>
    @endif

    @if ($notary)
        <div class="notary">
            @if ($variant === 'fl')
                <div class="venue-lines">STATE OF FLORIDA<br>COUNTY OF <span class="fill fill-mid">&nbsp;</span></div>
                @if ($notaryForm === 'acknowledgment')
                    <p>The foregoing instrument was acknowledged before me by means of <span class="box"></span> physical presence or <span class="box"></span> online notarization, this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, <span class="fill fill-short">&nbsp;</span>, by @if ($signerName){{ $signerName }}@else<span class="fill">&nbsp;</span>@endif as @if ($signerTitle){{ $signerTitle }}@else<span class="fill fill-mid">&nbsp;</span>@endif for @if ($company){{ $company }}@else<span class="fill">&nbsp;</span>@endif.</p>
                @else
                    <p>Sworn to (or affirmed) and subscribed before me by means of <span class="box"></span> physical presence or <span class="box"></span> online notarization, this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, <span class="fill fill-short">&nbsp;</span>, by @if ($signerName){{ $signerName }}@else<span class="fill">&nbsp;</span>@endif.</p>
                @endif
                @include('documents.lien._parts.notary-lines', [
                    'signatureCaption' => '(Signature of Notary Public - State of Florida)',
                    'nameCaption' => '(Print, Type, or Stamp Commissioned Name of Notary Public)',
                ])
                <p style="margin-top: 8pt;">Personally Known <span class="box"></span> OR Produced Identification <span class="box"></span><br>Type of Identification Produced <span class="fill fill-wide">&nbsp;</span></p>
            @elseif ($variant === 'ca')
                <div class="notice-box notary-disclaimer" style="font-weight: normal;">A notary public or other officer completing this certificate verifies only the identity of the individual who signed the document to which this certificate is attached, and not the truthfulness, accuracy, or validity of that document.</div>
                <div class="venue-lines">State of California<br>County of <span class="fill fill-mid">&nbsp;</span></div>
                @if ($notaryForm === 'acknowledgment')
                    <p>On <span class="fill fill-mid">&nbsp;</span> before me, <span class="fill fill-wide">&nbsp;</span> (here insert name and title of the officer), personally appeared @if ($signerName){{ $signerName }}@else<span class="fill fill-wide">&nbsp;</span>@endif, who proved to me on the basis of satisfactory evidence to be the person(s) whose name(s) is/are subscribed to the within instrument and acknowledged to me that he/she/they executed the same in his/her/their authorized capacity(ies), and that by his/her/their signature(s) on the instrument the person(s), or the entity upon behalf of which the person(s) acted, executed the instrument.</p>
                    <p>I certify under PENALTY OF PERJURY under the laws of the State of California that the foregoing paragraph is true and correct.</p>
                    <p>WITNESS my hand and official seal.</p>
                @else
                    <p>Subscribed and sworn to (or affirmed) before me on this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, 20<span class="fill fill-short">&nbsp;</span>, by @if ($signerName){{ $signerName }}@else<span class="fill fill-wide">&nbsp;</span>@endif, proved to me on the basis of satisfactory evidence to be the person(s) who appeared before me.</p>
                @endif
                @include('documents.lien._parts.notary-lines', ['signatureCaption' => 'Signature (Seal)'])
            @elseif ($variant === 'nc')
                <div class="venue-lines"><span class="fill fill-mid">&nbsp;</span> County, North Carolina</div>
                @if ($notaryForm === 'acknowledgment')
                    <p>I certify that the following person(s) personally appeared before me this day, each acknowledging to me that he or she signed the foregoing document: {!! $signerAndCapacity !!}.</p>
                @else
                    <p>Sworn to and subscribed before me this day by @if ($signerName){{ $signerName }}@else<span class="fill fill-wide">&nbsp;</span>@endif.</p>
                @endif
                <p>Date: <span class="fill fill-mid">&nbsp;</span></p>
                @include('documents.lien._parts.notary-lines', [
                    'signatureCaption' => 'Official Signature of Notary',
                    'nameCaption' => "Notary's printed or typed name, Notary Public",
                    'sealCaption' => '(Official Seal)',
                ])
            @else
                <div class="venue-lines">STATE OF <span class="fill fill-mid">&nbsp;</span><br>COUNTY OF <span class="fill fill-mid">&nbsp;</span></div>
                @if ($notaryForm === 'acknowledgment')
                    <p>On this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, 20<span class="fill fill-short">&nbsp;</span>, before me, the undersigned notary public, personally appeared @if ($signerName){{ $signerName }}@else<span class="fill fill-wide">&nbsp;</span>@endif, {{ $capacity }}, personally known to me or proved to me on the basis of satisfactory evidence to be the person whose name is subscribed to the foregoing instrument, and acknowledged that he or she executed it in that capacity{{ $company ? ' on behalf of '.$company : '' }} for the purposes stated in it.</p>
                @else
                    <p>Subscribed and sworn to (or affirmed) before me on this <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, 20<span class="fill fill-short">&nbsp;</span>, by @if ($signerName){{ $signerName }}@else<span class="fill fill-wide">&nbsp;</span>@endif, {{ $capacity }}, who is personally known to me or who produced <span class="fill fill-mid">&nbsp;</span> as identification.</p>
                @endif
                @include('documents.lien._parts.notary-lines')
            @endif
        </div>
    @endif
</div>
