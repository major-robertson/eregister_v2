<?php

namespace Database\Seeders;

use App\Domains\Lien\Models\LienDocumentType;
use Illuminate\Database\Seeder;

class LienDocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $documentTypes = config('lien.document_types');

        foreach ($documentTypes as $slug => $data) {
            LienDocumentType::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'is_active' => true,
                ]
            );
        }
    }
}
