<?php

use App\Domains\Business\Models\Business;
use App\Domains\Business\Models\BusinessInvitation;
use App\Domains\Lien\Waivers\WaiverFormPreview;
use App\Domains\Lien\Waivers\WaiverFormResolver;
use App\Domains\Lien\Waivers\WaiverIntent;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Models\User;

describe('start route', function () {
    it('stores the intent, credits the signup to the waiver page, and sends guests to register', function () {
        $this->get('/liens/lien-waivers/start?state=tx&direction=provide&kind=conditional_progress&from=/lp/lien-waiver/tx')
            ->assertRedirect(route('register'))
            ->assertSessionHas('waiver_intent', [
                'state' => 'TX',
                'direction' => 'provide',
                'kind' => 'conditional_progress',
                'source' => '/lp/lien-waiver/tx',
            ])
            ->assertSessionHas('signup_landing_path', '/lp/lien-waiver/tx');

        // The starter event is queued for the next full page render.
        $queued = session('gtag.queued');
        expect($queued)->toHaveCount(1);
        expect($queued[0]['name'])->toBe('waiver_starter_submit');
        expect($queued[0]['params'])->toMatchArray(['state' => 'TX', 'direction' => 'provide']);
    });

    it('drains queued events into the next page', function () {
        $this->get('/liens/lien-waivers/start?state=ca');

        $this->get(route('register'))
            ->assertSuccessful()
            ->assertSee("gtag('event', 'waiver_starter_submit'", false);

        expect(session('gtag.queued'))->toBeNull();
    });

    it('requires a supported state and rejects unknown directions, kinds, and external sources', function () {
        $this->from('/liens/lien-waivers')->get('/liens/lien-waivers/start?state=zz')
            ->assertRedirect('/liens/lien-waivers')
            ->assertSessionHasErrors('state');

        $this->from('/liens/lien-waivers')->get('/liens/lien-waivers/start?state=tx&direction=sideways')
            ->assertSessionHasErrors('direction');

        $this->from('/liens/lien-waivers')->get('/liens/lien-waivers/start?state=tx&kind=bogus')
            ->assertSessionHasErrors('kind');

        $this->from('/liens/lien-waivers')->get('/liens/lien-waivers/start?state=tx&from=https://evil.example')
            ->assertSessionHasErrors('from');

        expect(session('waiver_intent'))->toBeNull();
    });

    it('sends a logged-in user with a set-up business straight to the wizard', function () {
        $user = User::factory()->create();
        $business = Business::factory()->create(['onboarding_completed_at' => now()]);
        $business->users()->attach($user, ['role' => 'owner']);

        $this->actingAs($user)
            ->withSession(['current_business_id' => $business->id])
            ->get('/liens/lien-waivers/start?state=tx&kind=unconditional_final')
            ->assertRedirect(route('lien.waivers.create'));

        expect(WaiverIntent::get())->toMatchArray(['state' => 'TX', 'kind' => 'unconditional_final']);
    });

    it('sends a logged-in user without a business to business setup', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/liens/lien-waivers/start?state=tx')
            ->assertRedirect(route('portal.select-business'));
    });
});

