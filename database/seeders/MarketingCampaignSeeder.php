<?php

namespace Database\Seeders;

use App\Domains\Marketing\Enums\CampaignStatus;
use App\Domains\Marketing\Enums\CampaignStepType;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use Illuminate\Database\Seeder;

class MarketingCampaignSeeder extends Seeder
{
    /**
     * PostGrid template IDs by environment.
     */
    private const POSTGRID_TEMPLATES = [
        'local' => 'template_s4xsvnknTLb68jHQfZ3prA',
        'production' => 'template_fn6FF5hYopGg7AQevqwx2T',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createLiensCampaign();
    }

    /**
     * Create the liens marketing campaign for NosyNelly leads.
     */
    private function createLiensCampaign(): void
    {
        // Check if campaign already exists
        $existing = MarketingCampaign::where('landing_key', 'liens')->first();

        if ($existing) {
            $this->command->info("Liens campaign already exists (ID: {$existing->id}). Skipping.");

            return;
        }

        $templateId = $this->getTemplateId();

        $campaign = MarketingCampaign::create([
            'name' => 'NosyNelly Liens Campaign',
            'description' => 'Automated lien rights mailing campaign for leads imported from NosyNelly.',
            'landing_key' => 'liens',
            'status' => CampaignStatus::Active,
        ]);

        MarketingCampaignStep::create([
            'campaign_id' => $campaign->id,
            'order' => 1,
            'type' => CampaignStepType::Letter,
            'delay_days' => 0,
            'name' => 'Initial Lien Rights Letter',
            'description' => 'First contact letter explaining lien rights services.',
            'provider_template_ref' => ['templateId' => $templateId],
        ]);

        $this->command->info("Created liens campaign (ID: {$campaign->id}) with template: {$templateId}");
    }

    /**
     * Get the PostGrid template ID based on environment.
     */
    private function getTemplateId(): string
    {
        $env = app()->environment();

        if (in_array($env, ['local', 'testing'])) {
            return self::POSTGRID_TEMPLATES['local'];
        }

        return self::POSTGRID_TEMPLATES['production'];
    }
}
