<?php

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'fields' => [
                'append' => [
                    'ca_seller_permit_number' => [
                        'type' => 'text',
                        'label' => 'Existing CA Seller Permit # (if any)',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'help' => 'Leave blank if you do not have an existing permit.',
                    ],
                    'ca_business_location_type' => [
                        'type' => 'select',
                        'label' => 'Type of Business Location',
                        'options' => [
                            'retail_storefront' => 'Retail Storefront',
                            'office' => 'Office',
                            'warehouse' => 'Warehouse',
                            'home_based' => 'Home-Based',
                            'online_only' => 'Online Only',
                        ],
                        'rules' => ['required'],
                    ],
                ],
            ],
        ],
        'state_responsible_people' => [
            'fields' => [
                'responsible_people_extra' => [
                    'schema' => [
                        'append' => [
                            'ca_driver_license' => [
                                'type' => 'text',
                                'label' => 'California Driver License #',
                                'rules' => ['required', 'string', 'max:20'],
                                'sensitive' => true,
                            ],
                            'ca_driver_license_exp' => [
                                'type' => 'date',
                                'label' => 'Driver License Expiration Date',
                                'rules' => ['required', 'date', 'after:today'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
