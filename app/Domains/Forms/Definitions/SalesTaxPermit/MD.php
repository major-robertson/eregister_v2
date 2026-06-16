<?php

/**
 * Maryland — Sales Tax Permit overrides (v3 clean rebuild).
 *
 * Authoritative source: TaxResaleCertificate
 * `resources/views/states/maryland/application/`.
 *
 * Collapsed into core: business fax (core), wages gate (applies_employees
 * _or_payroll), first wages date + employee count (matrix), acquisition
 * gate + predecessor identity/date (core + matrix), multiple locations
 * (derived from locations[]), county selects (locations[] / address).
 *
 * §3A.2 fixes applied: reasonsForApplying[] and typeOfBusinessOverview
 * option lists restored to the legacy values.
 */
$mdGate = fn (string $appliesField) => ['contains' => [['var' => '$root.'.$appliesField.'.states'], 'MD']];

$mdReasons = [
    'md_reason_new_business' => 'New Business',
    'md_reason_reorganization' => 'Reorganization',
    'md_reason_employs_domestic_help' => 'Employs Domestic Help',
    'md_reason_merger' => 'Merger',
    'md_reason_agricultural_operation' => 'Agricultural Operation',
    'md_reason_change_of_entity' => 'Change of Entity',
    'md_reason_purchased_going_business' => 'Purchased Going Business',
    'md_reason_peo' => 'Professional Employer Organization',
    'md_reason_reopen_reactivate' => 'Reopen/Reactivate',
];

$mdReasonFields = [];
foreach ($mdReasons as $key => $label) {
    $mdReasonFields[$key] = [
        'type' => 'checkbox',
        'label' => $label,
        'source_name' => 'reasonsForApplying[]',
        'source_value' => $label,
    ];
}

