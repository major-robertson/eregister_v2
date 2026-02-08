<?php

namespace App\Domains\Marketing\Jobs;

use App\Domains\Marketing\Enums\LeadCampaignStatus;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessCampaignSteps implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $processed = 0;

        // Process due enrollments in batches using row locking to prevent race conditions
        DB::transaction(function () use (&$processed) {
            $enrollments = MarketingLeadCampaign::query()
                ->whereIn('status', [LeadCampaignStatus::Pending, LeadCampaignStatus::InProgress])
                ->where('next_action_at', '<=', now())
                ->lockForUpdate()
                ->limit(100) // Process in batches to avoid long transactions
                ->get();

            foreach ($enrollments as $enrollment) {
                try {
                    // Dispatch a job to send the mailing for this enrollment
                    SendCampaignMailing::dispatch($enrollment);
                    $processed++;
                } catch (\Throwable $e) {
                    Log::error('ProcessCampaignSteps: Failed to dispatch mailing job', [
                        'enrollment_id' => $enrollment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        // Only log when enrollments were actually processed
        if ($processed > 0) {
            Log::info('ProcessCampaignSteps: Processed campaign enrollments', [
                'processed' => $processed,
            ]);
        }
    }
}
