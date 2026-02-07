<?php

use App\Domains\Marketing\Enums\CampaignStatus;
use App\Domains\Marketing\Enums\DestinationType;
use App\Domains\Marketing\Enums\VisitSource;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingLead;
use App\Domains\Marketing\Models\MarketingTrackingLink;
use App\Domains\Marketing\Models\MarketingVisit;

beforeEach(function () {
    $this->campaign = MarketingCampaign::create([
        'name' => 'Liens Campaign',
        'landing_key' => 'liens',
        'status' => CampaignStatus::Active,
    ]);

    // Note: MarketingLead::created hook auto-creates a vanity tracking link
    $this->lead = MarketingLead::create([
        'business_name' => 'Test Contracting LLC',
        'contact_name' => 'John Doe',
        'mailing_address' => '123 Main St',
        'mailing_city' => 'Louisville',
        'mailing_state' => 'KY',
        'mailing_zip' => '40258',
    ]);

    // Get the auto-created vanity tracking link and update with campaign
    $this->vanityLink = MarketingTrackingLink::where('token', $this->lead->slug)->first();
    $this->vanityLink->update(['campaign_id' => $this->campaign->id]);
});

it('renders the full liens page via slug (vanity link)', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $response->assertSee('File liens.');
    $response->assertSee('Start free lien tracking');
});

it('renders the full liens page via tracking token', function () {
    // Create a QR tracking link (random token, separate from vanity link)
    $qrLink = MarketingTrackingLink::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'destination_type' => DestinationType::LeadLanding,
        'destination' => $this->lead->slug,
    ]);

    $response = $this->get("/go/t/{$qrLink->token}");

    $response->assertStatus(200);
    $response->assertSee('File liens.');
    $response->assertSee('Start free lien tracking');
});

it('shows personalized subtitle with business name', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $response->assertSee('Test Contracting LLC, file a mechanics lien on property with confidence.');
});

it('shows project card when lead has property data', function () {
    $this->lead->update([
        'property_address' => '456 Oak Street',
        'property_city' => 'Louisville',
        'property_state' => 'KY',
    ]);

    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $response->assertSee('Your recent job');
    $response->assertSee('456 Oak Street');
    $response->assertSee('Louisville, KY');
    $response->assertSee('Track this job free');
    $response->assertSee('File a lien / send notice');
});

it('hides project card when lead has no property data', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $response->assertDontSee('Your recent job');
});

it('shows default subtitle when lead has no business name', function () {
    $leadNoName = MarketingLead::create([
        'contact_name' => 'Jane Doe',
        'mailing_address' => '456 Oak Ave',
        'mailing_city' => 'Chicago',
        'mailing_state' => 'IL',
        'mailing_zip' => '60601',
    ]);

    $vanityLink = MarketingTrackingLink::where('token', $leadNoName->slug)->first();
    $vanityLink->update(['campaign_id' => $this->campaign->id]);

    $response = $this->get("/go/{$leadNoName->slug}");

    $response->assertStatus(200);
    $response->assertSee('File a mechanics lien on property with confidence.');
    $response->assertSee('Start free lien tracking');
});

it('includes noindex meta tag on marketing landing', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $response->assertSee('<meta name="robots" content="noindex, nofollow"', false);
});

it('includes canonical URL on marketing landing', function () {
    $response = $this->get("/go/{$this->lead->slug}");

    $response->assertStatus(200);
    $expectedCanonical = route('marketing.landing.slug', ['slug' => $this->lead->slug]);
    $response->assertSee($expectedCanonical);
});

it('records visit with source=direct for slug route', function () {
    expect(MarketingVisit::count())->toBe(0);

    $this->get("/go/{$this->lead->slug}");

    expect(MarketingVisit::count())->toBe(1);

    $visit = MarketingVisit::first();
    expect($visit->lead_id)->toBe($this->lead->id);
    expect($visit->source)->toBe(VisitSource::Direct);
    expect($visit->tracking_link_id)->toBe($this->vanityLink->id);
});

it('records visit with source=qr_scan for token route', function () {
    // Create a QR tracking link
    $qrLink = MarketingTrackingLink::create([
        'lead_id' => $this->lead->id,
        'campaign_id' => $this->campaign->id,
        'destination_type' => DestinationType::LeadLanding,
        'destination' => $this->lead->slug,
    ]);

    expect(MarketingVisit::count())->toBe(0);

    $this->get("/go/t/{$qrLink->token}");

    expect(MarketingVisit::count())->toBe(1);

    $visit = MarketingVisit::first();
    expect($visit->lead_id)->toBe($this->lead->id);
    expect($visit->source)->toBe(VisitSource::QrScan);
    expect($visit->tracking_link_id)->toBe($qrLink->id);
});

it('returns 404 for invalid token', function () {
    $response = $this->get('/go/t/invalid-token-123');

    $response->assertStatus(404);
});

it('returns 404 for invalid slug', function () {
    $response = $this->get('/go/nonexistent-company');

    $response->assertStatus(404);
});

it('creates vanity tracking link automatically on lead creation', function () {
    $newLead = MarketingLead::create([
        'business_name' => 'New Company',
        'contact_name' => 'Jane Doe',
        'mailing_address' => '456 Oak Ave',
        'mailing_city' => 'Chicago',
        'mailing_state' => 'IL',
        'mailing_zip' => '60601',
    ]);

    $vanityLink = MarketingTrackingLink::where('lead_id', $newLead->id)
        ->where('token', $newLead->slug)
        ->first();

    expect($vanityLink)->not->toBeNull();
    expect($vanityLink->destination_type)->toBe(DestinationType::LeadLanding);
    expect($vanityLink->mailing_id)->toBeNull();
});

it('cleans business name suffixes correctly', function () {
    $testCases = [
        'Acme LLC' => 'Acme',
        'Acme, LLC' => 'Acme',
        'Acme, Inc.' => 'Acme',
        'Acme Inc' => 'Acme',
        'Acme, L.L.C.' => 'Acme',
        'Acme, Ltd.' => 'Acme',
        'Acme, Co.' => 'Acme',
        'Acme Corp.' => 'Acme',
        'Acme Corporation' => 'Acme',
        'Acme Incorporated' => 'Acme',
        'Smith Construction' => 'Smith Construction',
        'Big Build Co.' => 'Big Build',
    ];

    foreach ($testCases as $input => $expected) {
        $lead = new MarketingLead(['business_name' => $input]);
        expect($lead->cleaned_business_name)->toBe($expected, "Failed for input: {$input}");
    }
});
