<?php

namespace App\Console\Commands\Marketing;

use App\Domains\Marketing\Enums\LeadCampaignStatus;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use Illuminate\Console\Command;

class ResetFailedEnrollmentsCommand extends Command
{
    protected $signature = 'marketing:reset-failed
                            {--skip-address-errors : Skip enrollments that failed due to address validation}
                            {--force : Skip confirmation prompt}
                            {--dry-run : Show what would be reset without making changes}';

    protected $description = 'Reset failed enrollments to pending so they can be reprocessed';

    public function handle(): int
    {
        $query = MarketingLeadCampaign::query()
            ->where('status', LeadCampaignStatus::Failed);

        if ($this->option('skip-address-errors')) {
            // Exclude enrollments where mailing failed due to address errors
            $query->whereDoesntHave('mailings', function ($q) {
                $q->where('error_message', 'LIKE', '%address_strictness_error%')
                    ->orWhere('error_message', 'LIKE', '%address verification%');
            });
        }

        $enrollments = $query->with(['lead', 'mailings'])->get();

        if ($enrollments->isEmpty()) {
            $this->info('No failed enrollments found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$enrollments->count()} failed enrollments:");
        $this->newLine();

        $rows = [];
        foreach ($enrollments as $enrollment) {
            $lastError = $enrollment->mailings->first()?->error_message ?? 'Unknown';
            $isAddressError = str_contains($lastError, 'address_strictness_error');

            $rows[] = [
                $enrollment->id,
                $enrollment->lead?->business_name ?? 'N/A',
                $enrollment->lead?->mailing_address ?? 'N/A',
                $isAddressError ? 'Address Invalid' : 'Other Error',
            ];
        }

        $this->table(['ID', 'Business', 'Address', 'Error Type'], $rows);
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('Dry run - no changes made.');

            return Command::SUCCESS;
        }

        if (! $this->option('force')) {
            if (! $this->confirm('Reset these enrollments to pending?', false)) {
                $this->info('Aborted.');

                return Command::SUCCESS;
            }
        }

        $resetCount = 0;
        $mailingCount = 0;
        $linkCount = 0;

        foreach ($enrollments as $enrollment) {
            // Delete tracking links for mailings (not vanity links)
            $linkCount += MarketingTrackingLink::whereIn(
                'mailing_id',
                $enrollment->mailings->pluck('id')
            )->delete();

            // Delete mailings
            $mailingCount += $enrollment->mailings()->delete();

            // Reset enrollment to pending
            $enrollment->status = LeadCampaignStatus::Pending;
            $enrollment->initializeForFirstStep();
            $resetCount++;
        }

        $this->newLine();
        $this->info("Reset {$resetCount} enrollments to pending.");
        $this->line("  Deleted {$mailingCount} mailings");
        $this->line("  Deleted {$linkCount} tracking links");
        $this->newLine();
        $this->info('Run `php artisan tinker --execute="(new App\Domains\Marketing\Jobs\ProcessCampaignSteps)->handle();"` to process them now.');

        return Command::SUCCESS;
    }
}
