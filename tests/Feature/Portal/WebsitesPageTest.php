<?php

use App\Domains\Business\Models\Business;
use App\Mail\HappyWebsitesRequest;
use App\Models\User;
use App\Support\HappyWebsites;
use Illuminate\Support\Facades\Mail;

/**
 * A customer with one finished business, the way the portal sees them.
 *
 * @return array{0: User, 1: Business}
 */
function websitesCustomer(array $userAttributes = []): array
{
    $user = User::factory()->create($userAttributes);
    $business = Business::create([
        'name' => 'Smith Roofing',
        'business_address' => ['line1' => '1 Main St', 'city' => 'Louisville', 'state' => 'KY', 'zip' => '40202'],
        'onboarding_completed_at' => now(),
    ]);
    $user->businesses()->attach($business->id, ['role' => 'owner']);

    return [$user, $business];
}

describe('Portal Websites page', function () {
    it('sends guests to login', function () {
        $this->get('/portal/websites')->assertRedirect(route('login'));
        $this->post('/portal/websites')->assertRedirect(route('login'));
    });

    it('shows the offer with the business name, the prices and the request button', function () {
        [$user] = websitesCustomer();

        $this->actingAs($user)
            ->get('/portal/websites')
            ->assertOk()
            ->assertSee('A website for Smith Roofing')
            ->assertSee('Built and run by Happy Websites, our sister company.')
            ->assertSee('A free mockup first. $99 a month only if you like it.')
            ->assertSee('Happy Websites will design a website for Smith Roofing and show it to you.')
            ->assertSee('Same features in both. Only the number of pages changes.')
            ->assertSee('Make my free mockup')
            ->assertSee('Want to see your mockup?')
            // Step 1 says what the request email carries (see HappyWebsitesRequest).
            ->assertSee('We send Happy Websites your name, email, business name, city and state.')
            ->assertSee('https://happywebsites.com/work/painting-company?utm_source=eregister&utm_medium=portal&utm_campaign=websites')
            ->assertSee('https://happywebsites.com/work?utm_source=eregister&utm_medium=portal&utm_campaign=websites')
            ->assertDontSee('Happy Websites has your request');
    });

    it('emails the request to Happy Websites once and confirms it', function () {
        Mail::fake();
        [$user] = websitesCustomer();

        $this->actingAs($user)
            ->post('/portal/websites')
            ->assertRedirect(route('portal.websites'))
            ->assertSessionHas('gtag.queued');

        Mail::assertSent(HappyWebsitesRequest::class, function (HappyWebsitesRequest $mail) use ($user) {
            return $mail->hasTo('hello@happywebsites.com')
                && $mail->hasReplyTo($user->email)
                && $mail->hasSubject('Free mockup request: Smith Roofing (eRegister customer)');
        });
        expect(HappyWebsites::requestedBy($user))->toBeTrue();

        // A second click sends nothing more.
        $this->actingAs($user)->post('/portal/websites')->assertRedirect(route('portal.websites'));
        Mail::assertSentCount(1);

        $this->actingAs($user)
            ->get('/portal/websites')
            ->assertOk()
            ->assertSee('Done. Happy Websites has your request.')
            ->assertSee($user->email)
            ->assertSee('Have a logo or photos? Keep them handy.')
            // Both buttons are gone: the one in the offer card and the bottom band.
            ->assertDontSee('Make my free mockup')
            ->assertDontSee('Want to see your mockup?');
    });

    it('sends the request to every inbox on the list', function () {
        Mail::fake();
        config()->set('happy_websites.lead_email', ['hello@happywebsites.com', 'owner@example.com']);
        [$user] = websitesCustomer();

        $this->actingAs($user)->post('/portal/websites')->assertRedirect(route('portal.websites'));

        Mail::assertSent(HappyWebsitesRequest::class, function (HappyWebsitesRequest $mail) {
            return $mail->hasTo('hello@happywebsites.com') && $mail->hasTo('owner@example.com');
        });
        Mail::assertSentCount(1);
    });

    it('tells Happy Websites who asked and where they are', function () {
        [$user, $business] = websitesCustomer(['first_name' => 'Dana', 'last_name' => 'Smith']);

        (new HappyWebsitesRequest($user, $business))
            ->assertSeeInText('Dana Smith asked for a free website mockup')
            ->assertSeeInText("Email: {$user->email}")
            ->assertSeeInText('Business: Smith Roofing')
            ->assertSeeInText('Location: Louisville, KY');
    });

    it('lets the customer try again when the send fails', function () {
        [$user] = websitesCustomer();
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Test transport failure'));

        $this->actingAs($user)
            ->post('/portal/websites')
            ->assertRedirect(route('portal.websites'))
            ->assertSessionHas('error');

        expect(HappyWebsites::requestedBy($user))->toBeFalse();
    });

    it('never emails Happy Websites for a test account', function () {
        Mail::fake();
        [$user] = websitesCustomer(['email' => 'websites-check@test.test']);

        $this->actingAs($user)->post('/portal/websites')->assertRedirect(route('portal.websites'));

        Mail::assertNothingSent();
        expect(HappyWebsites::requestedBy($user))->toBeTrue();
    });
});

describe('Websites offer around the site', function () {
    it('shows on the portal home and in the sidebar until the customer asks', function () {
        Mail::fake();
        [$user] = websitesCustomer();

        $this->actingAs($user)
            ->get('/portal')
            ->assertOk()
            ->assertSee('Could Smith Roofing use a new website?')
            ->assertSee('href="'.route('portal.websites').'"', false);

        $this->actingAs($user)->post('/portal/websites');

        // The card goes away. The sidebar item stays.
        $this->actingAs($user)
            ->get('/portal')
            ->assertOk()
            ->assertDontSee('Could Smith Roofing use a new website?')
            ->assertSee('href="'.route('portal.websites').'"', false);
    });

    it('links to Happy Websites from the public footer', function () {
        $this->get('/')
            ->assertOk()
            ->assertSee('Small Business Websites')
            ->assertSee('https://happywebsites.com/?utm_source=eregister&utm_medium=site&utm_campaign=websites');
    });
});
