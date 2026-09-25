<?php

use App\Domains\Marketing\Enums\CampaignStatus;
use App\Domains\Marketing\Enums\CampaignStepType;
use App\Domains\Marketing\Enums\LeadCampaignStatus;
use App\Domains\Marketing\Jobs\ProcessCampaignSteps;
use App\Domains\Marketing\Jobs\SendCampaignMailing;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingMailing;
use App\Domains\Marketing\Services\MailResult;
use App\Domains\Marketing\Services\PostGridMailProvider;
use App\Domains\Marketing\Services\QrCodeService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->lead = MarketingLead::create([
        'slug' => 'test-contracting-llc',
        'business_name' => 'Test Contracting LLC',
        'contact_name' => 'John Doe',
        'mailing_address' => '123 Main St',
        'mailing_city' => 'Louisville',
        'mailing_state' => 'KY',
        'mailing_zip' => '40258',
    ]);

    $this->campaign = MarketingCampaign::create([
        'name' => 'Test Campaign',
        'status' => CampaignStatus::Active,
    ]);

    $this->step1 = MarketingCampaignStep::create([
        'campaign_id' => $this->campaign->id,
        'order' => 1,
        'type' => CampaignStepType::Letter,
        'delay_days' => 0,
        'name' => 'Initial Letter',
        'provider_template_ref' => ['templateId' => 'tmpl_test'],
    ]);

    $this->step2 = MarketingCampaignStep::create([
        'campaign_id' => $this->campaign->id,
        'order' => 2,
        'type' => CampaignStepType::Letter,
        'delay_days' => 10,
        'name' => 'Follow-up Letter',
        'provider_template_ref' => ['templateId' => 'tmpl_test2'],
    ]);
});

it('can enroll a lead in a campaign', function () {
    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
    ]);

    $enrollment->initializeForFirstStep();

    expect($enrollment->status)->toBe(LeadCampaignStatus::Pending);
    expect($enrollment->current_step_order)->toBe(1);
    expect($enrollment->next_action_at)->not->toBeNull();
    expect($enrollment->enrolled_at)->not->toBeNull();
});

it('dispatches mailing jobs for due enrollments', function () {
    Queue::fake();

    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
    ]);

    $job = new ProcessCampaignSteps;
    $job->handle();

    Queue::assertPushed(SendCampaignMailing::class, function ($job) use ($enrollment) {
        return $job->enrollment->id === $enrollment->id;
    });
});

it('does not dispatch jobs for future enrollments', function () {
    Queue::fake();

    MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'next_action_at' => now()->addDay(),
        'enrolled_at' => now(),
    ]);

    $job = new ProcessCampaignSteps;
    $job->handle();

    Queue::assertNotPushed(SendCampaignMailing::class);
});

it('does not dispatch jobs for completed enrollments', function () {
    Queue::fake();

    MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Completed,
        'current_step_order' => 2,
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
        'completed_at' => now(),
    ]);

    $job = new ProcessCampaignSteps;
    $job->handle();

    Queue::assertNotPushed(SendCampaignMailing::class);
});

it('creates mailing records with idempotency', function () {
    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
    ]);

    // Create first mailing
    $mailing1 = MarketingMailing::firstOrCreate([
        'lead_campaign_id' => $enrollment->id,
        'campaign_step_id' => $this->step1->id,
    ]);

    // Try to create duplicate - should return existing
    $mailing2 = MarketingMailing::firstOrCreate([
        'lead_campaign_id' => $enrollment->id,
        'campaign_step_id' => $this->step1->id,
    ]);

    expect($mailing1->id)->toBe($mailing2->id);
    expect(MarketingMailing::count())->toBe(1);
});

it('advances to next step after execution', function () {
    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
    ]);

    $enrollment->markStepExecuted();

    $enrollment->refresh();

    expect($enrollment->status)->toBe(LeadCampaignStatus::InProgress);
    expect($enrollment->current_step_order)->toBe(2);
    expect($enrollment->last_step_executed_at)->not->toBeNull();
    // Next action should be 10 days from now (step 2 delay_days)
    expect($enrollment->next_action_at->startOfDay())
        ->toEqual(now()->addDays(10)->startOfDay());
});

it('marks enrollment as completed after last step', function () {
    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::InProgress,
        'current_step_order' => 2, // Last step
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
    ]);

    $enrollment->markStepExecuted();

    $enrollment->refresh();

    expect($enrollment->status)->toBe(LeadCampaignStatus::Completed);
    expect($enrollment->completed_at)->not->toBeNull();
    expect($enrollment->next_action_at)->toBeNull();
});

it('mails a due step once when two copies of the job run', function () {
    Storage::fake('s3');
    $this->mock(PostGridMailProvider::class, function ($mock) {
        $mock->shouldReceive('sendLetter')->once()->andReturn(MailResult::success('letter_1', 'ready'));
    });

    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
    ]);

    // Two scheduler runs each queue a copy before either copy runs.
    $first = new SendCampaignMailing($enrollment);
    $second = new SendCampaignMailing($enrollment);

    $first->handle(app(QrCodeService::class));
    $second->handle(app(QrCodeService::class));

    expect(MarketingMailing::count())->toBe(1);
    expect($enrollment->fresh()->current_step_order)->toBe(2);
});

it('does not mail the next step early when that step has no delay', function () {
    Storage::fake('s3');
    $this->step2->update(['delay_days' => 0]);
    $this->mock(PostGridMailProvider::class, function ($mock) {
        $mock->shouldReceive('sendLetter')->once()->andReturn(MailResult::success('letter_1', 'ready'));
    });

    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
    ]);

    $first = new SendCampaignMailing($enrollment);
    $second = new SendCampaignMailing($enrollment);

    $first->handle(app(QrCodeService::class));

    // Step 2 is due now as well, but the second copy was queued for step 1.
    $this->travel(5)->minutes();
    $second->handle(app(QrCodeService::class));

    expect(MarketingMailing::count())->toBe(1);
    expect($enrollment->fresh()->current_step_order)->toBe(2);
});

it('still sends the step when a retry follows a failed attempt', function () {
    Storage::fake('s3');
    $this->mock(PostGridMailProvider::class, function ($mock) {
        $mock->shouldReceive('sendLetter')->twice()->andReturn(
            MailResult::failure('PostGrid timed out', retryable: true),
            MailResult::success('letter_1', 'ready'),
        );
    });

    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'next_action_at' => now()->subMinute(),
        'enrolled_at' => now(),
    ]);

    $job = new SendCampaignMailing($enrollment);

    expect(fn () => $job->handle(app(QrCodeService::class)))->toThrow(Exception::class, 'PostGrid timed out');

    $job->handle(app(QrCodeService::class));

    expect(MarketingMailing::whereNotNull('executed_at')->count())->toBe(1);
    expect($enrollment->fresh()->current_step_order)->toBe(2);
});
