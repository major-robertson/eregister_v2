<?php

/*
 * Berkeley County, South Carolina (Register of Deeds; a register county
 * under S.C. Code Ann. § 30-5-10(A)).
 *
 * Learned from the JM Mechanical Services lien (a subcontractor's ductwork
 * claim against Pinnacle Bank's property, general contractor Brantley
 * Construction), recorded May 2026. The register prepends its own cover
 * page, which becomes page 1 of the recorded instrument. Its index names the
 * owner as Maker and the claimant as Recipient, and it indexed the general
 * contractor as the direct party because the old form labelled that party
 * "Contractor / Hiring Party". The clerk's own samples add a sworn Verified
 * Statement of Account page, which lien_documents/sc.php now prints
 * statewide. The fee is South Carolina's uniform $25 (§ 8-21-310), set in
 * sc.php.
 */

return [
    'county' => 'Berkeley',
    'recording' => [
        'filing_office' => [
            'label' => 'Berkeley County Register of Deeds',
            'method' => 'erecord',
        ],
        'adds_cover_page' => true,
    ],
    'notes' => [
        'The Register of Deeds adds its own cover page, which becomes page 1 of the recorded instrument.',
        'The register indexes the owner as Maker and the claimant as Recipient; it should follow the "Claimant / Lienor" and "Owner" labels on the page-1 index line (in May 2026 it indexed the general contractor, which the old form labelled "Contractor / Hiring Party").',
    ],
];
