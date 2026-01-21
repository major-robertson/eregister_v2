<?php

namespace Database\Seeders;

use App\Domains\Lien\Models\LienDocumentTemplate;
use App\Domains\Lien\Models\LienDocumentType;
use Illuminate\Database\Seeder;

class LienDocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Create a generic template for each document type
        $documentTypes = LienDocumentType::all();

        foreach ($documentTypes as $docType) {
            LienDocumentTemplate::updateOrCreate(
                [
                    'document_type_id' => $docType->id,
                    'state' => null,
                    'county' => null,
                    'version' => 1,
                ],
                [
                    'effective_date' => now()->startOfYear(),
                    'schema_json' => $this->getSchemaForType($docType->slug),
                    'blade_view' => null,
                    'is_active' => true,
                ]
            );
        }
    }

    private function getSchemaForType(string $slug): array
    {
        return match ($slug) {
            'prelim_notice' => $this->prelimNoticeSchema(),
            'noi' => $this->noiSchema(),
            'mechanics_lien' => $this->mechanicsLienSchema(),
            'lien_release' => $this->lienReleaseSchema(),
            default => ['steps' => []],
        };
    }

    private function prelimNoticeSchema(): array
    {
        return [
            'steps' => [
                [
                    'key' => 'project',
                    'title' => 'Project Information',
                    'fields' => [
                        ['key' => 'property_legal_description', 'type' => 'textarea', 'label' => 'Legal Description', 'required' => false],
                        ['key' => 'apn', 'type' => 'text', 'label' => 'Assessor Parcel Number (APN)', 'required' => false],
                    ],
                ],
                [
                    'key' => 'work',
                    'title' => 'Work Description',
                    'fields' => [
                        ['key' => 'description_of_work', 'type' => 'textarea', 'label' => 'Description of Work/Materials', 'required' => true],
                        ['key' => 'estimated_amount', 'type' => 'money', 'label' => 'Estimated Amount', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    private function noiSchema(): array
    {
        return [
            'steps' => [
                [
                    'key' => 'project',
                    'title' => 'Project Information',
                    'fields' => [
                        ['key' => 'property_legal_description', 'type' => 'textarea', 'label' => 'Legal Description', 'required' => false],
                    ],
                ],
                [
                    'key' => 'amounts',
                    'title' => 'Claim Amount',
                    'fields' => [
                        ['key' => 'amount_claimed', 'type' => 'money', 'label' => 'Amount Claimed', 'required' => true],
                        ['key' => 'description_of_work', 'type' => 'textarea', 'label' => 'Description of Work/Materials', 'required' => true],
                    ],
                ],
            ],
        ];
    }

    private function mechanicsLienSchema(): array
    {
        return [
            'steps' => [
                [
                    'key' => 'project',
                    'title' => 'Project Information',
                    'fields' => [
                        ['key' => 'property_legal_description', 'type' => 'textarea', 'label' => 'Legal Description', 'required' => true],
                        ['key' => 'apn', 'type' => 'text', 'label' => 'Assessor Parcel Number (APN)', 'required' => false],
                    ],
                ],
                [
                    'key' => 'amounts',
                    'title' => 'Claim Amount',
                    'fields' => [
                        ['key' => 'amount_claimed', 'type' => 'money', 'label' => 'Amount Claimed', 'required' => true],
                        ['key' => 'description_of_work', 'type' => 'textarea', 'label' => 'Description of Work/Materials', 'required' => true],
                    ],
                ],
                [
                    'key' => 'dates',
                    'title' => 'Important Dates',
                    'fields' => [
                        ['key' => 'first_work_date', 'type' => 'date', 'label' => 'Date Work First Commenced', 'required' => true],
                        ['key' => 'last_work_date', 'type' => 'date', 'label' => 'Date Work Last Performed', 'required' => true],
                    ],
                ],
            ],
        ];
    }

    private function lienReleaseSchema(): array
    {
        return [
            'steps' => [
                [
                    'key' => 'release',
                    'title' => 'Release Information',
                    'fields' => [
                        ['key' => 'original_lien_recording_number', 'type' => 'text', 'label' => 'Original Lien Recording Number', 'required' => true],
                        ['key' => 'original_lien_date', 'type' => 'date', 'label' => 'Original Lien Recording Date', 'required' => true],
                        ['key' => 'amount_received', 'type' => 'money', 'label' => 'Amount Received', 'required' => true],
                    ],
                ],
            ],
        ];
    }
}
