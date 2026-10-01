{{--
    Oregon Notice of Right to a Lien, ORS 87.023: the notice "shall include,
    but not be limited to, the following information and shall be
    substantially in the following form". This is the front of the form,
    verbatim from oregonlegislature.gov (ORS 2025 edition), verified
    2026-09-30: the warning (WARNING underlined as in the published form), the
    To / Date of mailing lines (the form's "Owner" and "Owner's address"
    captions in parentheses), the paragraphs with the claimant, the work, the
    person who ordered it and the property filled in, and the sender's name,
    address and telephone. The shell prints the form's heading (the title,
    NOTICE OF RIGHT TO A LIEN) above this and the signature below it; the
    form's "IMPORTANT INFORMATION ON REVERSE SIDE" line and the reverse side
    follow the signature (letters/clauses/or-notice-of-right-to-lien-reverse).
    ORS 87.023 sets no type size; the form's capitals and underlines are kept,
    and the bold on the warning is house emphasis. Never edit the wording
    without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $hiring = $parties['hiring'];
    // Without a street line the single-line address is only the state; print a blank instead.
    $location = $project['address']['line1'] ? $project['address']['single_line'] : null;
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<div class="center" style="font-weight: bold; margin: 0 0 10pt 0;">
    <div><u>WARNING</u>: READ THIS NOTICE.</div>
    <div>PROTECT YOURSELF FROM PAYING ANY CONTRACTOR</div>
    <div>OR SUPPLIER TWICE FOR THE SAME SERVICE.</div>
</div>

<table style="width: 100%; border-collapse: collapse; margin: 0 0 8pt 0;">
    <tr>
        <td style="width: 60%; vertical-align: top; padding: 0 12pt 0 0;">To: {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!} (Owner)</td>
        <td style="width: 40%; vertical-align: top; padding: 0;">Date of mailing: {!! $blank($filing['mailed_at'], 'fill fill-mid') !!}</td>
    </tr>
    <tr>
        <td colspan="2" style="vertical-align: top; padding: 2pt 0 0 0;">{!! $blank($owner['address_line'] ?? null, 'fill fill-wide') !!} (Owner's address)</td>
    </tr>
</table>

<p>This is to inform you that {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!} has begun to provide {!! $blank($filing['description_of_work'], 'fill fill-wide') !!} (description of materials, equipment, labor or services) ordered by {!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!} for improvements to property you own. The property is located at {!! $blank($location, 'fill fill-wide') !!}.</p>

<p>A lien may be claimed for all materials, equipment, labor and services furnished after a date that is eight days, not including Saturdays, Sundays and other holidays, as defined in ORS 187.010, before this notice was mailed to you.</p>

<p>Even if you or your mortgage lender have made full payment to the contractor who ordered these materials or services, your property may still be subject to a lien unless the supplier providing this notice is paid.</p>

<p>THIS IS NOT A LIEN. It is a notice sent to you for your protection in compliance with the construction lien laws of the State of Oregon.</p>

<p style="margin-bottom: 2pt;">This notice has been sent to you by:</p>
<div class="block" style="margin: 0 0 8pt 0;">
    <div>NAME: {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}</div>
    <div>ADDRESS: {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}</div>
    <div>TELEPHONE: {!! $blank($claimant['phone'] ?? null, 'fill fill-mid') !!}</div>
</div>

<p>IF YOU HAVE ANY QUESTIONS ABOUT THIS NOTICE, FEEL FREE TO CALL US.</p>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
