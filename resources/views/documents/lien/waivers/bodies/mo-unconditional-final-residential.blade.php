{{--
    Missouri statutory form: UNCONDITIONAL FINAL LIEN WAIVER FOR RESIDENTIAL
    REAL PROPERTY, Mo. Rev. Stat. § 429.016.27. Valid "only if it is on a form
    that is substantially as follows" (substantial compliance). Operative
    sentence reproduced verbatim from revisor.mo.gov, with the statute's
    "(provide legal name and address of Claimant)" parenthetical replaced by
    the claimant's actual name and address, and the "(Provide legal
    description of the Property)" blank bound to the project's legal
    description.

    No notary or witness. Per the statute, the claimant's legal name and the
    signer's name, title or position, address, and telephone number are
    printed immediately below the signature (via _signature-lines plus the
    address/telephone rows), and the date is adjacent to the signature.
--}}
@php
    $claimantAddress = implode(', ', $waiver['claimant']['address_lines'] ?? []);
@endphp

<div class="waiver-title">Unconditional Final Lien Waiver<br>for Residential Real Property</div>
<div class="waiver-statute">Missouri: Mo. Rev. Stat. &sect; 429.016.27</div>

<div class="waiver-body">
    <p>
        Claimant, <span class="fill-wide">{{ $waiver['claimant']['company'] ?? '' }}</span>, whose address is
        <span class="fill-wide">{{ $claimantAddress }}</span>, hereby fully, finally, and unconditionally
        waives and releases any right to assert or enforce a mechanic's lien claim against the residential
        real property identified below for all work performed by Claimant prior to the date set forth below
        and for any work hereafter performed by or on behalf of Claimant under any agreements executed by
        Claimant prior to said date set forth below:
    </p>

    <p class="waiver-section-label">Legal description of the Property:</p>
    <table class="waiver-fields">
        <tr>
            <td class="label">Legal Description:</td>
            <td class="value">{{ $waiver['project']['legal_description'] ?? '' }}&nbsp;</td>
        </tr>
        <tr>
            <td class="label">Commonly Known As:</td>
            <td class="value">{{ $waiver['project']['address_line'] ?? '' }}&nbsp;</td>
        </tr>
        <tr>
            <td class="label">County:</td>
            <td class="value">{{ $waiver['project']['county'] ?? '' }}&nbsp;</td>
        </tr>
        @if (!empty($waiver['project']['apn']))
            <tr>
                <td class="label">Parcel / APN:</td>
                <td class="value">{{ $waiver['project']['apn'] }}&nbsp;</td>
            </tr>
        @endif
    </table>
</div>

@include('documents.lien.waivers._signature-lines', ['waiver' => $waiver, 'esign' => $esign ?? null])

<table class="waiver-fields" style="margin-top: 12px;">
    <tr>
        <td class="label">Address:</td>
        <td class="value">{{ $claimantAddress }}&nbsp;</td>
    </tr>
    <tr>
        <td class="label">Telephone:</td>
        <td class="value">{{ $waiver['claimant']['phone'] ?? '' }}&nbsp;</td>
    </tr>
</table>

<p class="waiver-foot">
    Missouri statutory form, Mo. Rev. Stat. &sect; 429.016.27 (valid only on a form substantially as set
    out in the statute; applies to residential real property as defined in &sect; 429.016). Under
    &sect; 429.016.29 this waiver is enforceable notwithstanding Claimant's failure to receive any promised
    payment or other consideration. Form LW-MO-UFR v{{ $waiver['form']['template_version'] }}.
</p>
