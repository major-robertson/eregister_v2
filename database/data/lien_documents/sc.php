<?php

/*
 * South Carolina: S.C. Code Ann. Title 29, ch. 5 (Mechanics' Liens).
 *
 * Who has a lien: § 29-5-10(a) (labor or materials furnished by agreement
 * with, or by consent of, the owner) and § 29-5-20(A) (every laborer,
 * mechanic, subcontractor or supplier, when the owner authorized the
 * improvement). Lien statement: § 29-5-90 (the lien is dissolved unless,
 * within ninety days after the claimant ceases to furnish labor or
 * materials, it serves upon the owner, or the person in possession if the
 * owner cannot be found, and files with the register of deeds or clerk of
 * court of the county "a statement of a just and true account of the amount
 * due him, with all just credits given, together with a description of the
 * property intended to be covered by the lien sufficiently accurate for
 * identification, with the name of the owner of the property, if known,
 * which certificate shall be subscribed and sworn to by the person claiming
 * the lien or by someone in his behalf"; no form and no service method). An
 * inaccuracy does not invalidate it unless the claimant wilfully and
 * knowingly claimed more than is due (§ 29-5-100). A contractor who must be
 * licensed or registered "must record his contractor license number or
 * registration number on the lien document" (§ 29-5-15(A)). Suit and notice
 * of pendency within six months after last furnishing: § 29-5-120(A). Bond:
 * § 29-5-110. Release: § 29-5-430 (once the debt is fully paid, the creditor
 * enters a discharge on the margin of the registry or "shall execute a
 * release thereof, which may be recorded where the statement is recorded";
 * no form). Notice of furnishing: § 29-5-40 (to the owner, in writing, "of
 * the furnishing of such labor or material and the amount or value thereof";
 * no form) and § 29-5-20(B) (to the contractor "by certified or registered
 * mail", with the contents in (1)-(6); it counts only where a notice of
 * project commencement was filed, § 29-5-23). Fees: § 8-21-310(A)(2)(j) and
 * (B)(17). Filing office: § 30-5-10(A). Notary: § 26-1-90(B) and
 * § 26-1-120(F) (a jurat names the signer, the oath, the date, the notary's
 * signature and the commission expiry; no mandatory form) and § 26-1-120(D)
 * with § 26-3-50 (an acknowledgment that says "acknowledged before me" or
 * its substantial equivalent), so the shared generic certificates apply.
 * Title 30, ch. 5 sets no margin or first-page rule; § 30-5-30 wants a
 * recorded release acknowledged or proved.
 *
 * The chapter lists contents; it prescribes no wording for the statement,
 * the notice or the release, so nothing printed here is verbatim statute.
 * The affirmations track § 29-5-90's words, the notice of furnishing lists
 * § 29-5-20(B)(1)-(6) in order (letters/bodies/sc-notice-of-furnishing), and
 * the Verified Statement of Account page follows the Berkeley County clerk's
 * sample (instruments/clauses/sc-verified-statement-of-account). Verified
 * against scstatehouse.gov (South Carolina Code of Laws, Titles 8, 26, 29
 * and 30) on 2026-09-30.
 */

