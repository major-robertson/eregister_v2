{{--
    North Carolina Notice to Lien Agent, N.C. Gen. Stat. § 44A-11.2(i): "shall
    be legible, shall include the following information … and shall be
    substantially as follows", items (1) to (4) verbatim, verified against
    ncleg.gov on 2026-09-29. It goes to the lien agent designated on the
    Appointment of Lien Agent (LiensNC), not to the clerk; the owner copy is
    a courtesy. Never edit the wording without re-checking the statute.
--}}
@php
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
@endphp
<p>Lien agent (as designated on the Appointment of Lien Agent or the building permit): <span class="fill fill-wide">&nbsp;</span></p>

<table class="item">
    <tr>
        <td class="n">(1)</td>
        <td>
            Potential lien claimant's name, mailing address, telephone number, fax number (if available), and email address (if available):
            @include('documents.lien._parts.party', ['party' => $claimant, 'contact' => true])
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(2)</td>
        <td>
            Name of the party with whom the potential lien claimant has contracted to improve the real property described below:
            @include('documents.lien._parts.party', ['party' => $parties['hiring']])
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(3)</td>
        <td>
            A description of the real property sufficient to identify the real property, such as the name of the project, if applicable, the physical address as shown on the building permit or notice received from the owner:
            @if ($project['name'])<div>{{ $project['name'] }}</div>@endif
            @include('documents.lien._parts.property')
        </td>
    </tr>
</table>

<table class="item">
    <tr>
        <td class="n">(4)</td>
        <td>I give notice of my right subsequently to pursue a claim of lien for improvements to the real property described in this notice.</td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
