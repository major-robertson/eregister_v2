<?php

/*
 * Charleston County, South Carolina (Register of Deeds; a register county
 * under S.C. Code Ann. § 30-5-10(A)).
 *
 * Learned from an earlier eRegister filing: the register prepends its own
 * cover page, which becomes page 1 of the recorded instrument, and recording
 * cost $25, which is South Carolina's uniform fee for a notice of mechanic's
 * lien (§ 8-21-310(A)(2)(j)). The fee note stays in sc.php so the release
 * card keeps its $10 figure.
 */

return [
    'county' => 'Charleston',
    'recording' => [
        'filing_office' => [
            'label' => 'Charleston County Register of Deeds',
            'method' => 'erecord',
        ],
        'adds_cover_page' => true,
    ],
    'notes' => [
        'The Register of Deeds adds its own cover page, which becomes page 1 of the recorded instrument.',
    ],
    // How documents are signed here (EREG-142): only what this file already
    // states; null is unknown, and a known value wins over the state's.
    'signature_requirements' => [
        'esign_accepted' => null,
        'notary_required' => null,
        'wet_ink_original_required' => null,
        'ron_accepted' => null,
        'source' => null,
    ],
];
