<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LienStateRuleSeeder extends Seeder
{
    private static ?array $cachedRows = null;

    public function run(): void
    {
        DB::table('lien_state_rules')->delete();

        $rows = self::loadRows();
        $now = now()->toDateTimeString();

        $insert = [];
        foreach ($rows as $row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            $insert[] = $row;
        }

        DB::table('lien_state_rules')->insert($insert);
    }

    private static function loadRows(): array
    {
        if (self::$cachedRows === null) {
            $jsonPath = database_path('seeders/data/lien_state_rules.json');
            self::$cachedRows = json_decode(file_get_contents($jsonPath), true);
        }

        return self::$cachedRows;
    }
}
