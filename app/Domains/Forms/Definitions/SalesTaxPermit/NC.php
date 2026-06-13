<?php

/**
 * North Carolina — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate standard flow — NC is served
 * by the standard blades, which add an NC business-county select
 * (entityQuestions) and a Secretary of State number when the registration
 * state is North Carolina (organizationInformation).
 */
$ncCounties = config('counties.NC', []);

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'North Carolina Sales Tax Permit Details',
            'description' => 'North Carolina registration details.',
            'groups' => [
                ['title' => 'North Carolina Details', 'fields' => [
                    'nc_business_county', 'nc_secretary_of_state_number',
                ]],
            ],
            'fields' => [
                'nc_business_county' => [
                    'type' => 'select',
                    'label' => 'County you will be doing business in',
                    'options' => array_combine($ncCounties, $ncCounties),
                    'rules' => ['required'],
                    'source_name' => 'northCarolinaBusinessCounty',
                ],
                'nc_secretary_of_state_number' => [
                    'type' => 'text',
                    'label' => 'NC Secretary of State Number',
                    'rules' => ['nullable', 'string', 'max:30'],
                    'when' => ['==' => [['var' => '$root.formation_state'], 'NC']],
                    'source_name' => 'secretaryOfStateNumber',
                ],
            ],
        ],
    ],
];
