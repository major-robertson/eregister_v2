<?php

namespace App\Console\Commands\Marketing;

use App\Domains\Marketing\Models\MarketingEvent;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingMailing;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Domains\Marketing\Models\MarketingVisit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeMarketingDataCommand extends Command
{
    protected $signature = 'marketing:purge
                            {--force : Skip confirmation prompt}
                            {--keep-campaigns : Keep campaigns and campaign steps, only purge lead data}';

    protected $description = 'Purge all marketing leads, enrollments, mailings, tracking links, QR codes, visits, and events';

    public function handle(): int
    {
        $this->warn('⚠️  WARNING: This will permanently delete marketing data!');
        $this->newLine();

        // Show counts
        $counts = $this->getCounts();
        $this->showCounts($counts);

        if ($counts['total'] === 0) {
            $this->info('No marketing data to purge.');

            return Command::SUCCESS;
        }

        // Confirm unless --force
        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to purge all this data?', false)) {
                $this->info('Aborted.');

                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Purging marketing data...');

        // Delete QR codes from S3
        $this->deleteQrCodes();

        // Delete in order to respect foreign key constraints
        $this->deleteWithProgress('events', MarketingEvent::class);
        $this->deleteWithProgress('visits', MarketingVisit::class);
        $this->deleteWithProgress('tracking links', MarketingTrackingLink::class);
        $this->deleteWithProgress('mailings', MarketingMailing::class);
        $this->deleteWithProgress('enrollments', MarketingLeadCampaign::class);
        $this->deleteWithProgress('leads', MarketingLead::class);

        $this->newLine();
        $this->info('✓ Marketing data purged successfully.');

        return Command::SUCCESS;
    }

    /**
     * Get counts of all marketing data.
     *
     * @return array<string, int>
     */
    protected function getCounts(): array
    {
        $counts = [
            'leads' => MarketingLead::count(),
            'enrollments' => MarketingLeadCampaign::count(),
            'mailings' => MarketingMailing::count(),
            'tracking_links' => MarketingTrackingLink::count(),
            'qr_codes' => MarketingTrackingLink::whereNotNull('qr_code_path')->count(),
            'visits' => MarketingVisit::count(),
            'events' => MarketingEvent::count(),
        ];

        $counts['total'] = array_sum($counts) - $counts['qr_codes']; // Don't double-count QR codes

        return $counts;
    }

    /**
     * Show counts table.
     *
     * @param  array<string, int>  $counts
     */
    protected function showCounts(array $counts): void
    {
        $this->table(
            ['Data Type', 'Count'],
            [
                ['Leads', $counts['leads']],
                ['Enrollments', $counts['enrollments']],
                ['Mailings', $counts['mailings']],
                ['Tracking Links', $counts['tracking_links']],
                ['QR Codes (S3)', $counts['qr_codes']],
                ['Visits', $counts['visits']],
                ['Events', $counts['events']],
            ]
        );
    }

    /**
     * Delete QR codes from S3.
     */
    protected function deleteQrCodes(): void
    {
        $links = MarketingTrackingLink::whereNotNull('qr_code_path')->get();

        if ($links->isEmpty()) {
            $this->line('  No QR codes to delete from S3.');

            return;
        }

        $deleted = 0;
        $failed = 0;

        foreach ($links as $link) {
            try {
                if (Storage::disk('s3')->exists($link->qr_code_path)) {
                    Storage::disk('s3')->delete($link->qr_code_path);
                    $deleted++;
                }
            } catch (\Exception $e) {
                $failed++;
                $this->warn("  Failed to delete: {$link->qr_code_path}");
            }
        }

        $this->line("  Deleted {$deleted} QR codes from S3".($failed > 0 ? " ({$failed} failed)" : ''));
    }

    /**
     * Delete all records from a model with progress output.
     *
     * @param  class-string  $modelClass
     */
    protected function deleteWithProgress(string $label, string $modelClass): void
    {
        $count = $modelClass::count();

        if ($count === 0) {
            $this->line("  No {$label} to delete.");

            return;
        }

        $modelClass::query()->delete();

        $this->line("  Deleted {$count} {$label}.");
    }
}
