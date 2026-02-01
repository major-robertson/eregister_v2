<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Marketing\Enums\CampaignStatus;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ImportLeadsRequest;
use Illuminate\Http\JsonResponse;

class MarketingLeadController extends Controller
{
    /**
     * Import leads from NosyNelly.
     *
     * Upserts leads by record_id, checks if mailing address has ever been mailed,
     * and enrolls new addresses in the active liens campaign.
     */
    public function import(ImportLeadsRequest $request): JsonResponse
    {
        // Fetch campaign ONCE before the loop - fail fast if missing
        $campaign = MarketingCampaign::query()
            ->where('landing_key', 'liens')
            ->where('status', CampaignStatus::Active)
            ->first();

        if (! $campaign) {
            return response()->json([
                'success' => false,
                'error' => 'No active liens campaign configured. Contact support.',
            ], 422);
        }

        $imported = 0;
        $updated = 0;
        $enrolled = 0;
        $skippedAlreadyMailed = 0;

        foreach ($request->validated()['leads'] as $leadData) {
            // Upsert MarketingLead using record_id as idempotency key
            $lead = MarketingLead::updateOrCreate(
                ['record_id' => $leadData['record_id']],
                $leadData
            );

            if ($lead->wasRecentlyCreated) {
                $imported++;
            } else {
                $updated++;
            }

            // Check if this mailing address has ever been mailed
            if ($this->hasBeenMailed($lead)) {
                $skippedAlreadyMailed++;

                continue;
            }

            // Check if already enrolled in this campaign
            $alreadyEnrolled = MarketingLeadCampaign::where('lead_id', $lead->id)
                ->where('campaign_id', $campaign->id)
                ->exists();

            if ($alreadyEnrolled) {
                continue;
            }

            // Enroll in campaign
            $enrollment = MarketingLeadCampaign::create([
                'lead_id' => $lead->id,
                'campaign_id' => $campaign->id,
            ]);
            $enrollment->initializeForFirstStep();

            $enrolled++;
        }

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'enrolled' => $enrolled,
            'skipped_already_mailed' => $skippedAlreadyMailed,
        ]);
    }

    /**
     * Check if the mailing address has ever been mailed.
     *
     * Looks for any lead with the same normalized mailing address + zip
     * that has a MarketingMailing with executed_at set.
     */
    protected function hasBeenMailed(MarketingLead $lead): bool
    {
        if (empty($lead->mailing_address) || empty($lead->mailing_zip)) {
            return false;
        }

        $normalizedAddress = strtolower(trim($lead->mailing_address));
        $normalizedZip = substr(trim($lead->mailing_zip), 0, 5);

        return MarketingLead::query()
            ->whereRaw('LOWER(TRIM(mailing_address)) = ?', [$normalizedAddress])
            ->where('mailing_zip', 'LIKE', $normalizedZip.'%')
            ->whereHas('campaigns.mailings', fn ($q) => $q->whereNotNull('executed_at'))
            ->exists();
    }
}
