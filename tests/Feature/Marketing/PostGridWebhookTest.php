<?php

use App\Domains\Marketing\Enums\CampaignStatus;
use App\Domains\Marketing\Enums\CampaignStepType;
use App\Domains\Marketing\Enums\LeadCampaignStatus;
use App\Domains\Marketing\Enums\MailingStatus;
use App\Domains\Marketing\Enums\MailProvider;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingLeadCampaign;
use App\Domains\Marketing\Models\MarketingMailing;

beforeEach(function () {
    config(['services.postgrid.webhook_secret' => 'test_secret']);

    $this->lead = MarketingLead::create([
        'slug' => 'test-contracting-llc',
        'business_name' => 'Test Contracting LLC',
    ]);

    $this->campaign = MarketingCampaign::create([
        'name' => 'Test Campaign',
        'status' => CampaignStatus::Active,
    ]);

    $this->step = MarketingCampaignStep::create([
        'campaign_id' => $this->campaign->id,
        'order' => 1,
        'type' => CampaignStepType::Letter,
        'delay_days' => 0,
        'name' => 'Initial Letter',
    ]);

    $this->enrollment = MarketingLeadCampaign::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'status' => LeadCampaignStatus::InProgress,
        'current_step_order' => 1,
        'enrolled_at' => now(),
    ]);

    $this->mailing = MarketingMailing::create([
        'lead_campaign_id' => $this->enrollment->id,
        'campaign_step_id' => $this->step->id,
        'provider' => MailProvider::PostGrid,
        'provider_id' => 'letter_test123',
        'provider_status' => MailingStatus::Ready,
        'executed_at' => now(),
    ]);
});

it('rejects webhooks without signature', function () {
    $response = $this->postJson('/webhooks/postgrid', [
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'printing'],
    ]);

    $response->assertStatus(401);
});

it('rejects webhooks with invalid signature', function () {
    $response = $this->postJson('/webhooks/postgrid', [
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'printing'],
    ], [
        'PostGrid-Signature' => 't='.time().',v1=invalid_signature',
    ]);

    $response->assertStatus(401);
});

it('rejects webhooks with stale timestamp', function () {
    $timestamp = time() - 600; // 10 minutes ago
    $body = json_encode([
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'printing'],
    ]);
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'test_secret');

    $response = $this->call('POST', '/webhooks/postgrid', [], [], [], [
        'HTTP_PostGrid-Signature' => "t={$timestamp},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $response->assertStatus(401);
});

it('accepts webhooks with valid signature', function () {
    $timestamp = time();
    $body = json_encode([
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'printing'],
    ]);
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'test_secret');

    $response = $this->call('POST', '/webhooks/postgrid', [], [], [], [
        'HTTP_PostGrid-Signature' => "t={$timestamp},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $response->assertStatus(200);
});

it('updates mailing status from webhook', function () {
    $timestamp = time();
    $body = json_encode([
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'printing'],
    ]);
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'test_secret');

    $this->call('POST', '/webhooks/postgrid', [], [], [], [
        'HTTP_PostGrid-Signature' => "t={$timestamp},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $this->mailing->refresh();

    expect($this->mailing->provider_status)->toBe(MailingStatus::Printing);
});

it('sets delivered_at when status is completed', function () {
    expect($this->mailing->delivered_at)->toBeNull();

    $timestamp = time();
    $body = json_encode([
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'completed'],
    ]);
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'test_secret');

    $this->call('POST', '/webhooks/postgrid', [], [], [], [
        'HTTP_PostGrid-Signature' => "t={$timestamp},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $this->mailing->refresh();

    expect($this->mailing->provider_status)->toBe(MailingStatus::Completed);
    expect($this->mailing->delivered_at)->not->toBeNull();
});

it('sets failed_at when status is cancelled', function () {
    expect($this->mailing->failed_at)->toBeNull();

    $timestamp = time();
    $body = json_encode([
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'cancelled'],
    ]);
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'test_secret');

    $this->call('POST', '/webhooks/postgrid', [], [], [], [
        'HTTP_PostGrid-Signature' => "t={$timestamp},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $this->mailing->refresh();

    expect($this->mailing->provider_status)->toBe(MailingStatus::Cancelled);
    expect($this->mailing->failed_at)->not->toBeNull();
});

it('ignores webhooks for unknown provider_id', function () {
    $timestamp = time();
    $body = json_encode([
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_unknown', 'status' => 'printing'],
    ]);
    $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'test_secret');

    $response = $this->call('POST', '/webhooks/postgrid', [], [], [], [
        'HTTP_PostGrid-Signature' => "t={$timestamp},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    // Should still return 200 (acknowledged)
    $response->assertStatus(200);

    // But mailing should not be updated
    $this->mailing->refresh();
    expect($this->mailing->provider_status)->toBe(MailingStatus::Ready);
});

it('accepts webhooks with millisecond timestamps', function () {
    // PostGrid sends timestamps in milliseconds
    $timestampMs = (int) (microtime(true) * 1000);
    $body = json_encode([
        'type' => 'letter.updated',
        'data' => ['id' => 'letter_test123', 'status' => 'printing'],
    ]);
    // Signature is computed using the raw millisecond timestamp
    $signature = hash_hmac('sha256', $timestampMs.'.'.$body, 'test_secret');

    $response = $this->call('POST', '/webhooks/postgrid', [], [], [], [
        'HTTP_PostGrid-Signature' => "t={$timestampMs},v1={$signature}",
        'CONTENT_TYPE' => 'application/json',
    ], $body);

    $response->assertStatus(200);

    $this->mailing->refresh();
    expect($this->mailing->provider_status)->toBe(MailingStatus::Printing);
});
