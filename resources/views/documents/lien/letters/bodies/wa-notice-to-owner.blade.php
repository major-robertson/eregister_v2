{{--
    Washington Notice to Owner, RCW 60.04.031(4): the notice "shall include
    but not be limited to the following information and shall substantially
    be in the following form, using lower-case and upper-case ten-point type
    where appropriate". The form's text is verbatim from app.leg.wa.gov,
    verified 2026-09-30; the 2026 archive notices paraphrased it, so never
    take wording from them. The shell prints the form's heading (NOTICE TO
    OWNER), the addressees and the signature. This body prints the front of
    the form with its To, Date, Re, From, AT THE REQUEST OF, Sender,
    Address, Telephone and description lines filled in, then the facts the
    form does not ask for (first furnishing date, estimated price), as "not
    be limited to" allows, then the form's reverse side (IMPORTANT
    INFORMATION FOR YOUR PROTECTION) kept together, so it starts a new page
    whenever it does not fit whole; the signature follows it. The form
    needs no oath, declaration or notary. Never edit the wording without
    re-checking the statute.
--}}
@php
    $form = $doc['form'];
    $sections = $form['sections'];
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    $showFirst = $sections['first_furnish'] ?? true;
    $showEstimate = ($sections['amount'] ?? 'estimate') === 'estimate';

    // "Re: (description of property: Street address or general location.)"; a bare state is no location.
    $location = ($project['address']['line1'] || $project['address']['city'])
        ? $project['address']['single_line'].($project['county'] ? ' ('.$project['county'].' County, '.$form['state_name'].')' : '')
        : $project['legal_description'];
@endphp
<p class="center" style="margin-bottom: 2pt;"><strong>IMPORTANT: READ BOTH SIDES OF THIS NOTICE CAREFULLY.</strong></p>
<p class="center"><strong>PROTECT YOURSELF FROM PAYING TWICE</strong></p>

<table style="width: 100%; border-collapse: collapse; margin: 0 0 4pt 0;">
    <tr>
        <td style="width: 62%; vertical-align: top; padding: 0;">To: {!! $blank($parties['owner']['display_name'] ?? null, 'fill fill-wide') !!}</td>
        <td style="vertical-align: top; padding: 0;">Date: {{ $doc['date'] }}</td>
    </tr>
</table>
<div class="block" style="margin: 0 0 8pt 0;">
    <div>Re: {!! $blank($location, 'fill fill-wide') !!}</div>
    <div>From: {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}</div>
    <div>AT THE REQUEST OF: {!! $blank($parties['hiring']['display_name'] ?? null, 'fill fill-wide') !!}</div>
</div>

<p>THIS IS NOT A LIEN: This notice is sent to you to tell you who is providing professional services, materials, or equipment for the improvement of your property and to advise you of the rights of these persons and your responsibilities. Also take note that laborers on your project may claim a lien without sending you a notice.</p>

<div class="keep">
    <p class="center" style="margin-bottom: 4pt;"><strong>OWNER/OCCUPIER OF EXISTING<br>RESIDENTIAL PROPERTY</strong></p>
    <p>Under Washington law, those who furnish labor, professional services, materials, or equipment for the repair, remodel, or alteration of your owner-occupied principal residence and who are not paid, have a right to enforce their claim for payment against your property. This claim is known as a construction lien.</p>
    <p>The law limits the amount that a lien claimant can claim against your property. Claims may only be made against that portion of the contract price you have not yet paid to your prime contractor as of the time this notice was given to you or three days after this notice was mailed to you. Review the back of this notice for more information and ways to avoid lien claims.</p>
</div>

<div class="keep">
    <p class="center" style="margin-bottom: 4pt;"><strong>COMMERCIAL AND/OR NEW<br>RESIDENTIAL PROPERTY</strong></p>
    <p>We have or will be providing professional services, materials, or equipment for the improvement of your commercial or new residential project. In the event you or your contractor fail to pay us, we may file a lien against your property. A lien may be claimed for all professional services, materials, or equipment furnished after a date that is sixty days before this notice was given to you or mailed to you, unless the improvement to your property is the construction of a new single-family residence, then ten days before this notice was given to you or mailed to you.</p>
</div>

<div class="block indent">
    <div>Sender: {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}</div>
    <div>Address: {!! $blank($claimant['address_line'] ?? null, 'fill fill-wide') !!}</div>
    <div>Telephone: {!! $blank($claimant['phone'] ?? null) !!}</div>
</div>
<p>Brief description of professional services, materials, or equipment provided or to be provided: {!! $blank($filing['description_of_work'], 'fill fill-wide') !!}</p>

<p class="center"><strong>IMPORTANT INFORMATION<br>ON REVERSE SIDE</strong></p>

@if ($showFirst || $showEstimate)
    <table class="fields" style="margin-top: 6pt;">
        @if ($showFirst)
            <tr>
                <td class="k">First furnished</td>
                <td>{!! $blank($project['dates']['first_furnish']) !!}</td>
            </tr>
        @endif
        @if ($showEstimate)
            <tr>
                <td class="k">Estimated total price</td>
                <td>${!! $blank($filing['estimate'], 'fill fill-mid') !!}</td>
            </tr>
        @endif
    </table>
@endif

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<div class="keep" style="margin-top: 10pt;">
    <p class="center" style="margin-bottom: 6pt;"><strong>IMPORTANT INFORMATION<br>FOR YOUR PROTECTION</strong></p>
    <p style="margin-bottom: 4pt;">This notice is sent to inform you that we have or will provide professional services, materials, or equipment for the improvement of your property. We expect to be paid by the person who ordered our services, but if we are not paid, we have the right to enforce our claim by filing a construction lien against your property.</p>
    <p style="margin-bottom: 4pt;">LEARN more about the lien laws and the meaning of this notice by discussing them with your contractor, suppliers, Department of Labor and Industries, the firm sending you this notice, your lender, or your attorney.</p>
    <p style="margin-bottom: 4pt;">COMMON METHODS TO AVOID CONSTRUCTION LIENS: There are several methods available to protect your property from construction liens. The following are two of the more commonly used methods.</p>
    <p style="margin: 0 0.5in 4pt 0.5in;">DUAL PAYCHECKS (Joint Checks): When paying your contractor for services or materials, you may make checks payable jointly to the contractor and the firms furnishing you this notice.</p>
    <p style="margin: 0 0.5in 4pt 0.5in;">LIEN RELEASES: You may require your contractor to provide lien releases signed by all the suppliers and subcontractors from whom you have received this notice. If they cannot obtain lien releases because you have not paid them, you may use the dual payee check method to protect yourself.</p>
    <p style="margin-bottom: 4pt;">YOU SHOULD TAKE APPROPRIATE STEPS TO PROTECT YOUR PROPERTY FROM LIENS.</p>
    <p style="margin-bottom: 4pt;">YOUR PRIME CONTRACTOR AND YOUR CONSTRUCTION LENDER ARE REQUIRED BY LAW TO GIVE YOU WRITTEN INFORMATION ABOUT LIEN CLAIMS. IF YOU HAVE NOT RECEIVED IT, ASK THEM FOR IT.</p>
</div>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
