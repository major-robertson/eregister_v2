<?php

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    'tx_franchise_tax_id' => [
                        'type' => 'text',
                        'label' => 'Texas Franchise Tax ID (if any)',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'help' => 'Leave blank if you do not have a franchise tax ID yet.',
                    ],
                    'tx_sos_file_number' => [
                        'type' => 'text',
                        'label' => 'TX Secretary of State File Number',
                        'rules' => ['nullable', 'string', 'max:20'],
                    ],
                    'tx_naics_code' => [
                        'type' => 'text',
                        'label' => 'NAICS Code',
                        'rules' => ['required', 'string', 'max:10'],
                        'help' => 'Enter your 6-digit NAICS code.',
                    ],
                ],
            ],
        ],
        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'tx_driver_license' => [
                                'type' => 'text',
                                'label' => 'Texas Driver License #',
                                'rules' => ['required', 'string', 'max:20'],
                                'sensitive' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
