<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
        ]);

        // Lien domain seeders
        $this->call([
            LienDocumentTypeSeeder::class,
            LienDocumentTemplateSeeder::class,
            LienStateRuleSeeder::class,
            LienDeadlineRuleSeeder::class,
        ]);

        // Billing / Pricing seeders
        $this->call([
            PriceSeeder::class,
            FormationFeeSeeder::class,
            ResaleCertPriceSeeder::class,
            LienWaiverPriceSeeder::class,
        ]);

        // Resale certificate reference data
        $this->call([
            ResaleStateRuleSeeder::class,
        ]);

        // Admin permissions and roles
        $this->call([
            PermissionsSeeder::class,
            AssignUserRolesSeeder::class,
        ]);
    }
}
