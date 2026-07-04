<?php

namespace Database\Seeders;

use App\Domains\ResaleCert\Models\ResaleStateRule;
use Illuminate\Database\Seeder;

/**
 * Reference data for the resale certificate generator, ported verbatim from
 * the original TaxResaleCertificate app's StateRulesSeeder. Safe to re-run
 * (updateOrCreate keyed on state_code).
 */
class ResaleStateRuleSeeder extends Seeder
{
    public function run(): void
    {
        $states = [
            ['state_code' => 'AL', 'state_name' => 'Alabama', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All purchases of tangible personal property for resale', 'expiration_months' => 12, 'metadata' => ['expiration_type' => 'end_of_year']],
            ['state_code' => 'AZ', 'state_name' => 'Arizona', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null],
            ['state_code' => 'AR', 'state_name' => 'Arkansas', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All purchases of tangible personal property and taxable services', 'expiration_months' => 12, 'metadata' => ['expiration_type' => 'end_of_year']],
            ['state_code' => 'CA', 'state_name' => 'California', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null],
            ['state_code' => 'CO', 'state_name' => 'Colorado', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All purchases of tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'CT', 'state_name' => 'Connecticut', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 36],
            ['state_code' => 'DC', 'state_name' => 'District of Columbia', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'FL', 'state_name' => 'Florida', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => 12, 'metadata' => ['expiration_type' => 'end_of_year']],
            ['state_code' => 'GA', 'state_name' => 'Georgia', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'HI', 'state_name' => 'Hawaii', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'ID', 'state_name' => 'Idaho', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'IL', 'state_name' => 'Illinois', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => 36],
            ['state_code' => 'IN', 'state_name' => 'Indiana', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'IA', 'state_name' => 'Iowa', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 36],
            ['state_code' => 'KS', 'state_name' => 'Kansas', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'KY', 'state_name' => 'Kentucky', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'LA', 'state_name' => 'Louisiana', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => 24],
            ['state_code' => 'ME', 'state_name' => 'Maine', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 28, 'metadata' => ['expiration_type' => 'end_of_year']],
            ['state_code' => 'MD', 'state_name' => 'Maryland', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'MA', 'state_name' => 'Massachusetts', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null],
            ['state_code' => 'MI', 'state_name' => 'Michigan', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 48],
            ['state_code' => 'MN', 'state_name' => 'Minnesota', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'MS', 'state_name' => 'Mississippi', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'MO', 'state_name' => 'Missouri', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 60],
            ['state_code' => 'NE', 'state_name' => 'Nebraska', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NV', 'state_name' => 'Nevada', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NJ', 'state_name' => 'New Jersey', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NM', 'state_name' => 'New Mexico', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NY', 'state_name' => 'New York', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null],
            ['state_code' => 'NC', 'state_name' => 'North Carolina', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'ND', 'state_name' => 'North Dakota', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 24],
            ['state_code' => 'OH', 'state_name' => 'Ohio', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'OK', 'state_name' => 'Oklahoma', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'PA', 'state_name' => 'Pennsylvania', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'RI', 'state_name' => 'Rhode Island', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'SC', 'state_name' => 'South Carolina', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'SD', 'state_name' => 'South Dakota', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'TN', 'state_name' => 'Tennessee', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'TX', 'state_name' => 'Texas', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All taxable items purchased for resale', 'expiration_months' => null],
            ['state_code' => 'UT', 'state_name' => 'Utah', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'VT', 'state_name' => 'Vermont', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'VA', 'state_name' => 'Virginia', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'WA', 'state_name' => 'Washington', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 24],
            ['state_code' => 'WV', 'state_name' => 'West Virginia', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'WI', 'state_name' => 'Wisconsin', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'WY', 'state_name' => 'Wyoming', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            // Alaska - no state sales tax but some municipalities collect
            ['state_code' => 'AK', 'state_name' => 'Alaska', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => false, 'default_blanket_text' => null, 'expiration_months' => null],
        ];

        foreach ($states as $state) {
            ResaleStateRule::updateOrCreate(
                ['state_code' => $state['state_code']],
                $state
            );
        }

        // Add MTC and SST uniform certificates
        ResaleStateRule::updateOrCreate(
            ['state_code' => 'MTC'],
            [
                'state_name' => 'Multistate Tax Commission Uniform Certificate',
                'accepts_mtc' => true,
                'accepts_sst' => false,
                'accepts_out_of_state' => false,
                'allows_blanket' => true,
                'default_blanket_text' => 'All purchases of tangible personal property or services',
                'expiration_months' => null,
            ]
        );

        ResaleStateRule::updateOrCreate(
            ['state_code' => 'SST'],
            [
                'state_name' => 'Streamlined Sales Tax Certificate',
                'accepts_mtc' => false,
                'accepts_sst' => true,
                'accepts_out_of_state' => false,
                'allows_blanket' => true,
                'default_blanket_text' => 'All tangible personal property, digital goods, or services',
                'expiration_months' => null,
            ]
        );
    }
}
