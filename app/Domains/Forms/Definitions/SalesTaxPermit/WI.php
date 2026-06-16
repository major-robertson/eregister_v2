<?php

/**
 * Wisconsin — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/standard/application/wisconsin.blade.php`.
 *
 * Collapsed into core: bank account + routing (core bank_*), automobile/
 * short-term vehicle/limousine rentals (applies_vehicle_rentals), lodging
 * (applies_lodging_or_rentals), voice communications (applies_telecom_or_
 * prepaid_wireless).
 */
$wiTourismGate = ['==' => [['var' => 'wi_sell_food_auto_or_lodging'], '1']];

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'Wisconsin Sales Tax Permit Details',
            'description' => 'Wisconsin local tax and licensing questions.',
            'groups' => [
                ['title' => 'Milwaukee County / Tourism Zones', 'fields' => [
                    'wi_sell_food_auto_or_lodging', 'wi_sell_food_and_beverages',
                ]],
                ['title' => 'Dry Cleaning', 'fields' => [
                    'wi_provide_dry_cleaning_services', 'wi_sell_dry_cleaning_products',
                ]],
                ['title' => 'Local Taxes & Licensing', 'fields' => [
                    'wi_premier_resort_tax', 'wi_apply_for_local_license',
                ]],
            ],
            'fields' => [
                'wi_sell_food_auto_or_lodging' => yesNoField('Will you sell food and beverages, automobile rentals, or lodging in Milwaukee County or WI tourism zones?', 'sellFoodAutomobileOrLodging', ['drives_conditional' => true]),
                'wi_sell_food_and_beverages' => nullableYesNoField('Will you sell food and beverages?', 'sellFoodAndBeverages', [
                    'when' => $wiTourismGate,
                ]),
                'wi_provide_dry_cleaning_services' => yesNoField('Will you perform dry cleaning services?', 'provideDryCleaningServices'),
                'wi_sell_dry_cleaning_products' => yesNoField('Will you sell dry cleaning products?', 'sellDryCleaningProducts'),
                'wi_premier_resort_tax' => yesNoField('Will you sell items subject to the Premier Resort Area Tax?', 'premierResortTax'),
                'wi_apply_for_local_license' => yesNoField('Will you apply for a local license?', 'applyForLocalLicense'),
            ],
        ],
    ],
];
