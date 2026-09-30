<?php

/*
 * Tennessee: Tenn. Code Ann. Title 66, ch. 11, part 1 (mechanics' and
 * materialmen's liens) and ch. 24 (registration).
 *
 * Notice of lien: § 66-11-112(a) (to keep the lien's priority against later
 * purchasers and lenders, the lienor records in the register of deeds office of
 * the county where the property lies "a sworn statement of the amount for, and a
 * reasonably certain description of the real property on, which the lien is
 * claimed", no later than 90 days after the improvement is complete or
 * abandoned; (d) "The statement provided for in subsection (a) may be in
 * substantially the following form", the NOTICE OF LIEN rendered in
 * bodies/tn-sworn-statement). A remote contractor (one whose contract is with
 * anyone other than the owner, § 66-11-101(14)) must also serve the notice of
 * lien on the owner within that time, and it "may be in substantially the form
 * provided in § 66-11-112(d)" (§ 66-11-115(a)(2), (c)); its lien lasts 90 days
 * from service unless suit is brought (§ 66-11-115(b), § 66-11-126). A prime
 * contractor's lien lasts one year after completion or abandonment
 * (§ 66-11-106). Service: registered or certified mail, return receipt
 * requested, notarized hand delivery, or a commercial carrier with written
 * confirmation of delivery (§ 66-11-149(c)).
 *
 * Notice of nonpayment: § 66-11-145 (every remote contractor, except on one- to
 * four-family residential units, serves the owner and the prime contractor
 * within 90 days of the last day of each month of unpaid work; contents in
 * (a)(1)-(5); the form in (d), rendered in letters/bodies/tn-notice-of-nonpayment;
 * without it a remote contractor has no lien except for retainage, (b)).
 * Release: § 66-11-135 (recorded where the notice of lien was recorded; the
 * part prescribes no wording). A copy of an electronic document records only
 * with the certification of § 66-24-101(d)(3), which "must be in the following
 * form" (instruments/clauses/tn-certificate-of-authenticity).
 *
 * Verified on 2026-09-30 against the 2025 Tennessee Code as reproduced on
 * law.justia.com, whose pages show the § 66-11-112(d) and § 66-11-145(d) forms
 * only as images, so the form wording comes from Justia's 2021 edition and
 * matches codes.findlaw.com (current as of 2024-01-02); neither section has
 * been amended since 2007. The § 66-24-101(d)(3) certification matches on
 * Justia (2025), FindLaw and the Hamilton County register's 2024 requirement
 * guide. The official LexisNexis site blocks automated reading.
 */

