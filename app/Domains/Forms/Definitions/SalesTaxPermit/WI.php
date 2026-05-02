<?php

/**
 * Wisconsin — Sales Tax Permit overrides.
 *
 * Ported from TaxResaleCertificate `resources/views/states/standard/application/wisconsin.blade.php`
 * + matching `public/js/states/standard/wisconsin.js`.
 */
return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'groups' => ['append' => [
                ['title' => 'Banking', 'fields' => [
                    'wi_does_business_have_bank_account', 'wi_business_account_routing_number',
                ]],
                ['title' => 'Tourism Zones / Auto / Lodging', 'fields' => [
                    'wi_sell_food_auto_or_lodging', 'wi_provide_automobile_rentals',
                    'wi_provide_lodging', 'wi_sell_food_and_beverages',
                ]],
                ['title' => 'Vehicle Rental & Services', 'fields' => [
                    'wi_short_term_vehicle_rental', 'wi_provide_limousine_service',
                    'wi_provide_dry_cleaning_services', 'wi_sell_dry_cleaning_products',
                    'wi_sell_voice_communication_services',
                ]],
                ['title' => 'Premier Resort & Local License', 'fields' => [
                    'wi_premier_resort_tax', 'wi_apply_for_local_license',
                ]],
            ]],
            'fields' => [
                'append' => [
                    'wi_does_business_have_bank_account' => yesNoField('Does the business have a bank account?', 'doesBusinessHaveBankAccount', ['drives_conditional' => true]),
                    'wi_business_account_routing_number' => [
                        'type' => 'text',
                        'label' => 'Business Account Routing Number',
                        'rules' => ['nullable', 'digits:9'],
                        'sensitive' => true,
                        'when' => ['==' => [['var' => 'wi_does_business_have_bank_account'], '1']],
                        'source_name' => 'businessAccountRoutingNumber',
                    ],
                    'wi_sell_food_auto_or_lodging' => yesNoField('Sell food, beverages, auto rentals, or lodging in Milwaukee County / WI tourism zones?', 'sellFoodAutomobileOrLodging', ['drives_conditional' => true]),
                    'wi_provide_automobile_rentals' => nullableYesNoField('Provide automobile rentals?', 'provideAutomobileRentals', [
                        'when' => ['==' => [['var' => 'wi_sell_food_auto_or_lodging'], '1']],
                    ]),
                    'wi_provide_lodging' => nullableYesNoField('Provide lodging?', 'provideLodging', [
                        'when' => ['==' => [['var' => 'wi_sell_food_auto_or_lodging'], '1']],
                    ]),
                    'wi_sell_food_and_beverages' => nullableYesNoField('Sell food and beverages?', 'sellFoodAndBeverages', [
                        'when' => ['==' => [['var' => 'wi_sell_food_auto_or_lodging'], '1']],
                    ]),
                    'wi_short_term_vehicle_rental' => yesNoField('Provide short-term vehicle rentals?', 'shortTermVehicleRental'),
                    'wi_provide_limousine_service' => yesNoField('Provide limousine service?', 'provideLimousineService'),
                    'wi_provide_dry_cleaning_services' => yesNoField('Provide dry cleaning services?', 'provideDryCleaningServices'),
                    'wi_sell_dry_cleaning_products' => yesNoField('Sell dry cleaning products?', 'sellDryCleaningProducts'),
                    'wi_sell_voice_communication_services' => yesNoField('Sell voice communications services?', 'sellVoiceCommunicationServices'),
                    'wi_premier_resort_tax' => yesNoField('Subject to Premier Resort Area Tax?', 'premierResortTax'),
                    'wi_apply_for_local_license' => yesNoField('Will you apply for a local license?', 'applyForLocalLicense'),
                ],
            ],
        ],
    ],
];
