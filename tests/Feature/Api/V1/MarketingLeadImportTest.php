<?php

use App\Domains\Marketing\Enums\CampaignStatus;
use App\Domains\Marketing\Enums\CampaignStepType;
use App\Domains\Marketing\Enums\LeadCampaignStatus;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingMailing;

beforeEach(function () {
    config(['services.nosynelly.api_key' => 'test-api-key-12345']);
});

it('returns 401 when no API key is provided', function () {
    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            ['record_id' => 'test-123'],
        ],
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'error' => 'Invalid or missing API key.',
        ]);
});

it('returns 401 when invalid API key is provided', function () {
    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            ['record_id' => 'test-123'],
        ],
    ], [
        'Authorization' => 'Bearer wrong-key',
    ]);

    $response->assertUnauthorized()
        ->assertJson([
            'success' => false,
            'error' => 'Invalid or missing API key.',
        ]);
});

it('returns 422 when no active liens campaign exists', function () {
    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            ['record_id' => 'test-123'],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertUnprocessable()
        ->assertJson([
            'success' => false,
            'error' => 'No active liens campaign configured. Contact support.',
        ]);
});

it('returns 422 when leads array is empty', function () {
    createActiveLiensCampaign();

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);
});

it('returns 422 when leads array exceeds 100 items', function () {
    createActiveLiensCampaign();

    $leads = array_map(fn ($i) => ['record_id' => "test-$i"], range(1, 101));

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => $leads,
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);
});

it('successfully imports new leads', function () {
    createActiveLiensCampaign();

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'abc123:def456:general contractor',
                'record_date' => '2026-01-26',
                'business_name' => 'ABC Plumbing LLC',
                'contact_name' => 'John Smith',
                'email' => 'john@abcplumbing.com',
                'phone' => '502-555-1234',
                'mailing_address' => '123 Main St',
                'mailing_address_2' => 'Suite 100',
                'mailing_city' => 'Louisville',
                'mailing_state' => 'KY',
                'mailing_zip' => '40202',
                'property_address' => '456 Oak Ave',
                'property_city' => 'Louisville',
                'property_state' => 'KY',
                'property_zip' => '40203',
                'permit_or_external_id' => 'BLD-2026-12345',
                'raw_category' => 'Building Permit',
                'title' => 'New Commercial Building',
                'description' => 'Construction of 3-story office building',
                'estimated_cost' => '1500000.00',
                'source_url' => 'https://nosynelly.com/scoop/123',
                'role' => 'General Contractor',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'imported' => 1,
            'updated' => 0,
            'enrolled' => 1,
            'skipped_already_mailed' => 0,
        ]);

    expect(MarketingLead::count())->toBe(1);
    expect(MarketingLeadCampaign::count())->toBe(1);

    $lead = MarketingLead::first();
    expect($lead->business_name)->toBe('ABC Plumbing LLC');
    expect($lead->record_id)->toBe('abc123:def456:general contractor');
});

it('upserts leads by record_id', function () {
    createActiveLiensCampaign();

    // First import
    MarketingLead::create([
        'record_id' => 'existing-record-123',
        'business_name' => 'Original Name',
        'mailing_address' => '100 Old St',
        'mailing_zip' => '40201',
    ]);

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'existing-record-123',
                'business_name' => 'Updated Name',
                'mailing_address' => '200 New St',
                'mailing_zip' => '40202',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'imported' => 0,
            'updated' => 1,
        ]);

    expect(MarketingLead::count())->toBe(1);

    $lead = MarketingLead::first();
    expect($lead->business_name)->toBe('Updated Name');
    expect($lead->mailing_address)->toBe('200 New St');
});

it('skips enrollment when mailing address has been mailed before', function () {
    $campaign = createActiveLiensCampaign();

    // Create an existing lead that has been mailed
    $existingLead = MarketingLead::create([
        'record_id' => 'old-record-123',
        'business_name' => 'Old Company',
        'mailing_address' => '123 Main St',
        'mailing_city' => 'Louisville',
        'mailing_state' => 'KY',
        'mailing_zip' => '40202',
    ]);

    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $existingLead->id,
        'campaign_id' => $campaign->id,
        'status' => LeadCampaignStatus::Completed,
        'current_step_order' => 1,
        'enrolled_at' => now(),
    ]);

    // Create a mailing that was executed (marking this address as "mailed")
    MarketingMailing::create([
        'lead_campaign_id' => $enrollment->id,
        'campaign_step_id' => $campaign->steps->first()->id,
        'executed_at' => now(),
    ]);

    // Try to import a new lead with the same mailing address
    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'new-record-456',
                'business_name' => 'New Company',
                'mailing_address' => '123 Main St', // Same address
                'mailing_city' => 'Louisville',
                'mailing_state' => 'KY',
                'mailing_zip' => '40202', // Same zip
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'imported' => 1,
            'enrolled' => 0,
            'skipped_already_mailed' => 1,
        ]);

    // Lead was created but not enrolled
    expect(MarketingLead::count())->toBe(2);
    expect(MarketingLeadCampaign::count())->toBe(1); // Only the original enrollment
});

