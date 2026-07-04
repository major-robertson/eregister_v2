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
            'class' => \App\Domains\ResaleCert\Pdf\States\DistrictOfColumbiaCertificate::class,
            'template' => 'district_of_columbia.pdf',
            'name' => 'District of Columbia',
        ],
        'FL' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MtcUniformCertificate::class,
            'template' => 'mtc.pdf',
            'name' => 'Florida',
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
            'class' => \App\Domains\ResaleCert\Pdf\States\LouisianaCertificate::class,
            'template' => '',  // Uses custom generation
            'name' => 'Louisiana',
        ],
        'ME' => [
            'class' => \App\Domains\ResaleCert\Pdf\States\MtcUniformCertificate::class,
            'template' => 'mtc.pdf',
            'name' => 'Maine',
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
            'class' => \App\Domains\ResaleCert\Pdf\States\MississippiCertificate::class,
            'template' => 'mississippi.pdf',
            'name' => 'Mississippi',
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
            'class' => \App\Domains\ResaleCert\Pdf\States\TennesseeCertificate::class,
            'template' => 'tennessee.pdf',
            'name' => 'Tennessee',
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