describe('ads landing page', function () {
    it('renders the generic page with the starter and stays out of search', function () {
        $this->get('/lp/lien-waiver')
            ->assertSuccessful()
            ->assertSee('Free Lien Waiver Form Generator')
            ->assertSee('name="robots" content="noindex, nofollow"', false)
            ->assertSee(route('liens.lien-waivers.start'))
            ->assertSee('Create my free waiver')
            // A slim menu of its own, not the full site navigation.
            ->assertDontSee('Form a Business');
    });

    it('gives the visitor a way around: a slim menu, a fuller footer, and the business address', function () {
        config(['mail.postal_address' => '4869 Example Rd, Louisville KY 40207']);

        $this->get('/lp/lien-waiver/tx')
            ->assertSuccessful()
            // Menu links and the sections they jump to.
            ->assertSee('href="#how-it-works"', false)
            ->assertSee('id="how-it-works"', false)
            ->assertSee('href="#pricing"', false)
            ->assertSee('id="pricing"', false)
            ->assertSee('href="#questions"', false)
            ->assertSee('id="questions"', false)
            ->assertSee(route('liens.lien-waivers').'#states', false)
            ->assertSee(route('contact'), false)
            // Footer: who we are and where else to go.
            ->assertSee(route('liens.lien-waivers.pricing'), false)
            ->assertSee(route('liens'), false)
            ->assertSee('4869 Example Rd, Louisville KY 40207');

        // "Forms by state" has somewhere to land.
        $this->get('/liens/lien-waivers')->assertSee('id="states"', false);
    });

    it('leaves the address out of the footer when none is configured', function () {
        config(['mail.postal_address' => null]);

        // The FAQ names the city; the street address and ZIP only come from config.
        $this->get('/lp/lien-waiver')->assertSuccessful()->assertDontSee('KY 40207');
    });

    it('backs the promise with proof: the Google rating, years in business, and real reviews', function () {
        $this->get('/lp/lien-waiver/tx')
            ->assertSuccessful()
            ->assertSee('on Google')
            // Seven is too few to brag about, so the count stays off the page.
            ->assertDontSee('7 reviews')
            ->assertSee('In business since 2017')
            ->assertSee('href="#reviews"', false)
            ->assertSee('What contractors say about eRegister')
            ->assertSee('Everything was straightforward, communication was excellent')
            ->assertSee('Floors Kitchen & Bath Direct')
            ->assertSee("Reviews of eRegister's lien filing service.", false)
            ->assertSee(config('company.google_reviews.url'), false);

        // The owner asked to keep his first name out of quoted reviews.
        foreach (config('company.google_reviews.featured') as $review) {
            expect($review['text'])->not->toContain('Major');
        }
    });

    it('drops the proof line and the reviews when no rating is configured', function () {
        config(['company.google_reviews' => ['url' => null, 'rating' => null, 'count' => null, 'featured' => []]]);

        $this->get('/lp/lien-waiver')
            ->assertSuccessful()
            ->assertDontSee('on Google')
            ->assertDontSee('What contractors say about eRegister');
    });

    it('answers seven questions, and writes the notarization answer for the state', function () {
        $texas = $this->get('/lp/lien-waiver/tx')->assertSuccessful();
        $texas->assertSee('Not in Texas.')
            ->assertSee('Who is eRegister?')
            ->assertSee('in business since 2017')
            ->assertSee('Does the person I send a waiver to need an account to sign it?');
        expect(substr_count($texas->getContent(), '<details'))->toBe(7);

        $this->get('/lp/lien-waiver/ms')->assertSee('In Mississippi, yes.');
        $this->get('/lp/lien-waiver/ga')->assertSee('Georgia asks for a witness rather than a notary');
        $this->get('/lp/lien-waiver')->assertSee('Almost never.');
    });

    it('keeps a copy of the button fixed to the screen on phones, on the ads pages only', function () {
        foreach (['/lp/lien-waiver', '/lp/lien-waiver/tx', '/lp/lien-waiver-software'] as $path) {
            $this->get($path)->assertSuccessful()->assertSee('data-sticky-button', false);
        }

        $this->get('/liens/lien-waivers')->assertDontSee('data-sticky-button', false);
        $this->get('/liens/lien-waivers/tx')->assertDontSee('data-sticky-button', false);
    });

    it('renders a state page with its statutory titles and pre-selects the starter from the query', function () {
        $this->get('/lp/lien-waiver/tx?d=provide&type=conditional_final')
            ->assertSuccessful()
            ->assertSee('Free Texas Lien Waiver Form')
            ->assertSee('Tex. Prop. Code')
            ->assertSee('Conditional Waiver and Release on Final Payment')
            ->assertSee('value="TX" selected', false)
            ->assertSee("direction: 'provide'", false)
            ->assertSee("kind: 'conditional_final'", false);
    });

    it('shows the real form, sample-filled, for every waiver the state uses', function () {
        $response = $this->get('/lp/lien-waiver/tx')->assertSuccessful();

        expect(substr_count($response->getContent(), '<iframe'))->toBe(4);

        $response
            ->assertSee('data-waiver-preview', false)
            ->assertSee('Exact statutory text')
            ->assertSee('SAMPLE')
            // The generator's own document, framed: statutory wording and sample details.
            ->assertSee('On receipt by the signer of this document of a check from')
            ->assertSee('Tex. Prop. Code')
            ->assertSee('Acme Drywall LLC')
            ->assertSee('Anytown, TX')
            // The titles card it replaced is gone.
            ->assertDontSee('Conditional Progress');
    });

    it('opens the preview on the waiver type the ad asked for', function () {
        $this->get('/lp/lien-waiver/tx?type=unconditional_final')
            ->assertSuccessful()
            ->assertSee("{ kind: 'unconditional_final', scale: null }", false);
    });

    it('previews the house form, with the state left blank, on the all-states pages', function () {
        foreach (['/lp/lien-waiver', '/lp/lien-waiver-software'] as $path) {
            $response = $this->get($path)->assertSuccessful();

            expect(substr_count($response->getContent(), '<iframe'))->toBe(4);

            $response
                ->assertSee('Any state')
                ->assertSee('Attorney-reviewed form')
                ->assertSee('State of ____________')
                ->assertDontSee('Exact statutory text');
        }
    });

    it('only previews the waiver types a state uses', function () {
        foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
            $used = collect(app(WaiverFormResolver::class)->availableKinds($code))->where('enabled', true)->count();
            $previews = app(WaiverFormPreview::class)->for($code);

            expect($previews)->toHaveCount($used, $code);

            foreach ($previews as $preview) {
                expect($preview['html'])->toContain('<html', '</head>', 'Acme Drywall LLC');
            }
        }
    });

    it('keeps the preview off the search pages', function () {
        $this->get('/liens/lien-waivers/tx')->assertSuccessful()->assertDontSee('data-waiver-preview', false);
    });

    it('ignores unknown pre-selections', function () {
        $this->get('/lp/lien-waiver/tx?d=sideways&type=bogus')
            ->assertSuccessful()
            ->assertSee("direction: ''", false)
            ->assertSee("kind: ''", false);
    });

    it('301s uppercase state codes and 404s unknown ones', function () {
        $this->get('/lp/lien-waiver/TX')
            ->assertStatus(301)
            ->assertRedirect(route('lp.lien-waiver', ['state' => 'tx']));

        $this->get('/lp/lien-waiver/zz')->assertNotFound();
    });

    it('renders every state', function () {
        foreach (array_keys(WaiverStateRegistry::STATE_NAMES) as $code) {
            $this->get('/lp/lien-waiver/'.strtolower($code))->assertSuccessful();
        }
    });
});

