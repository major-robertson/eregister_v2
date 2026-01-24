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
            LienDeadlineRuleSeeder::class,
        ]);

        // Billing / Pricing seeders
        $this->call([
            PriceSeeder::class,
        ]);
    }
}
