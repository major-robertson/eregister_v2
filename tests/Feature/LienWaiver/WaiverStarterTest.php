<?php

use App\Domains\Business\Models\Business;
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
            // No site navigation on a paid-traffic page.
            ->assertDontSee('Form a Business');
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
