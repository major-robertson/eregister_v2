{{--
    What N.J.S.A. 2A:44A-30(a) says the claimant's discharge certificate
    "shall contain": "(1) The date of filing the lien claim; (2) The book and
    page number endorsed thereon; (3) The name of the owner of the land, or
    the community association, if applicable, named in the notice; (4) The
    location of the property; and (5) The name of the person for whom the
    work, services, equipment or materials was provided." Verified against
    lis.njleg.state.nj.us on 2026-09-30. The section sets out no form for
    this certificate, so the labels are house wording in the statute's
    order. The filing date, book and page come from the original lien in
    Document details (or the project's recorded lien filing); a missing one
    prints as a ruled blank.
--}}
@php
    $original = $doc['original_lien'];
    $project = $doc['project'];
    $owner = $doc['parties']['owner'];
    $hiring = $doc['parties']['hiring'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
    // A book or page from Document details wins; otherwise the recording reference, which New
    // Jersey clerks often write as the book and page; otherwise blanks.
    $showBookPage = $original['book'] !== null || $original['page'] !== null || $original['recording_reference'] === null;
@endphp
<p>As N.J.S.A. 2A:44A-30(a) requires, this certificate states:</p>
<table class="fields">
    <tr>
        <td class="k">Date the lien claim was filed</td>
        <td>{!! $blank($original['recorded_at'], 'fill fill-mid') !!}</td>
    </tr>
    <tr>
        <td class="k">Book and page endorsed on the lien claim</td>
        <td>
            @if ($showBookPage)
                Book {!! $blank($original['book'], 'fill fill-short') !!}, Page {!! $blank($original['page'], 'fill fill-short') !!}
            @else
                {{ $original['recording_reference'] }}
            @endif
        </td>
    </tr>
    <tr>
        <td class="k">Owner named in the lien claim</td>
        <td>{!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
    <tr>
        <td class="k">Location of the property</td>
        <td>{!! $blank($project['address']['single_line'], 'fill fill-wide') !!}{{ $project['county'] ? ' ('.$project['county'].' County, '.$doc['form']['state_name'].')' : '' }}</td>
    </tr>
    <tr>
        <td class="k">Person for whom the work, services, equipment or materials was provided</td>
        <td>{!! $blank($hiring['display_name'] ?? null, 'fill fill-wide') !!}</td>
    </tr>
</table>
