{{--
    Alabama materialman's notice to the owner before furnishing, the form of
    Ala. Code § 35-11-210 ("The notice may be given in the following form,
    which shall be sufficient"), verified against the official Code of
    Alabama on alison.legislature.state.al.us on 2026-09-30. The form's words
    are verbatim, with the owner, the contractor or subcontractor the
    material goes to, and the property in its blanks. The section also has
    the materialman notify the owner "that certain specified material will be
    furnished ... at certain specified prices", so the material and its price
    (the estimate) follow the form in house wording. The signature comes from
    the shell. Never edit the form's wording without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $owner = $doc['parties']['owner'];
    $hiring = $doc['parties']['hiring'];
    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    // "your contractor or subcontractor": the party the material is sold to, never the owner.
    $contractor = $same($hiring, $owner) ? null : $hiring;
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<p>To {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}, owner or proprietor:</p>

<p>Take notice, that the undersigned is about to furnish {!! $blank($contractor['display_name'] ?? null, 'fill fill-wide') !!}, your contractor or subcontractor, certain material for the construction, or for the repairing, altering, or beautifying of a building or buildings, or improvement or improvements, on the following described property:</p>

<div class="indent">
    @include('documents.lien._parts.property')
</div>

<p>and there will become due to the undersigned on account thereof the price of the material, for the payment of which the undersigned will claim a lien.</p>

<table class="fields">
    <tr>
        <td class="k">Material to be furnished</td>
        <td>@include('documents.lien._parts.work')</td>
    </tr>
    <tr>
        <td class="k">Price of the material</td>
        <td>${!! $blank($filing['estimate'], 'fill fill-mid') !!}</td>
    </tr>
</table>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