it('normalizes address comparison for duplicate check', function () {
    $campaign = createActiveLiensCampaign();

    // Create an existing lead that has been mailed
    $existingLead = MarketingLead::create([
        'record_id' => 'old-record-123',
        'business_name' => 'Old Company',
        'mailing_address' => '  123 MAIN ST  ', // Uppercase with spaces
        'mailing_zip' => '40202-1234', // Extended zip
    ]);

    $enrollment = MarketingLeadCampaign::create([
        'lead_id' => $existingLead->id,
        'campaign_id' => $campaign->id,
        'status' => LeadCampaignStatus::Completed,
        'current_step_order' => 1,
        'enrolled_at' => now(),
    ]);

    MarketingMailing::create([
        'lead_campaign_id' => $enrollment->id,
        'campaign_step_id' => $campaign->steps->first()->id,
        'executed_at' => now(),
    ]);

    // Import with different case/spacing but same address
    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'new-record-456',
                'business_name' => 'New Company',
                'mailing_address' => '123 main st', // Lowercase, no extra spaces
                'mailing_zip' => '40202', // Short zip
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'skipped_already_mailed' => 1,
        ]);
});

it('enrolls leads that have same address but were never mailed', function () {
    $campaign = createActiveLiensCampaign();

    // Create an existing lead that was enrolled but never mailed
    $existingLead = MarketingLead::create([
        'record_id' => 'old-record-123',
        'business_name' => 'Old Company',
        'mailing_address' => '123 Main St',
        'mailing_zip' => '40202',
    ]);

    MarketingLeadCampaign::create([
        'lead_id' => $existingLead->id,
        'campaign_id' => $campaign->id,
        'status' => LeadCampaignStatus::Pending, // Still pending, never executed
        'current_step_order' => 1,
        'enrolled_at' => now(),
    ]);

    // No mailing record with executed_at!

    // Import new lead with same address
    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'new-record-456',
                'business_name' => 'New Company',
                'mailing_address' => '123 Main St',
                'mailing_zip' => '40202',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'imported' => 1,
            'enrolled' => 1, // Should be enrolled since address was never mailed
            'skipped_already_mailed' => 0,
        ]);
});

it('does not re-enroll lead already in campaign', function () {
    $campaign = createActiveLiensCampaign();

    // Create lead already enrolled
    $existingLead = MarketingLead::create([
        'record_id' => 'existing-123',
        'business_name' => 'Existing Company',
        'mailing_address' => '999 Other St',
        'mailing_zip' => '40299',
    ]);

    MarketingLeadCampaign::create([
        'lead_id' => $existingLead->id,
        'campaign_id' => $campaign->id,
        'status' => LeadCampaignStatus::Pending,
        'current_step_order' => 1,
        'enrolled_at' => now(),
    ]);

    // Import same lead again
    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'existing-123',
                'business_name' => 'Existing Company Updated',
                'mailing_address' => '999 Other St',
                'mailing_zip' => '40299',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'updated' => 1,
            'enrolled' => 0, // Not enrolled again
        ]);

    // Still only one enrollment
    expect(MarketingLeadCampaign::count())->toBe(1);
});

it('initializes enrollment for first campaign step', function () {
    createActiveLiensCampaign();

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'new-123',
                'business_name' => 'New Company',
                'mailing_address' => '123 New St',
                'mailing_zip' => '40202',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk();

    $enrollment = MarketingLeadCampaign::first();

    expect($enrollment->status)->toBe(LeadCampaignStatus::Pending);
    expect($enrollment->current_step_order)->toBe(1);
    expect($enrollment->enrolled_at)->not->toBeNull();
    expect($enrollment->next_action_at)->not->toBeNull();
});

it('validates required fields', function () {
    createActiveLiensCampaign();

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                // Missing record_id
                'business_name' => 'Test Company',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['errors' => ['leads.0.record_id']]);
});

it('validates email format', function () {
    createActiveLiensCampaign();

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'test-123',
                'email' => 'not-an-email',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertUnprocessable()
        ->assertJsonPath('success', false);
});

it('handles batch of multiple leads', function () {
    createActiveLiensCampaign();

    $response = $this->postJson('/api/v1/marketing/leads/import', [
        'leads' => [
            [
                'record_id' => 'lead-1',
                'business_name' => 'Company One',
                'mailing_address' => '111 First St',
                'mailing_zip' => '40201',
            ],
            [
                'record_id' => 'lead-2',
                'business_name' => 'Company Two',
                'mailing_address' => '222 Second St',
                'mailing_zip' => '40202',
            ],
            [
                'record_id' => 'lead-3',
                'business_name' => 'Company Three',
                'mailing_address' => '333 Third St',
                'mailing_zip' => '40203',
            ],
        ],
    ], [
        'Authorization' => 'Bearer test-api-key-12345',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'imported' => 3,
            'enrolled' => 3,
        ]);

    expect(MarketingLead::count())->toBe(3);
    expect(MarketingLeadCampaign::count())->toBe(3);
});

/**
 * Helper function to create an active liens campaign with a step.
 */
function createActiveLiensCampaign(): MarketingCampaign
{
    $campaign = MarketingCampaign::create([
        'name' => 'Liens Campaign',
        'landing_key' => 'liens',
        'status' => CampaignStatus::Active,
    ]);

    MarketingCampaignStep::create([
        'campaign_id' => $campaign->id,
        'order' => 1,
        'type' => CampaignStepType::Letter,
        'delay_days' => 0,
        'name' => 'Initial Letter',
        'provider_template_ref' => ['templateId' => 'tmpl_test'],
    ]);

    return $campaign->fresh(['steps']);
}
