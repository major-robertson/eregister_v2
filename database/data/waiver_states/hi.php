<?php

/*
 * Hawaii: no statutory lien waiver form and no waiver regulation.
 *
 * The mechanics' and materialmen's lien statute (HRS §§ 507-41 to 507-49)
 * is silent on waivers (no forms, no anti-waiver rule, no notarization
 * requirement), so the generic house forms apply and enforceability of a
 * no-lien clause or advance waiver is a common-law, case-by-case question.
 * Caution encoded from the research verification: some secondary summaries
 * claim HRS § 507-42 voids prospective waivers, but the actual text of
 * § 507-42 ("When allowed; lessees, etc.") is the lien-GRANTING provision
 * and says nothing about waivers, so no anti-waiver cite is asserted here.
 * Distinct Hawaii wrinkle (context, not waiver law): a mechanic's lien
 * attaches only after a court hearing on an Application for Lien
 * (HRS § 507-43). No ui_notes: no notable waiver rule to banner.
 */

return [
    'state' => 'HI',
    'state_name' => 'Hawaii',
    'family' => 'generic',
    'advance_waiver_note' => 'Hawaii has no statute regulating lien waivers or barring waiver of lien rights in advance of payment: the mechanics\' and materialmen\'s lien statute (HRS §§ 507-41 to 507-49) is silent on waivers, so the enforceability of a no-lien clause or advance waiver is a common-law, case-by-case question. (Some secondary sources claim HRS § 507-42 voids prospective waivers, but that section is the lien-granting provision and says nothing about waivers.)',
    'landing' => [
        'headline' => 'Hawaii Lien Waivers: No Statutory Form or Waiver Rules',
        'summary' => 'Hawaii does not prescribe a lien waiver form: the mechanics\' and materialmen\'s lien statute (HRS §§ 507-41 to 507-49) is silent on waivers, so our general-purpose conditional and unconditional progress and final waiver forms apply, with no notarization required. There is no statute barring advance waivers, so their enforceability is a case-by-case, common-law question, and conditional waivers tied to actual payment are the prudent default. Hawaii\'s separate wrinkle: a mechanic\'s lien attaches only after a court hearing on an application for lien under HRS § 507-43.',
    ],
];
