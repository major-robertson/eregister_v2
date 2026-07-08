<?php

/**
 * Massachusetts: Mass. Gen. Laws ch. 254, § 32 (void advance waivers; the
 * only statutory form), § 33 (lender disbursement rule), § 10 (dissolution
 * of a filed lien by recorded notice).
 *
 * Massachusetts prescribes NO statutory waiver form for ordinary payment
 * exchanges, so all four canonical kinds keep the generic house forms. The
 * one statutory form, the "Partial Waiver and Subordination of Lien" in
 * § 32(4), which must be "substantially in the following form with no
 * material deviation therefrom", is available ONLY to persons who filed or
 * recorded a notice of contract under § 2 (contractors in direct privity
 * with the owner) and pairs an unconditional partial waiver with a
 * subordination to the construction lender. We do not yet offer it; when we
 * do it belongs on its own statutory body, not as a swap of a generic kind.
 *
 * The anti-waiver rule is strong: § 32 voids, as against public policy, any
 * covenant, promise, agreement or understanding in, in connection with, or
 * collateral to a construction contract that purports to bar the filing of
 * a notice of contract, bar steps to enforce a lien, or subordinate lien
 * rights. Only four exceptions survive: (1) waivers by principals on § 12
 * lien bonds tied to interim/final payments received; (2) statements of
 * amounts due or paid; (3) § 10 dissolutions of lien; (4) the § 32(4)
 * statutory partial waiver form. Practical consequence for the generic
 * forms: waivers must be payment-contemporaneous (given in exchange for
 * payment actually received), never prospective.
 *
 * Execution: no notary, no witness; e-sign fine. Full/final release of a
 * FILED lien is not a waiver document at all; it is a notice of
 * dissolution signed and recorded at the registry of deeds under § 10.
 *
 * Text verified against malegislature.gov (July 2026); § 32 last amended by
 * St. 2010, c. 424 (effective July 1, 2011).
 */

return [
    'state' => 'MA',
    'state_name' => 'Massachusetts',
    'family' => 'special',
    'statute' => 'Mass. Gen. Laws ch. 254, § 32',
    'advance_waiver_note' => 'Under Mass. Gen. Laws ch. 254, § 32, any covenant, promise, agreement or understanding in, in connection with, or collateral to a construction contract that purports to bar the filing of a notice of contract, bar the taking of any steps to enforce a lien, or subordinate lien rights to the rights of other persons is against public policy and void and unenforceable. Advance or prospective lien waivers are therefore void in Massachusetts outside four narrow statutory exceptions: sign waivers only in exchange for payment actually received.',
    'ui_notes' => [
        'Massachusetts prescribes no statutory lien waiver form for ordinary payment exchanges, so these are general-purpose (non-statutory) forms. Waivers should be payment-contemporaneous: Massachusetts courts have enforced waivers given in exchange for payment actually received, while prospective waivers risk being void under c. 254, § 32.',
        'C. 254, § 32 voids advance lien waivers: any contract clause barring a notice of contract, barring lien enforcement, or subordinating lien rights is unenforceable. Do not sign an unconditional waiver before the payment it covers is in hand.',
        'The only statutory Massachusetts waiver form, the "Partial Waiver and Subordination of Lien" in c. 254, § 32(4), is limited to contractors in direct privity with the owner who filed or recorded a notice of contract under § 2, and combines a partial waiver with a subordination to the construction lender. That statutory form is not yet offered here; subcontractors and suppliers have no statutory waiver form at all.',
        'A recorded Massachusetts lien is fully released by a notice of dissolution signed and recorded at the registry of deeds under c. 254, § 10, a separate recorded instrument, not one of these waiver forms.',
    ],
    'landing' => [
        'headline' => 'Massachusetts lien waivers: no general statutory form, and advance waivers are void',
        'summary' => 'Massachusetts prescribes no statutory lien waiver form for ordinary payment exchanges: waivers are general-purpose documents, and under Mass. Gen. Laws ch. 254, § 32 any contract clause that bars filing a notice of contract, bars lien enforcement, or subordinates lien rights is void as against public policy. The statute allows only four narrow exceptions, the key one being a "Partial Waiver and Subordination of Lien" given substantially on the statutory form with no material deviation, available solely to contractors who filed a § 2 notice of contract. The safe practice everywhere else: exchange waivers only for payment actually received, and dissolve a recorded lien with a § 10 notice of dissolution at the registry of deeds.',
    ],
];
