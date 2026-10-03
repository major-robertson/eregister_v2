<?php

/*
|--------------------------------------------------------------------------
| Resale Certificate Generator
|--------------------------------------------------------------------------
|
| Domain settings plus the state -> certificate-class registry ported from
| the original TaxResaleCertificate app. Each state maps to a PDF handler
| (FPDI coordinate stamping onto the official state form in
| resources/pdfs/state_resale_certificates, or custom FPDF drawing when
| 'template' is empty). Uniform MTC/SST pseudo-states cover multiple
| states with one form.
|
*/

return [

    /*
    | Storage disk for generated certificate PDFs and signature images.
    */
    'disk' => env('RESALE_CERT_DISK', env('FILESYSTEM_DISK', 'local')),

    /*
    | Path prefix for stored objects on the disk above.
    */
    'storage_prefix' => 'resale-certificates',

    /*
    | Directory (relative to resources/) holding the official state PDF forms.
    */
    'templates_path' => 'pdfs/state_resale_certificates',

    /*
    | Cashier subscription type + prices-catalog coordinates for the
    | $297/yr unlimited-generation subscription.
    */
    'subscription_type' => 'resale_cert',
    'price_family' => 'resale_cert',
    'price_key' => 'resale_cert_generator',

    /*
    | 'state_issued' marks a state that issues the resale certificate itself
    | (document, issuer, url, guidance shown to the customer).
    | 'registered_buyers' => 'blocked': a buyer registered there must use the
    | state's document, so the wizard locks the state for them and no blank
    | form is offered. 'note': the state stays selectable and the wizard
    | shows the guidance as a note. A state with 'class' => null has no
    | generator: it can only be covered by a uniform form it accepts.
    */
    'states' => [
        'AL' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\AlabamaCertificate::class,
            'template' => '',  // Uses custom generation
            'name' => 'Alabama',
        ],
        'AK' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MtcUniformCertificate::class,
            'template' => 'mtc.pdf',
            'name' => 'Alaska',
        ],
        'AZ' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\ArizonaCertificate::class,
            'template' => 'arizona.pdf',
            'name' => 'Arizona',
        ],
        'AR' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\ArkansasCertificate::class,
            'template' => 'arkansas.pdf',
            'name' => 'Arkansas',
        ],
        'CA' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\CaliforniaCertificate::class,
            'template' => 'california.pdf',
            'name' => 'California',
        ],
        'CO' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\ColoradoCertificate::class,
            'template' => 'colorado.pdf',
            'name' => 'Colorado',
        ],
        'CT' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\ConnecticutCertificate::class,
            'template' => 'connecticut.pdf',
            'name' => 'Connecticut',
        ],
        // 'DE' => [
        //     'class' => null,
        //     'template' => '',
        //     'name' => 'Delaware',
        // ],  // TODO: Delaware - No sales tax, no certificate needed
        'DC' => [
            'class' => null,
            'template' => '',
            'name' => 'District of Columbia',
            'state_issued' => [
                'document' => 'Certificate of Resale (OTR-368)',
                'issuer' => 'District of Columbia Office of Tax and Revenue',
                'url' => 'https://mytax.dc.gov/',
                'guidance' => 'The District issues this certificate to registered businesses. Request your Certificate of Resale (OTR-368) through MyTax.DC.gov. It expires after one year.',
                'registered_buyers' => 'blocked',
            ],
        ],
        'FL' => [
            'class' => null,
            'template' => '',
            'name' => 'Florida',
            'state_issued' => [
                'document' => 'Annual Resale Certificate (Form DR-13)',
                'issuer' => 'Florida Department of Revenue',
                'url' => 'https://floridarevenue.com/taxes/printcertificate',
                'guidance' => 'Florida issues this certificate to registered dealers. Print your Annual Resale Certificate (Form DR-13) from your Florida Department of Revenue account.',
                'registered_buyers' => 'blocked',
            ],
        ],
        'GA' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\GeorgiaCertificate::class,
            'template' => 'georgia.pdf',
            'template_out_of_state' => 'georgia_out_of_state.pdf',
            'name' => 'Georgia',
        ],
        'HI' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\HawaiiCertificate::class,
            'template' => 'hawaii.pdf',
            'name' => 'Hawaii',
        ],
        'ID' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\IdahoCertificate::class,
            'template' => 'idaho.pdf',
            'name' => 'Idaho',
        ],
        'IL' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\IllinoisCertificate::class,
            'template' => 'illinois.pdf',
            'name' => 'Illinois',
        ],
        'IN' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\IndianaCertificate::class,
            'template' => 'indiana.pdf',
            'name' => 'Indiana',
        ],
        'IA' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\IowaCertificate::class,
            'template' => 'iowa.pdf',
            'name' => 'Iowa',
        ],
        'KS' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\KansasCertificate::class,
            'template' => 'kansas.pdf',
            'name' => 'Kansas',
        ],
        'KY' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\KentuckyCertificate::class,
            'template' => 'kentucky.pdf',
            'name' => 'Kentucky',
        ],
        'LA' => [
            'class' => null,
            'template' => '',
            'name' => 'Louisiana',
            'state_issued' => [
                'document' => 'Louisiana Resale Certificate (Form R-1064)',
                'issuer' => 'Louisiana Department of Revenue',
                'url' => 'https://latap.revenue.louisiana.gov/',
                'guidance' => 'Louisiana issues this certificate to registered dealers. Get your Louisiana Resale Certificate (Form R-1064) from the Department of Revenue through LaTAP.',
                'registered_buyers' => 'blocked',
            ],
        ],
        'ME' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MtcUniformCertificate::class,
            'template' => 'mtc.pdf',
            'name' => 'Maine',
            'state_issued' => [
                'document' => 'Resale Certificate',
                'issuer' => 'Maine Revenue Services',
                'url' => 'https://www.maine.gov/revenue/',
                'guidance' => 'Maine Revenue Services issues a Resale Certificate to registered retailers. Give suppliers a copy of the certificate Maine issued to you.',
                'registered_buyers' => 'blocked',
            ],
        ],
        'MD' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MarylandCertificate::class,
            'template' => 'maryland.pdf',
            'name' => 'Maryland',
        ],
        'MA' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MassachusettsCertificate::class,
            'template' => 'massachusetts.pdf',
            'name' => 'Massachusetts',
        ],
        'MI' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MichiganCertificate::class,
            'template' => 'michigan.pdf',
            'name' => 'Michigan',
        ],
        'MN' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MinnesotaCertificate::class,
            'template' => 'minnesota.pdf',
            'name' => 'Minnesota',
        ],
        'MS' => [
            'class' => null,
            'template' => '',
            'name' => 'Mississippi',
            'state_issued' => [
                'document' => 'Mississippi sales tax permit',
                'issuer' => 'Mississippi Department of Revenue',
                'url' => 'https://tap.dor.ms.gov/',
                'guidance' => 'Mississippi has no resale certificate form. Give each supplier a copy of your Mississippi sales tax permit, which you can get from TAP.',
                'registered_buyers' => 'blocked',
            ],
        ],
        'MO' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MissouriCertificate::class,
            'template' => 'missouri.pdf',
            'name' => 'Missouri',
        ],
        // 'MT' => [
        //     'class' => null,
        //     'template' => '',
        //     'name' => 'Montana',
        // ],  // TODO: Montana - No state sales tax, no certificate needed
        'NE' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\NebraskaCertificate::class,
            'template' => 'nebraska.pdf',
            'name' => 'Nebraska',
        ],
        'NV' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\NevadaCertificate::class,
            'template' => 'nevada.pdf',
            'name' => 'Nevada',
        ],
        // 'NH' => [
        //     'class' => null,
        //     'template' => '',
        //     'name' => 'New Hampshire',
        // ],  // TODO: New Hampshire - No state sales tax, no certificate needed
        'NJ' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\NewJerseyCertificate::class,
            'template' => 'new_jersey.pdf',
            'template_out_of_state' => 'new_jersey_out_of_state.pdf',
            'name' => 'New Jersey',
        ],
        'NM' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MtcUniformCertificate::class,
            'template' => 'mtc.pdf',
            'name' => 'New Mexico',
            'state_issued' => [
                'document' => 'Nontaxable Transaction Certificate, Type 2',
                'issuer' => 'New Mexico Taxation and Revenue Department',
                'url' => 'https://tap.state.nm.us/',
                'guidance' => 'New Mexico buyers use a Type 2 Nontaxable Transaction Certificate. Execute yours in the Taxpayer Access Point (TAP).',
                'registered_buyers' => 'blocked',
            ],
        ],
        'NY' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\NewYorkCertificate::class,
            'template' => 'new_york.pdf',
            'name' => 'New York',
        ],
        'NC' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SstUniformCertificate::class,
            'template' => 'sst.pdf',
            'name' => 'North Carolina',
        ],
        'ND' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\NorthDakotaCertificate::class,
            'template' => 'north_dakota.pdf',
            'name' => 'North Dakota',
        ],
        'OH' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\OhioCertificate::class,
            'template' => 'ohio.pdf',
            'name' => 'Ohio',
        ],
        'OK' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\OklahomaCertificate::class,
            'template' => '',  // Uses custom generation
            'name' => 'Oklahoma',
        ],
        // 'OR' => [
        //     'class' => null,
        //     'template' => '',
        //     'name' => 'Oregon',
        // ],  // TODO: Oregon - No state sales tax, no certificate needed
        'PA' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\PennsylvaniaCertificate::class,
            'template' => 'pennsylvania.pdf',
            'name' => 'Pennsylvania',
        ],
        'RI' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\RhodeIslandCertificate::class,
            'template' => 'rhode_island.pdf',
            'name' => 'Rhode Island',
        ],
        'SC' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SouthCarolinaCertificate::class,
            'template' => 'south_carolina.pdf',
            'name' => 'South Carolina',
        ],
        'SD' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SstUniformCertificate::class,
            'template' => 'sst.pdf',
            'name' => 'South Dakota',
        ],
        'TN' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SstUniformCertificate::class,
            'template' => 'sst.pdf',
            'name' => 'Tennessee',
            'state_issued' => [
                'document' => 'Blanket Certificate of Resale',
                'issuer' => 'Tennessee Department of Revenue',
                'url' => 'https://tntap.tn.gov/eservices/',
                'guidance' => 'Tennessee issues a Blanket Certificate of Resale to registered dealers through TNTAP (More... > View Letters). The Streamlined Sales Tax certificate is an accepted alternative, and it is the form we generate.',
                'registered_buyers' => 'note',
            ],
        ],
        'TX' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\TexasCertificate::class,
            'template' => 'texas.pdf',
            'name' => 'Texas',
        ],
        'UT' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\UtahCertificate::class,
            'template' => 'utah.pdf',
            'name' => 'Utah',
        ],
        'VT' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\VermontCertificate::class,
            'template' => 'vermont.pdf',
            'name' => 'Vermont',
        ],
        'VA' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\VirginiaCertificate::class,
            'template' => 'virginia.pdf',
            'name' => 'Virginia',
        ],
        'WA' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SstUniformCertificate::class,
            'template' => 'sst.pdf',
            'name' => 'Washington',
            'state_issued' => [
                'document' => 'Reseller Permit',
                'issuer' => 'Washington State Department of Revenue',
                'url' => 'https://secure.dor.wa.gov/',
                'guidance' => 'Washington issues a Reseller Permit to registered businesses. Apply for yours in My DOR and give suppliers a copy.',
                'registered_buyers' => 'blocked',
            ],
        ],
        'WV' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SstUniformCertificate::class,
            'template' => 'sst.pdf',
            'name' => 'West Virginia',
        ],
        'WI' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\WisconsinCertificate::class,
            'template' => 'wisconsin.pdf',
            'name' => 'Wisconsin',
        ],
        'WY' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SstUniformCertificate::class,
            'template' => 'sst.pdf',
            'name' => 'Wyoming',
        ],

        /*
        |--------------------------------------------------------------------------
        | Uniform Certificates
        |--------------------------------------------------------------------------
        |
        | Multi-state uniform certificates that can be used across multiple states.
        |
        */
        'MTC' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MtcUniformCertificate::class,
            'template' => 'mtc.pdf',
            'name' => 'MTC Uniform',
        ],
        'SST' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\SstUniformCertificate::class,
            'template' => 'sst.pdf',
            'name' => 'SST Uniform',
        ],
    ],
];
