<?php

return [
    'key' => 'llc',
    'version' => 1,

    'core_steps' => [
        'llc_info' => [
            'title' => 'LLC Information',
            'description' => 'Basic information about your LLC.',
            'fields' => [
                'llc_name' => [
                    'type' => 'text',
                    'label' => 'LLC Name',
                    'rules' => ['required', 'string', 'max:120'],
                    'help' => 'The full legal name of your LLC, including "LLC" or "Limited Liability Company".',
                    'persist_to_business' => true,
                ],
                'purpose' => [
                    'type' => 'textarea',
                    'label' => 'Business Purpose',
                    'rules' => ['required', 'string', 'max:500'],
                    'help' => 'Describe the primary purpose or activities of your LLC.',
                ],
                'formation_date' => [
                    'type' => 'date',
                    'label' => 'Desired Formation Date',
                    'rules' => ['required', 'date', 'after_or_equal:today'],
                ],
                'management_type' => [
                    'type' => 'select',
                    'label' => 'Management Structure',
                    'options' => [
                        'member_managed' => 'Member-Managed (members run the business)',
                        'manager_managed' => 'Manager-Managed (designated managers run the business)',
                    ],
                    'rules' => ['required'],
                    'drives_conditional' => true,
                ],
            ],
        ],
        'principal_address' => [
            'title' => 'Principal Address',
            'description' => 'The main business address for your LLC.',
            'fields' => [
                'principal_address' => [
                    'type' => 'address',
                    'label' => 'Principal Business Address',
                    'rules' => ['required'],
                    'persist_to_business' => true,
                ],
            ],
        ],
        'members' => [
            'title' => 'Members & Managers',
            'description' => 'Add all members of the LLC. Members are the owners of an LLC.',
            'fields' => [
                'members' => [
                    'type' => 'repeater',
                    'label' => 'Members',
                    'min' => 1,
                    'item_label' => 'Member',
                    'schema' => [
                        'full_name' => [
                            'type' => 'text',
                            'label' => 'Full Name',
                            'rules' => ['required', 'string', 'max:120'],
                            'persist_to_business' => true,
                        ],
                        'email' => [
                            'type' => 'email',
                            'label' => 'Email Address',
                            'rules' => ['required', 'email', 'max:255'],
                            'persist_to_business' => true,
                        ],
                        'address' => [
                            'type' => 'address',
                            'label' => 'Member Address',
                            'rules' => ['required'],
                        ],
                        'ownership_percent' => [
                            'type' => 'percent',
                            'label' => 'Ownership %',
                            'rules' => ['required', 'numeric', 'min:0', 'max:100'],
                            'persist_to_business' => true,
                        ],
                        'is_manager' => [
                            'type' => 'checkbox',
                            'label' => 'Is a Manager',
                            'when' => ['==' => [['var' => 'management_type'], 'manager_managed']],
                        ],
                    ],
                    'rules' => ['required', 'array', 'min:1'],
                ],
            ],
            'cross_validations' => [
                [
                    'rule' => 'ownership_totals_100',
                    'field' => 'members',
                    'phase' => 'core',
                ],
            ],
        ],
        'registered_agent' => [
            'title' => 'Registered Agent',
            'description' => 'A registered agent is required to receive legal documents on behalf of your LLC.',
            'fields' => [
                'agent_type' => [
                    'type' => 'select',
                    'label' => 'Registered Agent',
                    'options' => [
                        'self' => 'I will be my own registered agent',
                        'member' => 'Use a member as registered agent',
                        'service' => 'Use a registered agent service',
                    ],
                    'rules' => ['required'],
                    'drives_conditional' => true,
                ],
                'agent_member_index' => [
                    'type' => 'select',
                    'label' => 'Select Member',
                    'options' => [], // Populated dynamically based on members
                    'rules' => ['required'],
                    'when' => ['==' => [['var' => 'agent_type'], 'member']],
                ],
                'agent_name' => [
                    'type' => 'text',
                    'label' => 'Agent Name or Company',
                    'rules' => ['required', 'string', 'max:120'],
                    'when' => ['==' => [['var' => 'agent_type'], 'service']],
                ],
                'agent_address' => [
                    'type' => 'address',
                    'label' => 'Agent Address',
                    'rules' => ['required'],
                    'help' => 'Must be a physical address in the state of formation (no P.O. boxes).',
                    'when' => ['in' => [['var' => 'agent_type'], ['self', 'service']]],
                ],
            ],
        ],
    ],

    'state_steps' => [
        'state_requirements' => [
            'title' => '{state_name} Requirements',
            'description' => 'Additional requirements for forming an LLC in {state_name}.',
            'fields' => [
                // State-specific fields can be added in state override files (e.g., DE.php, WY.php)
            ],
        ],
    ],

    'available_states' => array_keys(config('states')),
];
