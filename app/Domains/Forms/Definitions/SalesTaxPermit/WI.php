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
            'fields' => [
                'append' => [
                    'wi_does_business_have_bank_account' => [
                        'type' => 'radio',
                        'label' => 'Does the business have a bank account?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'doesBusinessHaveBankAccount',
                    ],
                    'wi_business_account_routing_number' => [
                        'type' => 'text',
                        'label' => 'Business Account Routing Number',
                        'rules' => ['nullable', 'digits:9'],
                        'sensitive' => true,
                        'when' => ['==' => [['var' => 'wi_does_business_have_bank_account'], '1']],
                        'source_name' => 'businessAccountRoutingNumber',
                    ],
                    'wi_sell_food_auto_or_lodging' => [
                        'type' => 'radio',
                        'label' => 'Sell food, beverages, auto rentals, or lodging in Milwaukee County / WI tourism zones?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'drives_conditional' => true,
                        'source_name' => 'sellFoodAutomobileOrLodging',
                    ],
                    'wi_provide_automobile_rentals' => [
                        'type' => 'radio',
                        'label' => 'Provide automobile rentals?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'wi_sell_food_auto_or_lodging'], '1']],
                        'source_name' => 'provideAutomobileRentals',
                    ],
                    'wi_provide_lodging' => [
                        'type' => 'radio',
                        'label' => 'Provide lodging?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'wi_sell_food_auto_or_lodging'], '1']],
                        'source_name' => 'provideLodging',
                    ],
                    'wi_sell_food_and_beverages' => [
                        'type' => 'radio',
                        'label' => 'Sell food and beverages?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['nullable', 'in:0,1'],
                        'when' => ['==' => [['var' => 'wi_sell_food_auto_or_lodging'], '1']],
                        'source_name' => 'sellFoodAndBeverages',
                    ],
                    'wi_short_term_vehicle_rental' => [
                        'type' => 'radio',
                        'label' => 'Provide short-term vehicle rentals?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'shortTermVehicleRental',
                    ],
                    'wi_provide_limousine_service' => [
                        'type' => 'radio',
                        'label' => 'Provide limousine service?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'provideLimousineService',
                    ],
                    'wi_provide_dry_cleaning_services' => [
                        'type' => 'radio',
                        'label' => 'Provide dry cleaning services?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'provideDryCleaningServices',
                    ],
                    'wi_sell_dry_cleaning_products' => [
                        'type' => 'radio',
                        'label' => 'Sell dry cleaning products?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellDryCleaningProducts',
                    ],
                    'wi_sell_voice_communication_services' => [
                        'type' => 'radio',
                        'label' => 'Sell voice communications services?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'sellVoiceCommunicationServices',
                    ],
                    'wi_premier_resort_tax' => [
                        'type' => 'radio',
                        'label' => 'Subject to Premier Resort Area Tax?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'premierResortTax',
                    ],
                    'wi_apply_for_local_license' => [
                        'type' => 'radio',
                        'label' => 'Will you apply for a local license?',
                        'options' => ['1' => 'Yes', '0' => 'No'],
                        'rules' => ['required', 'in:0,1'],
                        'source_name' => 'applyForLocalLicense',
                    ],
                ],
            ],
        ],
    ],
];
