<?php

namespace App\Domains\Marketing\Jobs;

use App\Contracts\MailProviderInterface;
use App\Domains\Marketing\Enums\CampaignStepType;
use App\Domains\Marketing\Enums\DestinationType;
use App\Domains\Marketing\Enums\MailProvider;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingMailing;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Domains\Marketing\Services\PostGridMailProvider;
use App\Domains\Marketing\Services\QrCodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendCampaignMailing implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public MarketingLeadCampaign $enrollment
    ) {}

    /**
     * Execute the job.
     */
    public function handle(QrCodeService $qrCodeService): void
    {
        $enrollment = $this->enrollment->fresh();

        if (! $enrollment) {
            Log::warning('SendCampaignMailing: Enrollment not found');

            return;
        }

        $step = $enrollment->getCurrentStep();

        if (! $step) {
            Log::warning('SendCampaignMailing: No current step found', [
                'enrollment_id' => $enrollment->id,
            ]);
            $enrollment->markFailed('No current step found');

            return;
        }

        $lead = $enrollment->lead;

        if (! $lead) {
            Log::warning('SendCampaignMailing: Lead not found', [
                'enrollment_id' => $enrollment->id,
            ]);
            $enrollment->markFailed('Lead not found');

            return;
        }

        try {
            // Create or get the mailing (idempotent via unique constraint)
            $mailing = $this->getOrCreateMailing($enrollment, $step);

            // If mailing already executed, skip
            if ($mailing->isExecuted()) {
                Log::info('SendCampaignMailing: Mailing already executed', [
                    'mailing_id' => $mailing->id,
                ]);
                // Still update the enrollment in case it wasn't updated before
                $enrollment->markStepExecuted();

                return;
            }

            // Create tracking link for this mailing
            $trackingLink = $this->createTrackingLink($mailing, $enrollment, $step, $lead);

            // Generate QR code
            $qrCodeService->generateForTrackingLink($trackingLink);

            // Send via provider
            $result = $this->sendViaProvider($step, $lead, $mailing, $trackingLink);

            if ($result->success) {
                // Mark mailing as executed
                $mailing->markExecuted($result->providerId, $result->payload ?? []);

                // Update enrollment for next step
                $enrollment->markStepExecuted();

                Log::info('SendCampaignMailing: Mailing sent successfully', [
                    'mailing_id' => $mailing->id,
                    'provider_id' => $result->providerId,
                ]);
            } else {
                // Handle failure
                $mailing->update([
                    'error_message' => $result->errorMessage,
                    'failed_at' => now(),
                ]);

                if (! $result->retryable) {
                    $enrollment->markFailed($result->errorMessage);
                }

                Log::error('SendCampaignMailing: Failed to send mailing', [
                    'mailing_id' => $mailing->id,
                    'error' => $result->errorMessage,
                    'retryable' => $result->retryable,
                ]);

                if ($result->retryable) {
                    throw new \Exception($result->errorMessage);
                }
            }
        } catch (UniqueConstraintViolationException $e) {
            // Mailing already exists (race condition), fetch and check
            Log::info('SendCampaignMailing: Mailing already created (race condition)', [
                'enrollment_id' => $enrollment->id,
                'step_id' => $step->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendCampaignMailing: Unexpected error', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get or create the mailing record (idempotent).
     */
    protected function getOrCreateMailing(
        MarketingLeadCampaign $enrollment,
        MarketingCampaignStep $step
    ): MarketingMailing {
        return MarketingMailing::firstOrCreate(
            [
                'lead_campaign_id' => $enrollment->id,
                'campaign_step_id' => $step->id,
            ],
            [
                'provider' => MailProvider::PostGrid,
            ]
        );
    }

    /**
     * Create a tracking link for the mailing.
     */
    protected function createTrackingLink(
        MarketingMailing $mailing,
        MarketingLeadCampaign $enrollment,
        MarketingCampaignStep $step,
        $lead
    ): MarketingTrackingLink {
        return MarketingTrackingLink::firstOrCreate(
            ['mailing_id' => $mailing->id],
            [
                'lead_id' => $lead->id,
                'campaign_id' => $enrollment->campaign_id,
                'campaign_step_id' => $step->id,
                'destination_type' => DestinationType::LeadLanding,
                'destination' => $lead->slug,
            ]
        );
    }

    /**
     * Send the mailpiece via the provider.
     */
    protected function sendViaProvider(
        MarketingCampaignStep $step,
        $lead,
        MarketingMailing $mailing,
        MarketingTrackingLink $trackingLink
    ) {
        $provider = $this->getProvider();

        $companyName = $lead->business_name;
        if ($lead->permit_or_external_id) {
            $companyName .= ' | REF: '.$lead->permit_or_external_id;
        }

        $to = [
            'company_name' => $companyName,
            'first_name' => $this->getFirstName($lead->contact_name),
            'last_name' => $this->getLastName($lead->contact_name),
            'address' => $lead->mailing_address,
            'address_2' => $lead->mailing_address_2,
            'city' => $lead->mailing_city,
            'state' => $lead->mailing_state,
            'zip' => $lead->mailing_zip,
        ];

        // Strip protocol from landing URL for cleaner display on printed mail
        $landingUrl = route('marketing.landing.slug', ['slug' => $lead->slug]);
        $landingUrl = preg_replace('#^https?://#', '', $landingUrl);

        $mergeVariables = [
            'business_name' => $lead->business_name,
            'contact_name' => $lead->contact_name,
            'property_address' => $lead->property_address,
            'property_city' => $lead->property_city,
            'property_state' => $lead->property_state,
            'qr_code_url' => $trackingLink->getQrCodeUrl(),
            'landing_url' => $landingUrl,
        ];

        $metadata = [
            'lead_id' => $lead->id,
            'campaign_id' => $mailing->leadCampaign->campaign_id,
            'campaign_step_id' => $step->id,
            'mailing_id' => $mailing->id,
            'tracking_link_token' => $trackingLink->token,
        ];

        if ($step->type === CampaignStepType::Postcard) {
            return $provider->sendPostcard(
                to: $to,
                templateRef: $step->provider_template_ref ?? [],
                mergeVariables: $mergeVariables,
                options: $step->mailpiece_options ?? [],
                metadata: $metadata,
                idempotencyKey: $mailing->idempotency_key,
            );
        }

        // Default to letter
        return $provider->sendLetter(
            to: $to,
            templateRef: $step->provider_template_ref ?? [],
            mergeVariables: $mergeVariables,
            options: $step->mailpiece_options ?? [],
            metadata: $metadata,
            idempotencyKey: $mailing->idempotency_key,
        );
    }

    /**
     * Get the mail provider instance.
     */
    protected function getProvider(): MailProviderInterface
    {
        return app(PostGridMailProvider::class);
    }

    /**
     * Extract first name from a full name.
     */
    protected function getFirstName(?string $fullName): string
    {
        if (! $fullName) {
            return '';
        }

        $parts = explode(' ', trim($fullName), 2);

        return $parts[0] ?? '';
    }

    /**
     * Extract last name from a full name.
     */
    protected function getLastName(?string $fullName): string
    {
        if (! $fullName) {
            return '';
        }

        $parts = explode(' ', trim($fullName), 2);

        return $parts[1] ?? '';
    }
}
