<?php

namespace Database\Seeders;

use App\Domains\Lien\Models\LienDocumentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LienDeadlineRuleSeeder extends Seeder
{
    private static ?array $cachedRows = null;

    public function run(): void
    {
        $docTypes = LienDocumentType::pluck('id', 'slug')->toArray();

        if (empty($docTypes)) {
            $this->command?->error('No document types found. Run LienDocumentTypeSeeder first.');

            return;
        }

        DB::table('lien_deadline_rules')->delete();

        $rows = self::loadRows();
        $now = now()->toDateTimeString();

        $chunk = [];

        foreach ($rows as $row) {
            $slug = $row['document_type_slug'];
            unset($row['document_type_slug']);
            $row['document_type_id'] = $docTypes[$slug];
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
            $chunk[] = $row;

            if (count($chunk) >= 100) {
                DB::table('lien_deadline_rules')->insert($chunk);
                $chunk = [];
            }
        }

        if (! empty($chunk)) {
            DB::table('lien_deadline_rules')->insert($chunk);
        }
    }

    private static function loadRows(): array
    {
        if (self::$cachedRows === null) {
            $jsonPath = database_path('seeders/data/lien_deadline_rules.json');
            self::$cachedRows = json_decode(file_get_contents($jsonPath), true);
        }

        return self::$cachedRows;
    }
}
