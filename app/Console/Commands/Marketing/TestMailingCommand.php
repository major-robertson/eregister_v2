<?php

namespace App\Console\Commands\Marketing;

use App\Domains\Marketing\Enums\LeadCampaignStatus;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingMailing;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Domains\Marketing\Services\QrCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestMailingCommand extends Command
{
    protected $signature = 'marketing:test
                            {--reset : Reset enrollment and clear mailings before testing}
                            {--qr-only : Only regenerate QR code for existing tracking link}
                            {--status : Show current status without making changes}
                            {--process : Process campaigns and run queue job}';

    protected $description = 'Test the marketing mailing system';

    public function handle(QrCodeService $qrCodeService): int
    {
        if ($this->option('status')) {
            return $this->showStatus();
        }

        if ($this->option('qr-only')) {
            return $this->regenerateQrCode($qrCodeService);
        }

        if ($this->option('reset')) {
            $this->resetTestData();
        }

        if ($this->option('process')) {
            return $this->processAndRun();
        }

        $this->showStatus();

        return Command::SUCCESS;
    }

    protected function showStatus(): int
    {
        $this->info('=== Marketing System Status ===');
        $this->newLine();

        $lead = MarketingLead::first();
        if (! $lead) {
            $this->error('No leads found. Create a lead first.');

            return Command::FAILURE;
        }

        $this->line("Lead: {$lead->business_name} (slug: {$lead->slug})");

        $enrollment = MarketingLeadCampaign::where('lead_id', $lead->id)->first();
        if ($enrollment) {
            $this->line("Enrollment: status={$enrollment->status->value}, step={$enrollment->current_step_order}");
            $this->line('Next action: '.($enrollment->next_action_at ?? 'N/A'));
        } else {
            $this->warn('No enrollment found.');
        }

        $step = MarketingCampaignStep::first();
        if ($step) {
            $templateRef = $step->provider_template_ref;
            $this->line('Campaign Step: '.($templateRef['templateId'] ?? 'No template ID'));
        }

        $this->newLine();
        $this->info('=== Mailings ===');

        $mailings = MarketingMailing::all();
        if ($mailings->isEmpty()) {
            $this->line('No mailings yet.');
        }

        foreach ($mailings as $mailing) {
            $this->line("ID: {$mailing->id}");
            $this->line('  Provider ID: '.($mailing->provider_id ?? 'pending'));
            $this->line('  Executed: '.($mailing->executed_at ?? 'N/A'));
            if ($mailing->error_message) {
                $this->error("  Error: {$mailing->error_message}");
            }
        }

        $this->newLine();
        $this->info('=== Tracking Links ===');

        $links = MarketingTrackingLink::all();
        foreach ($links as $link) {
            $type = $link->mailing_id ? 'QR' : 'Vanity';
            $this->line("[{$type}] {$link->token}");
            $this->line("  URL: http://127.0.0.1:8000/go/t/{$link->token}");
            if ($link->qr_code_path) {
                $url = Storage::disk('s3')->url($link->qr_code_path);
                $this->line("  QR: {$url}");
            }
        }

        $this->newLine();
        $this->info('=== Test URLs ===');
        $this->line("Direct: http://127.0.0.1:8000/go/{$lead->slug}");

        $qrLink = MarketingTrackingLink::whereNotNull('mailing_id')->first();
        if ($qrLink) {
            $this->line("QR Scan: http://127.0.0.1:8000/go/t/{$qrLink->token}");
        }

        return Command::SUCCESS;
    }

    protected function resetTestData(): void
    {
        $this->info('Resetting test data...');

        // Ensure lead has test data for all fields
        $lead = MarketingLead::first();
        if ($lead) {
            $lead->update([
                'permit_or_external_id' => $lead->permit_or_external_id ?? 'TEST-PERMIT-001',
                'property_address' => $lead->property_address ?? '456 Oak Street',
                'property_city' => $lead->property_city ?? 'Louisville',
                'property_state' => $lead->property_state ?? 'KY',
            ]);
            $this->line("Updated lead with test data (permit: {$lead->permit_or_external_id})");
        }

        $enrollment = MarketingLeadCampaign::first();
        if ($enrollment) {
            $enrollment->status = LeadCampaignStatus::Pending;
            $enrollment->save();
            $enrollment->initializeForFirstStep();
            $this->line("Reset enrollment to step {$enrollment->current_step_order}");
        }

        $deleted = MarketingTrackingLink::whereNotNull('mailing_id')->delete();
        $this->line("Deleted {$deleted} mailing tracking links");

        $deleted = MarketingMailing::query()->delete();
        $this->line("Deleted {$deleted} mailings");

        $this->info('Reset complete!');
        $this->newLine();
    }

    protected function regenerateQrCode(QrCodeService $qrCodeService): int
    {
        $this->info('Regenerating QR code...');

        $link = MarketingTrackingLink::whereNotNull('mailing_id')->first();
        if (! $link) {
            $link = MarketingTrackingLink::first();
        }

        if (! $link) {
            $this->error('No tracking link found.');

            return Command::FAILURE;
        }

        $this->line("Token: {$link->token}");

        $path = $qrCodeService->generateForTrackingLink($link);

        if ($path) {
            $url = Storage::disk('s3')->url($path);
            $this->info("Generated: {$path}");
            $this->line("URL: {$url}");
        } else {
            $this->error('Failed to generate QR code. Check logs.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function processAndRun(): int
    {
        $this->info('Processing campaigns...');

        // Run the processor
        $processor = new \App\Domains\Marketing\Jobs\ProcessCampaignSteps;
        $processor->handle();

        $this->line('Dispatched jobs. Running queue...');

        // Run one queue job
        $this->call('queue:work', ['--once' => true]);

        $this->newLine();

        return $this->showStatus();
    }
}
