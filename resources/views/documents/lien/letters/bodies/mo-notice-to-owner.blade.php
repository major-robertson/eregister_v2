{{--
    Missouri Notice to Owner, RSMo § 429.012.1: every original contractor
    gives the person with whom the contract is made (or the owner, if there is
    no contract), before receiving any payment, "a written notice which shall
    include the following disclosure language in ten-point bold type". The
    NOTICE TO OWNER heading and paragraph below are verbatim from
    revisor.mo.gov (version effective 2017-01-01), verified 2026-09-30. Never
    edit them without re-checking the statute. Compliance is a condition
    precedent to the original contractor's lien (§ 429.012.2).

    The statute says ten-point, not "at least" ten-point, so the disclosure
    is set at exactly 10pt bold with an inline style rather than the shared
    .bold-statement class, which is 12pt. The lines above it are house
    wording that identify the contract.
--}}
@php
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $contracting = $parties['hiring'] ?? $parties['owner'];
@endphp
<p>The contractor named below gives you this notice under RSMo § 429.012 about its contract to improve the property described below.</p>

<table class="fields">
    <tr>
        <td class="k">Contractor</td>
        <td>@include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])</td>
    </tr>
    <tr>
        <td class="k">Person with whom the contract is made</td>
        <td>@include('documents.lien._parts.party', ['party' => $contracting])</td>
    </tr>
    <tr>
        <td class="k">Property</td>
        <td>@include('documents.lien._parts.property')</td>
    </tr>
    <tr>
        <td class="k">Work and materials under the contract</td>
        <td>@include('documents.lien._parts.work')</td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])

<div class="keep" style="margin: 6pt 0 10pt 0;">
    <p class="center" style="font-size: 10pt; font-weight: bold; margin: 0 0 6pt 0;">NOTICE TO OWNER</p>
    <p style="font-size: 10pt; font-weight: bold;">FAILURE OF THIS CONTRACTOR TO PAY THOSE PERSONS SUPPLYING MATERIAL OR SERVICES TO COMPLETE THIS CONTRACT CAN RESULT IN THE FILING OF A MECHANIC'S LIEN ON THE PROPERTY WHICH IS THE SUBJECT OF THIS CONTRACT PURSUANT TO CHAPTER 429, RSMO. TO AVOID THIS RESULT YOU MAY ASK THIS CONTRACTOR FOR "LIEN WAIVERS" FROM ALL PERSONS SUPPLYING MATERIAL OR SERVICES FOR THE WORK DESCRIBED IN THIS CONTRACT. FAILURE TO SECURE LIEN WAIVERS MAY RESULT IN YOUR PAYING FOR LABOR AND MATERIAL TWICE.</p>
</div>

@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
