<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TestReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
            LienDocumentTypeSeeder::class,
            LienStateRuleSeeder::class,
            LienDeadlineRuleSeeder::class,
        ]);
    }
}
