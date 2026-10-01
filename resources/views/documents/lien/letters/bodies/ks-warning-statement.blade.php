{{--
    Kansas warning statement, K.S.A. 60-1103a(c): "The warning statement
    provided for by this section, to be effective, shall contain
    substantially the following statement". Verbatim from ksrevisor.gov,
    verified 2026-09-30, with the claimant, the job number, the residence
    address and the contractor filled in; a missing value prints as a ruled
    blank. "(name of contractor)" is the original contractor when the project
    has one (the owner's "contractor" in the statement's last two sentences),
    else the party the claimant contracted with. A subcontractor or supplier
    mails it to any one owner of residential property before filing a lien
    (§ 60-1103a(b)(1)); the owner's acknowledgment, the (b)(2) alternative,
    follows the signature (letters/clauses/ks-owner-acknowledgment). Never
    edit the statement without re-checking the statute.
--}}
@php
    $project = $doc['project'];
    $parties = $doc['parties'];
    $claimant = $parties['claimant'];
    $gc = $parties['gc'];
    $same = fn (?array $a, ?array $b) => $a !== null && $b !== null && $a['display_name'] !== null && $a['display_name'] === $b['display_name'];
    $contractor = $gc !== null && ! $same($gc, $claimant) ? $gc : $parties['hiring'];
    $blank = fn (?string $value, string $class = 'fill') => $value !== null && $value !== '' ? e($value) : '<span class="'.$class.'">&nbsp;</span>';
@endphp
<div class="notice-box">
    <p style="margin-bottom: 0;">Notice to owner: {!! $blank($claimant['display_name'] ?? null, 'fill fill-wide') !!} is a supplier or subcontractor providing materials or labor on Job No. {!! $blank($project['job_number'], 'fill fill-mid') !!} at {!! $blank($project['address']['single_line'], 'fill fill-wide') !!} under an agreement with {!! $blank($contractor['display_name'] ?? null, 'fill fill-wide') !!}. Kansas law will allow this supplier or subcontractor to file a lien against your property for materials or labor not paid for by your contractor unless you have a waiver of lien signed by this supplier or subcontractor. If you receive a notice of filing of a lien statement by this supplier or subcontractor, you may withhold from your contractor the amount claimed until the dispute is settled.</p>
</div>

@include('documents.lien._parts.clauses', ['only' => 'after_property'])
@include('documents.lien._parts.clauses', ['only' => 'before_signature'])
