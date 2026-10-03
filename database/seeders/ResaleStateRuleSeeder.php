<?php

namespace Database\Seeders;

use App\Domains\ResaleCert\Models\ResaleStateRule;
use Illuminate\Database\Seeder;

/**
 * Reference data for the resale certificate generator, first ported from the
 * original TaxResaleCertificate app's StateRulesSeeder. Safe to re-run
 * (updateOrCreate keyed on state_code).
 *
 * The rows were corrected on 2026-10-03 from the state agencies' current
 * guidance (EREG-90). This array is the source of truth: on an existing
 * database, `php artisan resale:sync-state-rules` shows the differences and
 * `--apply` writes them.
 */
class ResaleStateRuleSeeder extends Seeder
{
    /** Columns every row carries besides state_code, in the order the sync command compares them. */
    public const FIELDS = [
        'state_name',
        'accepts_mtc',
        'accepts_sst',
        'accepts_out_of_state',
        'allows_blanket',
        'default_blanket_text',
        'expiration_months',
        'metadata',
    ];

    public function run(): void
    {
        foreach (self::rows() as $row) {
            ResaleStateRule::updateOrCreate(
                ['state_code' => $row['state_code']],
                $row
            );
        }
    }

    /**
     * Every rule row: the 50 states and DC that have one, plus the MTC and
     * SST uniform pseudo-states. Each row carries every column (metadata
     * defaults to null), so applying a row also clears metadata dropped here.
     *
     * @return list<array{state_code: string, state_name: string, accepts_mtc: bool, accepts_sst: bool, accepts_out_of_state: bool, allows_blanket: bool, default_blanket_text: ?string, expiration_months: ?int, metadata: ?array<string, mixed>}>
     */
    public static function rows(): array
    {
        $states = [
            ['state_code' => 'AL', 'state_name' => 'Alabama', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All purchases of tangible personal property for resale', 'expiration_months' => 12, 'metadata' => ['expiration_type' => 'end_of_year']],
            ['state_code' => 'AZ', 'state_name' => 'Arizona', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null, 'metadata' => ['note' => 'Period stated on the form, 12 months suggested.']],
            // No expiry while purchases are no more than 12 months apart.
            ['state_code' => 'AR', 'state_name' => 'Arkansas', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All purchases of tangible personal property and taxable services', 'expiration_months' => null],
            ['state_code' => 'CA', 'state_name' => 'California', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null],
            ['state_code' => 'CO', 'state_name' => 'Colorado', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All purchases of tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'CT', 'state_name' => 'Connecticut', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 36],
            ['state_code' => 'DC', 'state_name' => 'District of Columbia', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 12],
            ['state_code' => 'FL', 'state_name' => 'Florida', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => 12, 'metadata' => ['expiration_type' => 'end_of_year']],
            ['state_code' => 'GA', 'state_name' => 'Georgia', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'HI', 'state_name' => 'Hawaii', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'ID', 'state_name' => 'Idaho', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            // The research says "limited"; Illinois stays closed to out-of-state numbers.
            ['state_code' => 'IL', 'state_name' => 'Illinois', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => 36],
            ['state_code' => 'IN', 'state_name' => 'Indiana', 'accepts_mtc' => false, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            // Valid until cancelled; lapses after 12 months without a purchase.
            ['state_code' => 'IA', 'state_name' => 'Iowa', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'KS', 'state_name' => 'Kansas', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'KY', 'state_name' => 'Kentucky', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'LA', 'state_name' => 'Louisiana', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => 12],
            // The state-issued certificate expires December 31 of its final year.
            ['state_code' => 'ME', 'state_name' => 'Maine', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null, 'metadata' => ['note' => 'MTC certificate accepted from nonresidents only.']],
            ['state_code' => 'MD', 'state_name' => 'Maryland', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'MA', 'state_name' => 'Massachusetts', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null],
            ['state_code' => 'MI', 'state_name' => 'Michigan', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 48],
            ['state_code' => 'MN', 'state_name' => 'Minnesota', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'MS', 'state_name' => 'Mississippi', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => false, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'MO', 'state_name' => 'Missouri', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 60],
            ['state_code' => 'NE', 'state_name' => 'Nebraska', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NV', 'state_name' => 'Nevada', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NJ', 'state_name' => 'New Jersey', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NM', 'state_name' => 'New Mexico', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'NY', 'state_name' => 'New York', 'accepts_mtc' => false, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property purchased for resale', 'expiration_months' => null],
            ['state_code' => 'NC', 'state_name' => 'North Carolina', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'ND', 'state_name' => 'North Dakota', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 24],
            ['state_code' => 'OH', 'state_name' => 'Ohio', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            // Tied to the buyer's sales tax permit.
            ['state_code' => 'OK', 'state_name' => 'Oklahoma', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 36],
            ['state_code' => 'PA', 'state_name' => 'Pennsylvania', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'RI', 'state_name' => 'Rhode Island', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'SC', 'state_name' => 'South Carolina', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'SD', 'state_name' => 'South Dakota', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'TN', 'state_name' => 'Tennessee', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'TX', 'state_name' => 'Texas', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All taxable items purchased for resale', 'expiration_months' => null],
            ['state_code' => 'UT', 'state_name' => 'Utah', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'VT', 'state_name' => 'Vermont', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null, 'metadata' => ['note' => 'Limited: see state page.']],
            ['state_code' => 'VA', 'state_name' => 'Virginia', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'WA', 'state_name' => 'Washington', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => 48, 'metadata' => ['note' => 'Some reseller permits are issued for 24 months.']],
            ['state_code' => 'WV', 'state_name' => 'West Virginia', 'accepts_mtc' => false, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'WI', 'state_name' => 'Wisconsin', 'accepts_mtc' => true, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            ['state_code' => 'WY', 'state_name' => 'Wyoming', 'accepts_mtc' => false, 'accepts_sst' => true, 'accepts_out_of_state' => true, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property for resale', 'expiration_months' => null],
            // Alaska - no state sales tax but some municipalities collect
            ['state_code' => 'AK', 'state_name' => 'Alaska', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => null, 'expiration_months' => 12, 'metadata' => ['expiration_type' => 'end_of_year']],

            // MTC and SST uniform certificates
            ['state_code' => 'MTC', 'state_name' => 'Multistate Tax Commission Uniform Certificate', 'accepts_mtc' => true, 'accepts_sst' => false, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All purchases of tangible personal property or services', 'expiration_months' => null],
            ['state_code' => 'SST', 'state_name' => 'Streamlined Sales Tax Certificate', 'accepts_mtc' => false, 'accepts_sst' => true, 'accepts_out_of_state' => false, 'allows_blanket' => true, 'default_blanket_text' => 'All tangible personal property, digital goods, or services', 'expiration_months' => null],
        ];

        return array_map(fn (array $row) => $row + ['metadata' => null], $states);
    }
}
