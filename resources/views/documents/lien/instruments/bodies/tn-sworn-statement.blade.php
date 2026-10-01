{{--
    Tennessee Notice of Lien: the sworn statement Tenn. Code Ann. § 66-11-112(a)
    requires, in the words of the § 66-11-112(d) form ("may be in substantially
    the following form"), which § 66-11-115(c) also allows for a remote
    contractor's notice of lien. The form's text is verbatim from the 2021
    Tennessee Code on law.justia.com and matches codes.findlaw.com (current as
    of 2024-01-02); the section is unamended since 2007. Checked 2026-09-30.

    The blanks are filled from live data. The affiant is the signer. The party
    the claimant contracted with, who is also the party the sum is due from, is
    the owner for a prime contractor and the hiring party for a remote
    contractor; the form's "[the owner, prime contractor, remote contractor, or
    other person, as the case may be]" prints as the one that applies. Dates
    print as "the 30th day of June, 2026". The sum prints in words and figures,
    the words ending "Dollars and ... Cents" in place of the form's own
    "dollars". The form's closing "Lienor" and "[Notary Acknowledgment]" lines
    are the shell's signature block and jurat (see tn.php). After the form: the
    description of work, a remote contractor's § 66-11-115 line and, for a
    sub-subcontractor or its supplier, the prime contractor. Never edit the
    form's wording without re-checking the statute.
--}}
@php
    $filing = $doc['filing'];
    $project = $doc['project'];
    $parties = $doc['parties'];
    $sections = $doc['form']['sections'];
    $claimant = $parties['claimant'];
    $owner = $parties['owner'];
    $gc = $parties['gc'];
    $work = $filing['description_of_work'];

    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';

    // Who the claimant contracted with (and is owed by), and which of the form's
    // "[the owner, prime contractor, remote contractor, or other person]" that is.
    $hiredBy = $project['hired_by'] ?? match ($project['claimant_type']) {
        'gc', 'supplier_to_owner' => 'owner',
        'subcontractor', 'supplier_to_contractor' => 'direct_contractor',
        'sub_sub_contractor', 'supplier_to_subcontractor' => 'subcontractor',
        default => null,
    };
    $inPrivity = $project['in_privity'] === true;
    $remote = $project['in_privity'] === false;
    $contracting = $inPrivity ? $owner : $parties['hiring'];
    $role = match (true) {
        $inPrivity => 'the owner',
        $hiredBy === 'direct_contractor' => 'the prime contractor',
        $hiredBy === 'subcontractor' => 'a remote contractor',
        default => '<span class="fill fill-mid">&nbsp;</span>',
    };
    $contractingParty = $blank($contracting['display_name'] ?? null, 'fill fill-wide').', '.$role;

    // "the ___ day of ___, ___ (year)"
    $dayOf = function (?string $date): string {
        if ($date === null) {
            return 'the <span class="fill fill-short">&nbsp;</span> day of <span class="fill fill-mid">&nbsp;</span>, <span class="fill fill-short">&nbsp;</span> (year)';
        }

        $day = \Illuminate\Support\Carbon::parse($date);

        return 'the '.$day->format('jS').' day of '.$day->format('F').', '.$day->format('Y');
    };

    // "the sum of ___ dollars": the words already say "Dollars and ... Cents", so the figures follow in parentheses.
    $sum = match (true) {
        $filing['amount'] === null => '<span class="fill fill-mid">&nbsp;</span> dollars',
        ! empty($sections['amount_in_words']) && $filing['amount_words'] !== null => e($filing['amount_words']).' ($'.e($filing['amount']).')',
        default => '$'.e($filing['amount']),
    };

    $namesPrime = ! empty($sections['gc']) && $remote && $gc !== null && ! $same($gc, $contracting) && ! $same($gc, $claimant);
@endphp
<p>
    {!! $blank($doc['signer']['name'], 'fill fill-wide') !!} being first duly sworn, says that {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!}, the Lien Claimant, furnished certain material or performed certain work or labor in furtherance of improvements to the real property hereinafter described, in pursuance of a certain contract, with {!! $contractingParty !!}. The first of the work or labor was performed or the first of the material, services, equipment, or machinery was furnished on {!! $dayOf($project['dates']['first_furnish']) !!}. The last of the work or labor was performed or the last of the material, services, equipment, or machinery was furnished on {!! $dayOf($project['dates']['last_furnish']) !!}, and there is justly and truly due Lien Claimant therefor from {!! $contractingParty !!} over and above all legal setoffs, the sum of {!! $sum !!}, for which amount Lien Claimant claims a lien under T.C.A. §§ 66-11-101, et seq. on the real property, of which {!! $blank($owner['display_name'] ?? null, 'fill fill-wide') !!} is or was the owner, which is described as follows:
</p>

<div class="indent keep">
    @include('documents.lien._parts.property')
</div>

<p><span class="lbl">Description of work:</span> {!! $work !== null ? nl2br(e($work)) : '<span class="fill fill-wide">&nbsp;</span>' !!}</p>

@if ($remote)
    <p>Lien Claimant is a remote contractor and serves this notice of lien on the owner under Tenn. Code Ann. § 66-11-115.</p>
@endif

@if ($namesPrime)
    <p><span class="lbl">Prime contractor:</span> {{ $gc['display_name'] }}{{ $gc['address_line'] ? ', '.$gc['address_line'] : '' }}.</p>
@endif

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