return [
    'state' => 'SC',
    'state_name' => 'South Carolina',
    'recording' => [
        'filing_office' => ['label' => 'Register of Deeds or Clerk of Court (county where the property is located)', 'method' => 'either'],
        'parcel_label' => 'TMS Number',
        'fee_note' => '$25 to file a notice of mechanic\'s lien and $10 to record a release, the same in every county (S.C. Code Ann. § 8-21-310).',
        'notes' => [
            'Serve the owner and file the statement within 90 days after the claimant last furnished labor or materials (S.C. Code Ann. § 29-5-90); if the owner cannot be found, serve the person in possession.',
            'The lien is dissolved unless suit is started and a notice of pendency is filed within six months after the claimant last furnished labor or materials (S.C. Code Ann. § 29-5-120(A)).',
            'Twenty-four counties have a Register of Deeds; in the others the Clerk of Court keeps the land records and takes the filing (S.C. Code Ann. § 30-5-10(A)).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner'],
        // § 29-5-90 gives no day count after recording: service and filing both
        // fall inside the 90 days after last furnishing, so serve at recording.
        'days_after' => 0,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Notice and Certificate of Mechanic\'s Lien',
            'statute' => 'S.C. Code Ann. § 29-5-90',
            'template_version' => 1,
            'sections' => [
                'amount' => 'breakdown',
                'gc' => true,
                'hiring_party' => true,
                'prior_notice' => true,
                // § 29-5-15(A): the contractor's license or registration number on the lien.
                'license' => true,
            ],
            'clauses' => [
                // House wording that tracks § 29-5-90; the statute prescribes no form.
                'affirmations' => [
                    'This statement is a just and true account of the amount due to Claimant, with all just credits given.',
                    'Claimant files this statement within ninety days after it last furnished labor or materials for the improvement of the property.',
                    'Claimant is serving a copy of this statement on the owner, or on the person in possession if the owner cannot be found, as S.C. Code Ann. § 29-5-90 requires.',
                ],
                // The sworn account page the Berkeley County clerk's sample adds after the lien.
                'after_execution' => ['documents.lien.instruments.clauses.sc-verified-statement-of-account'],
            ],
            'notes' => [
                'The Verified Statement of Account prints on its own page after the lien and has its own jurat, as in the Berkeley County clerk\'s sample; the claimant signs and swears twice.',
                'A contractor that must be licensed or registered in South Carolina records its license or registration number on the lien (S.C. Code Ann. § 29-5-15(A)); the line prints the business\'s number unless Document details sets another.',
                'If neither the owner nor the person in possession can be found after a diligent search, file the statement with an affidavit of the sheriff or a deputy saying so (S.C. Code Ann. § 29-5-90).',
                'Open question for Major: § 29-5-90 does not say how the owner must be served; is certified mail, return receipt requested, enough, or should the statement be served in person?',
            ],
        ],
        'lien_release' => [
            'title' => 'Release and Satisfaction of Mechanic\'s Lien',
            'statute' => 'S.C. Code Ann. § 29-5-430',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'clauses' => [
                'affirmations' => [
                    'The debt secured by the lien described above has been fully paid. This release may be recorded where the statement of the lien is recorded (S.C. Code Ann. § 29-5-430).',
                ],
            ],
            'notes' => [
                'Record the release where the lien statement is recorded; the fee is $10 (S.C. Code Ann. §§ 29-5-430, 8-21-310(B)(17)).',
                'Open question for Major: the release is acknowledged before a notary without witnesses (S.C. Code Ann. § 30-5-30(A)(2)); will the registers record it without the two witnesses that § 30-5-30(B) describes?',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Furnishing Labor or Materials',
            'statute' => 'S.C. Code Ann. §§ 29-5-20(B) and 29-5-40',
            'body' => 'documents.lien.letters.bodies.sc-notice-of-furnishing',
            'template_version' => 1,
            'sections' => [
                'amount' => 'estimate',
                'gc' => true,
                'first_furnish' => true,
                'last_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Tells the owner in writing what the claimant furnishes and its amount or value (S.C. Code Ann. § 29-5-40); after the owner receives it, the owner\'s payments to the contractor do not reduce what the claimant can recover (§ 29-5-50).',
                'A sub-subcontractor or a supplier to a subcontractor must also send it to the contractor by certified or registered mail, or its lien cannot exceed what the contractor owes the subcontractor (S.C. Code Ann. § 29-5-20(B)); that limit applies only when a notice of project commencement was filed (§ 29-5-23).',
                'Neither section sets a deadline, and the notice protects only against payments made after it arrives, so send it early.',
                'State any materials specially fabricated by someone other than the claimant, with their price or value, separately in the description of work (S.C. Code Ann. § 29-5-20(B)(3)).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to File a Mechanic\'s Lien',
            'statute' => 'S.C. Code Ann. § 29-5-10 et seq.',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'method' => 'certified_mail',
            ],
            'notes' => [
                'South Carolina has no notice-of-intent step; the notice of furnishing and the lien statement are the statutory steps. This is a demand courtesy.',
                'Send it early enough that the 10-day demand ends well inside the 90-day window to serve and file the lien (S.C. Code Ann. § 29-5-90).',
            ],
        ],
    ],
];
