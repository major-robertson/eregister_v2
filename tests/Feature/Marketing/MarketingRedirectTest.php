<?php

use App\Domains\Marketing\Models\MarketingRedirect;
use App\Domains\Marketing\Models\MarketingRedirectVisit;

beforeEach(function () {
    $this->redirect = MarketingRedirect::create([
        'slug' => 'plumbingforums',
        'destination_path' => '/liens',
        'utm_source' => 'plumbingforums',
        'utm_medium' => 'banner',
        'utm_campaign' => 'plumbingforums-banner',
    ]);
});

it('redirects to destination with utm params', function () {
    $response = $this->get('/r/plumbingforums');

    $response->assertRedirect('/liens?utm_source=plumbingforums&utm_medium=banner&utm_campaign=plumbingforums-banner');
});

it('records a visit on redirect', function () {
    expect(MarketingRedirectVisit::count())->toBe(0);

    $this->get('/r/plumbingforums');

    expect(MarketingRedirectVisit::count())->toBe(1);

    $visit = MarketingRedirectVisit::first();
    expect($visit->marketing_redirect_id)->toBe($this->redirect->id);
    expect($visit->visited_at)->not->toBeNull();
});

it('captures ip address and user agent on visit', function () {
    $this->get('/r/plumbingforums', [
        'User-Agent' => 'TestBrowser/1.0',
    ]);

    $visit = MarketingRedirectVisit::first();
    expect($visit->ip_address)->not->toBeNull();
    expect($visit->user_agent)->toBe('TestBrowser/1.0');
});

it('captures referrer on visit', function () {
    $this->get('/r/plumbingforums', [
        'Referer' => 'https://www.plumbingforums.com/thread/123',
    ]);

    $visit = MarketingRedirectVisit::first();
    expect($visit->referrer)->toBe('https://www.plumbingforums.com/thread/123');
});

it('returns 404 for unknown slug', function () {
    $response = $this->get('/r/nonexistent-campaign');

    $response->assertNotFound();
    expect(MarketingRedirectVisit::count())->toBe(0);
});

it('returns 404 for inactive redirect', function () {
    $this->redirect->update(['is_active' => false]);

    $response = $this->get('/r/plumbingforums');

    $response->assertNotFound();
    expect(MarketingRedirectVisit::count())->toBe(0);
});

it('includes optional utm_term and utm_content when set', function () {
    $this->redirect->update([
        'utm_term' => 'lien-filing',
        'utm_content' => 'top-banner',
    ]);

    $response = $this->get('/r/plumbingforums');

    $response->assertRedirect(
        '/liens?utm_source=plumbingforums&utm_medium=banner&utm_campaign=plumbingforums-banner&utm_term=lien-filing&utm_content=top-banner'
    );
});

it('omits null utm params from redirect url', function () {
    $redirect = MarketingRedirect::create([
        'slug' => 'simple',
        'destination_path' => '/liens',
        'utm_source' => 'test',
    ]);

    $response = $this->get('/r/simple');

    $response->assertRedirect('/liens?utm_source=test');
});

it('stores utm params in session after redirect via attribution middleware', function () {
    $response = $this->get('/r/plumbingforums');
    $redirectUrl = $response->headers->get('Location');

    $this->get($redirectUrl);

    expect(session('signup_utm_source'))->toBe('plumbingforums');
    expect(session('signup_utm_medium'))->toBe('banner');
    expect(session('signup_utm_campaign'))->toBe('plumbingforums-banner');
});

it('tracks multiple visits from the same redirect', function () {
    $this->get('/r/plumbingforums');
    $this->get('/r/plumbingforums');
    $this->get('/r/plumbingforums');

    expect(MarketingRedirectVisit::count())->toBe(3);
    expect(MarketingRedirectVisit::where('marketing_redirect_id', $this->redirect->id)->count())->toBe(3);
});