return [
    'extends' => 'base',

    'state_steps' => [
        'state_details' => [
            'title' => 'Maryland Sales Tax Permit Details',
            'description' => 'Maryland Combined Registration questions.',
            'groups' => [
                ['title' => 'MD Identifiers', 'fields' => [
                    'md_primary_id_type', 'md_business_taxpayer_id', 'md_da_ein',
                    'md_owner_name', 'md_owner_ssn', 'md_llc_classified_as_corp',
                ]],
                ['title' => 'Reasons for Applying', 'fields' => array_keys($mdReasonFields)],
                ['title' => 'Business Overview', 'fields' => [
                    'md_type_of_business_overview', 'md_type_of_business_detail', 'md_your_situation',
                ]],
                ['title' => 'Operations', 'fields' => [
                    'md_primarily_provide_support', 'md_type_of_service_provided',
                ]],
                ['title' => 'Employer', 'fields' => [
                    'md_sole_prop_employ_under_21', 'md_partnership_employ_anyone',
                    'md_llc_employ_other_members',
                ]],
                ['title' => 'Acquisition (MD detail)', 'fields' => [
                    'md_common_ownership_management', 'md_percent_acquired',
                    'md_prior_unemployment_insurance_number',
                ]],
            ],
            'fields' => array_merge(
                [
                    'md_primary_id_type' => [
                        'type' => 'radio',
                        'label' => 'ID Type',
                        'options' => ['ssn' => 'SSN', 'ein' => 'EIN'],
                        'rules' => ['required', 'in:ssn,ein'],
                        'source_name' => 'primaryContactIdType',
                    ],
                    'md_business_taxpayer_id' => [
                        'type' => 'text',
                        'label' => 'Maryland Business Taxpayer ID',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'source_name' => 'businessTaxpayerId',
                    ],
                    'md_da_ein' => [
                        'type' => 'text',
                        'label' => 'Department of Assessment & Taxation Entity ID (optional)',
                        'rules' => ['nullable', 'string', 'max:20'],
                        'source_name' => 'DAEIN',
                    ],
                    'md_owner_name' => [
                        'type' => 'text',
                        'label' => 'Owner Legal Name (whose SSN is on the application)',
                        'rules' => ['nullable', 'string', 'max:120'],
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'source_name' => 'ownerName',
                    ],
                    'md_owner_ssn' => [
                        'type' => 'text',
                        'label' => 'Owner SSN',
                        'rules' => ['nullable', 'regex:/^\d{3}-?\d{2}-?\d{4}$/'],
                        'placeholder' => '123-45-6789',
                        'mask' => '999-99-9999',
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                        'sensitive' => true,
                        'source_name' => 'ownerSSN',
                    ],
                    'md_llc_classified_as_corp' => nullableYesNoField('Is the LLC automatically classified as a corporation for federal tax purposes?', 'llcAsClassifiedAsCorp', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi']]],
                    ]),
                ],
                $mdReasonFields,
                [
                    // §3A.2.3: legacy option lists restored.
                    'md_type_of_business_overview' => [
                        'type' => 'select',
                        'label' => 'Which of the following best describes your type of business operation?',
                        'options' => [
                            'food_and_beverage' => 'Food and Beverage',
                            'apparel' => 'Apparel',
                            'general_merchandise' => 'General Merchandise',
                            'automotive' => 'Automotive',
                            'furniture_fixtures_appliances' => 'Furniture/Fixtures/Appliances',
                            'building_and_contractors' => 'Building and Contractors',
                            'utilities_and_transportation' => 'Utilities and Transportation',
                            'hardware_machinery_equipment' => 'Hardware/Machinery/Equipment',
                            'miscellaneous' => 'Miscellaneous',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'typeOfBusinessOverview',
                    ],
                    'md_type_of_business_detail' => [
                        'type' => 'text',
                        'label' => 'Further indicate the type of business (detail within the category)',
                        'rules' => ['required', 'string', 'max:120'],
                        'source_name' => 'typeOfBusinessDetail',
                    ],
                    'md_your_situation' => [
                        'type' => 'select',
                        'label' => 'Select the option that best describes your situation',
                        'options' => [
                            'starting_new' => 'I am starting a new business',
                            'opened_recently' => 'I recently opened my business',
                            'existing_no_changes' => 'I have an existing business and nothing has changed',
                            'existing_with_changes' => 'I have an existing business with changes (location, ownership)',
                        ],
                        'rules' => ['required'],
                        'source_name' => 'yourSituration',
                    ],
                    'md_primarily_provide_support' => yesNoField('Does the location primarily provide support services?', 'primarilyProvideSupport', ['drives_conditional' => true]),
                    'md_type_of_service_provided' => [
                        'type' => 'select',
                        'label' => 'Type of Service Provided',
                        'options' => [
                            'central_admin' => 'Central Administrative Office',
                            'warehouse' => 'Warehouse (Storage)',
                            'research' => 'Research/Development/Testing Laboratories',
                            'other' => 'Other',
                        ],
                        'rules' => ['nullable'],
                        'when' => ['==' => [['var' => 'md_primarily_provide_support'], '1']],
                        'source_name' => 'typeOfServiceProvided',
                    ],
                    'md_sole_prop_employ_under_21' => nullableYesNoField('As a sole proprietor, do you employ anyone other than your spouse or a child under 21?', 'solePropEmployUnder21', [
                        'when' => ['==' => [['var' => '$root.entity_type'], 'sole_prop']],
                    ]),
                    'md_partnership_employ_anyone' => nullableYesNoField('As a partnership, do you employ anyone other than a partner?', 'partnershipEmployAnyone', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['general_partnership', 'limited_partnership', 'llp']]],
                    ]),
                    'md_llc_employ_other_members' => nullableYesNoField('As an LLC, do you employ anyone other than a member?', 'llcEmployOtherMembers', [
                        'when' => ['in' => [['var' => '$root.entity_type'], ['llc_single', 'llc_multi']]],
                    ]),
                    'md_common_ownership_management' => nullableYesNoField('Is there any common ownership, management, or control with the previous business?', 'commonOwnershipManagement', [
                        'when' => $mdGate('applies_purchased_or_acquired_business'),
                    ]),
                    'md_percent_acquired' => [
                        'type' => 'percent',
                        'label' => 'Percentage of assets or workforce acquired',
                        'rules' => ['nullable', 'numeric', 'min:0', 'max:100'],
                        'when' => $mdGate('applies_purchased_or_acquired_business'),
                        'source_name' => 'percentAcquired',
                    ],
                    'md_prior_unemployment_insurance_number' => [
                        'type' => 'text',
                        'label' => 'Unemployment insurance number of the former business (optional)',
                        'rules' => ['nullable', 'string', 'max:30'],
                        'when' => $mdGate('applies_purchased_or_acquired_business'),
                        'source_name' => 'priorUnemploymentInsuranceNumber',
                    ],
                ],
            ),
        ],
    ],
];
