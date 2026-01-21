<?php

namespace Database\Seeders;

use App\Domains\Lien\Models\LienDeadlineRule;
use App\Domains\Lien\Models\LienDocumentType;
use Illuminate\Database\Seeder;

class LienDeadlineRuleSeeder extends Seeder
{
    /**
     * All 50 US states.
     */
    private const STATES = [
        'AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'FL', 'GA',
        'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD',
        'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ',
        'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC',
        'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY',
    ];

    /**
     * States with verified rules (not placeholders).
     * Add states here as rules are verified.
     */
    private const VERIFIED_STATES = [
        'CA', // California - well documented lien laws
    ];

    public function run(): void
    {
        $prelimNotice = LienDocumentType::where('slug', 'prelim_notice')->first();
        $noi = LienDocumentType::where('slug', 'noi')->first();
        $mechanicsLien = LienDocumentType::where('slug', 'mechanics_lien')->first();
        $lienRelease = LienDocumentType::where('slug', 'lien_release')->first();

        if (! $prelimNotice || ! $noi || ! $mechanicsLien || ! $lienRelease) {
            $this->command->error('Document types not found. Run LienDocumentTypeSeeder first.');

            return;
        }

        foreach (self::STATES as $state) {
            $isPlaceholder = ! in_array($state, self::VERIFIED_STATES, true);
            $stateRules = $this->getRulesForState($state);

            // Preliminary Notice
            LienDeadlineRule::updateOrCreate(
                [
                    'state' => $state,
                    'document_type_id' => $prelimNotice->id,
                    'claimant_type' => null,
                ],
                [
                    'trigger_event' => $stateRules['prelim_notice']['trigger'],
                    'offset_days' => $stateRules['prelim_notice']['offset'],
                    'is_required' => $stateRules['prelim_notice']['required'],
                    'is_placeholder' => $isPlaceholder,
                    'notes' => $stateRules['prelim_notice']['notes'] ?? null,
                ]
            );

            // Notice of Intent
            LienDeadlineRule::updateOrCreate(
                [
                    'state' => $state,
                    'document_type_id' => $noi->id,
                    'claimant_type' => null,
                ],
                [
                    'trigger_event' => $stateRules['noi']['trigger'],
                    'offset_days' => $stateRules['noi']['offset'],
                    'is_required' => $stateRules['noi']['required'],
                    'is_placeholder' => $isPlaceholder,
                    'notes' => $stateRules['noi']['notes'] ?? null,
                ]
            );

            // Mechanics Lien
            LienDeadlineRule::updateOrCreate(
                [
                    'state' => $state,
                    'document_type_id' => $mechanicsLien->id,
                    'claimant_type' => null,
                ],
                [
                    'trigger_event' => $stateRules['mechanics_lien']['trigger'],
                    'offset_days' => $stateRules['mechanics_lien']['offset'],
                    'is_required' => true,
                    'is_placeholder' => $isPlaceholder,
                    'notes' => $stateRules['mechanics_lien']['notes'] ?? null,
                ]
            );

            // Lien Release - typically no deadline, just after payment
            LienDeadlineRule::updateOrCreate(
                [
                    'state' => $state,
                    'document_type_id' => $lienRelease->id,
                    'claimant_type' => null,
                ],
                [
                    'trigger_event' => 'last_furnish_date',
                    'offset_days' => 365, // Placeholder - file when paid
                    'is_required' => false,
                    'is_placeholder' => true,
                    'notes' => 'File after payment received. No specific deadline.',
                ]
            );
        }

        $this->command->info('Seeded deadline rules for '.count(self::STATES).' states.');
    }

    /**
     * Get state-specific rules. Returns placeholder defaults for most states.
     * Add verified state rules as they are researched.
     */
    private function getRulesForState(string $state): array
    {
        // Verified state rules
        $verifiedRules = [
            'CA' => [
                'prelim_notice' => [
                    'trigger' => 'first_furnish_date',
                    'offset' => 20,
                    'required' => true,
                    'notes' => 'California Preliminary Notice required within 20 days of first furnishing for subs/suppliers.',
                ],
                'noi' => [
                    'trigger' => 'last_furnish_date',
                    'offset' => 60,
                    'required' => false,
                    'notes' => 'Optional notice of intent, commonly sent 10 days before lien filing.',
                ],
                'mechanics_lien' => [
                    'trigger' => 'last_furnish_date',
                    'offset' => 90,
                    'required' => true,
                    'notes' => 'Must file within 90 days of completion. If NOC recorded, deadline shortens to 30 days for subs.',
                ],
            ],
        ];

        if (isset($verifiedRules[$state])) {
            return $verifiedRules[$state];
        }

        // Default placeholder rules for unverified states
        return [
            'prelim_notice' => [
                'trigger' => 'first_furnish_date',
                'offset' => 30,
                'required' => true,
                'notes' => 'Placeholder: Verify actual requirements for this state.',
            ],
            'noi' => [
                'trigger' => 'last_furnish_date',
                'offset' => 60,
                'required' => false,
                'notes' => 'Placeholder: Verify if NOI is required or recommended.',
            ],
            'mechanics_lien' => [
                'trigger' => 'last_furnish_date',
                'offset' => 90,
                'required' => true,
                'notes' => 'Placeholder: Verify actual filing deadline for this state.',
            ],
        ];
    }
}