return [
    'state' => 'TN',
    'state_name' => 'Tennessee',
    'recording' => [
        'filing_office' => ['label' => 'Register of Deeds', 'method' => 'mail'],
        // Tennessee assessors and registers index parcels by tax map, group and parcel.
        'parcel_label' => 'Map and Parcel',
        'fee_note' => '$10 for up to two pages plus $5 for each additional page, and $2 for each instrument (Tenn. Code Ann. § 8-21-1001).',
        'notes' => [
            'Notices of lien and releases are recorded with the register of deeds of the county where the property is located (Tenn. Code Ann. § 66-11-112(a), § 66-11-135(b)).',
            'The Hamilton County register rejects a street address in place of a legal description (a subdivision lot or metes and bounds) and indexes by book and page (2024 requirement guide).',
        ],
    ],
    'execution' => [
        'verification' => 'sworn',
        'notary' => true,
        'notary_form' => 'jurat',
    ],
    'service' => [
        'recipients' => ['owner'],
        'days_after' => 0,
        'method' => 'certified_mail',
    ],
    'kinds' => [
        'mechanics_lien' => [
            'title' => 'Notice of Lien',
            'statute' => 'Tenn. Code Ann. § 66-11-112',
            'body' => 'documents.lien.instruments.bodies.tn-sworn-statement',
            'template_version' => 1,
            'sections' => [
                // The form states one sum, in words and figures; no amounts table.
                'amount' => 'single',
                'amount_in_words' => true,
                // A sub-subcontractor's notice also names the prime contractor.
                'gc' => true,
                // The notice of nonpayment is monthly and remote-only; the form recites no prior notice.
                'prior_notice' => false,
            ],
            // The § 66-11-112(d) form opens "(name) being first duly sworn, says
            // that", so the body is the sworn statement and the execution block
            // prints only the jurat.
            'execution' => ['statement' => false],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => 0,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                // § 66-24-101(d)(3): only when the register gets a copy of an electronic document.
                'after_execution' => ['documents.lien.instruments.clauses.tn-certificate-of-authenticity'],
            ],
            'notes' => [
                'Record within 90 days after the improvement is complete or abandoned, and mail the owner a copy at recording; a remote contractor (anyone who did not contract with the owner) must serve it on the owner within those 90 days (Tenn. Code Ann. § 66-11-112(a), § 66-11-115(a)(2)).',
                'A prime contractor\'s lien lasts one year after the improvement is complete or abandoned (Tenn. Code Ann. § 66-11-106); a remote contractor\'s lien lasts 90 days from service of the notice of lien (§ 66-11-115(b)); suit to enforce either must be filed within that time (§ 66-11-126).',
                'A remote contractor has a lien only if it served the monthly notices of nonpayment, which one- to four-family residential units do not need (Tenn. Code Ann. § 66-11-145); on owner-occupied residential property only the prime contractor has a lien (§ 66-11-146(a)).',
                'After an owner records a notice of completion, a lienor that has not recorded and served its notice of lien has 10 days (one- to four-family residential) or 30 days (other projects) to serve written notice of its claim, or the lien expires (Tenn. Code Ann. § 66-11-143(e)).',
                'The lien amount cannot include interest, service charges, late fees or attorney\'s fees (Tenn. Code Ann. § 66-11-102(e)), and a lienor that has not complied with the contractor licensing law has no lien (§ 66-11-102(a)).',
                'Keep the certificate of authenticity page only when the register gets a copy of an electronic document, such as a printed copy of an online-notarized notice or an e-recorded scan; drop it when the wet-ink original is mailed (Tenn. Code Ann. § 66-24-101(d)).',
                'The June 2026 Sumner County notice carried an acknowledgment instead of a jurat; this notice prints the jurat because the statute calls for a sworn statement.',
                'Undecided: whether every register will record the notice with only the jurat, since the § 66-11-112(d) form ends "[Notary Acknowledgment]" and a register may refuse an instrument whose signature is not acknowledged or proved (Tenn. Code Ann. § 66-22-101(a), § 66-24-101(e)); until counsel says otherwise, staff send it with the jurat and, if a register refuses it, have the notary add a Tennessee acknowledgment (§ 66-22-107, § 66-22-108).',
                'Undecided: whether a register treats the notice as an affidavit that must carry the jurat wording of Tenn. Code Ann. § 66-24-101(g) ("SWORN TO AND SUBSCRIBED before me"), which the generic jurat words differently; until counsel says otherwise, staff use the generic jurat.',
                'Undecided: who may sign the certificate of authenticity for an online-notarized notice, the claimant\'s signer or the eRegister staff member who holds the notarized file, since the statute asks for "a licensed attorney and/or the custodian of the original version"; until counsel says otherwise, staff do what the June 2026 Sumner County filing did.',
            ],
        ],
        'lien_release' => [
            'title' => 'Release of Lien',
            'statute' => 'Tenn. Code Ann. § 66-11-135',
            'execution' => [
                'verification' => 'acknowledged',
                'notary' => true,
                'notary_form' => 'acknowledgment',
            ],
            'service' => [
                'recipients' => ['owner'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'clauses' => [
                'after_execution' => ['documents.lien.instruments.clauses.tn-certificate-of-authenticity'],
            ],
            'notes' => [
                'Record the release in the register of deeds office where the notice of lien was recorded; the lien counts as released on the day the release is recorded (Tenn. Code Ann. § 66-11-135(b), (c)).',
                'Once the lien is paid, expired or forfeited, a lienor that does not record a release within 30 days after the owner\'s written demand owes the owner its damages, costs and attorney\'s fees (Tenn. Code Ann. § 66-11-135(a)).',
                'Put the book and page of the recorded notice of lien in Document details; registers index the release against it.',
                'A Tennessee online notary must say the signer "personally appeared before me by audio-video communication" (Tenn. Code Ann. § 66-22-101(d)); the acknowledgment printed here says "personally appeared", so an online notary amends it.',
                'Keep the certificate of authenticity page only when the register gets a copy of an electronic document; drop it when the wet-ink original is mailed (Tenn. Code Ann. § 66-24-101(d)).',
            ],
        ],
        'prelim_notice' => [
            'title' => 'Notice of Nonpayment',
            'statute' => 'Tenn. Code Ann. § 66-11-145',
            'body' => 'documents.lien.letters.bodies.tn-notice-of-nonpayment',
            'template_version' => 1,
            'sections' => [
                'amount' => 'single',
                'gc' => true,
                'first_furnish' => false,
                'last_furnish' => true,
            ],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Remote contractors only: serve a notice for each month in which unpaid work or materials were furnished, within 90 days after the last day of that month, and only while the account is unpaid (Tenn. Code Ann. § 66-11-145(a)).',
                'Serve the owner and the prime contractor by registered or certified mail with return receipt, by hand delivery proved by a notarized statement, or by a commercial carrier that confirms delivery in writing (Tenn. Code Ann. § 66-11-149(c)).',
                'Not required on one- to four-family residential units (Tenn. Code Ann. § 66-11-145(a)); on owner-occupied residential property only the prime contractor has a lien at all (§ 66-11-146(a)).',
                'A remote contractor that skips a required notice has no lien except for retainage (Tenn. Code Ann. § 66-11-145(b)); the notice does not replace the notice of lien served under § 66-11-115 (§ 66-11-145(c)).',
                'The names, addresses and property description on the building permit are presumed correct for this notice (Tenn. Code Ann. § 66-11-149(a)).',
            ],
        ],
        'noi' => [
            'title' => 'Notice of Intent to Record a Lien',
            'statute' => 'Tenn. Code Ann. § 66-11-101 et seq.',
            'sections' => ['demand_days' => 10],
            'service' => [
                'recipients' => ['owner', 'gc'],
                'days_after' => null,
                'method' => 'certified_mail',
            ],
            'notes' => [
                'Tennessee has no notice-of-intent step; a remote contractor\'s statutory notice is the monthly notice of nonpayment (Tenn. Code Ann. § 66-11-145). This is a demand courtesy.',
            ],
        ],
    ],
];