describe('starter form', function () {
    it('lists collect before send, and offers "not sure" as an always-visible default instead of a pop-in', function () {
        $this->get('/lp/lien-waiver/tx')
            ->assertSuccessful()
            ->assertSeeInOrder(['Collect a waiver from someone I pay', 'Send a waiver to get paid'])
            ->assertSeeInOrder(['Unconditional', 'Final payment', 'Not sure — help me choose'])
            // The "not sure" choice is a real option with an empty value...
            ->assertSee('name="kind" value=""', false)
            // ...and the old link that appeared after a selection is gone.
            ->assertDontSee("kind !== ''", false);
    });

    it('accepts the empty "not sure" waiver type', function () {
        $this->get('/liens/lien-waivers/start?state=tx&direction=collect&kind=')
            ->assertRedirect(route('register'))
            ->assertSessionHas('waiver_intent', [
                'state' => 'TX',
                'direction' => 'collect',
                'kind' => null,
                'source' => null,
            ]);
    });
});

describe('register page after the starter', function () {
    it('keeps talking about the waiver the visitor picked', function () {
        $this->get('/liens/lien-waivers/start?state=tx&from=/lp/lien-waiver/tx')->assertRedirect(route('register'));

        $this->get(route('register'))
            ->assertSuccessful()
            // Account, business, the job, the waiver details.
            ->assertSee('Step 1 of 4')
            ->assertSee('Create your free account to finish your Texas lien waiver')
            ->assertSee('Free. No credit card.')
            ->assertSee('Create my free account')
            ->assertDontSee('Enter your details below');
    });

    it('greets everyone else the usual way', function () {
        $this->get(route('register'))
            ->assertSuccessful()
            ->assertSee('Create an account')
            ->assertSee('Enter your details below')
            ->assertDontSee('Step 1 of');
    });

    it('lets a team invitation explain itself instead', function () {
        $business = Business::factory()->onboarded()->create(['name' => 'Acme Contracting']);
        $owner = User::factory()->create();
        $owner->businesses()->attach($business->id, ['role' => 'owner']);
        $invitation = BusinessInvitation::factory()->create([
            'business_id' => $business->id,
            'email' => 'invitee@example.com',
            'role' => 'member',
            'invited_by_user_id' => $owner->id,
        ]);

        $this->get('/liens/lien-waivers/start?state=tx')->assertRedirect(route('register'));
        $this->get($invitation->acceptUrl())->assertRedirect(route('register'));

        $this->get(route('register'))
            ->assertSuccessful()
            ->assertSee('Acme Contracting')
            ->assertDontSee('Step 1 of');
    });
});

describe('marketing pages', function () {
    it('puts the starter on the generator page and the state pages', function () {
        $this->get('/liens/lien-waivers')
            ->assertSuccessful()
            ->assertSee(route('liens.lien-waivers.start'))
            ->assertSee('Create my free waiver');

        $this->get('/liens/lien-waivers/tx')
            ->assertSuccessful()
            ->assertSee('Create your Texas waiver')
            ->assertSee('value="TX" selected', false);
    });
});

describe('signup attribution', function () {
    it('treats the waiver pages and the ads pages as waiver signups, which are lien signups too', function () {
        foreach (['/liens/lien-waivers', '/liens/lien-waivers/tx', '/lp/lien-waiver', '/lp/lien-waiver/ca'] as $path) {
            $user = User::factory()->make(['signup_landing_path' => $path]);

            expect($user->signedUpFromWaivers())->toBeTrue($path);
            expect($user->signedUpFromLiens())->toBeTrue($path);
        }

        $user = User::factory()->make(['signup_landing_path' => '/liens/preliminary-notice']);
        expect($user->signedUpFromWaivers())->toBeFalse();
        expect($user->signedUpFromLiens())->toBeTrue();

        expect(User::factory()->make(['signup_landing_path' => null])->signedUpFromWaivers())->toBeFalse();
    });
});
