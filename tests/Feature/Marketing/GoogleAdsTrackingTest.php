<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Livewire\Waivers\WaiverShow;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
| What Google hears about: the click that brought a signup, the email for
| enhanced conversions (hashed by the Google tag in the browser), a GA4
| purchase on payment, and the moment a free user asks to e-sign.
*/

describe('ad click captured at signup', function () {
    it('stores the Google click id and the keyword UTMs on the new user', function () {
        // What an ad click looks like with auto-tagging plus the campaign's final URL suffix.
        $this->get('/lp/lien-waiver/tx?gclid=Cj0KCQ-test-click&utm_source=google&utm_medium=cpc&utm_campaign=24058363412&utm_content=178800000001&utm_term=texas%20lien%20waiver%20form');

        $this->post(route('register.store'), [
            'first_name' => 'Gina',
            'last_name' => 'Click',
            'email' => 'gina.click@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasNoErrors();

        $user = User::where('email', 'gina.click@example.com')->firstOrFail();

        expect($user->signup_gclid)->toBe('Cj0KCQ-test-click');
        expect($user->signup_utm_campaign)->toBe('24058363412');
        expect($user->signup_utm_content)->toBe('178800000001');
        expect($user->signup_utm_term)->toBe('texas lien waiver form');

        // First-touch values don't leak into the next signup from this browser.
        expect(session('signup_gclid'))->toBeNull();
    });

    it('keeps the first click and ignores junk values', function () {
        $this->get('/?gclid=first-click');
        $this->get('/?gclid=second-click');
        expect(session('signup_gclid'))->toBe('first-click');

        session()->forget('signup_gclid');
        $this->get('/?gclid[]=x');
        $this->get('/?gclid='.str_repeat('a', 300));
        expect(session('signup_gclid'))->toBeNull();
    });
});

describe('payment success page', function () {
    beforeEach(function () {
        $this->user = User::factory()->create(['email' => 'payer@example.com', 'email_verified_at' => now()]);
        $this->business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'lien_onboarding_completed_at' => now(),
        ]);
        $this->business->users()->attach($this->user, ['role' => 'owner']);

        $this->actingAs($this->user);
        session(['current_business_id' => $this->business->id]);

        $this->payment = Payment::factory()->succeeded()->create([
            'business_id' => $this->business->id,
            'purchasable_type' => $this->business->getMorphClass(),
            'purchasable_id' => $this->business->id,
            'price_id' => Price::resolve('lien', 'lien_waiver', 'monthly', 'subscription')->id,
            'billing_type' => 'subscription',
            'amount_cents' => 4900,
            'stripe_payment_intent_id' => 'pi_test_tracking',
        ]);
    });

    it('sends Google the sale once: Ads conversion, GA4 purchase, and the email for matching', function () {
        $response = $this->get(route('lien.waivers.payment-confirmation', ['payment_intent' => 'pi_test_tracking']))
            ->assertSuccessful();

        $response->assertSee('AW-984288380/_vCOCIitwbgZEPyYrNUD', false);
        $response->assertSee("gtag('event', 'purchase'", false);
        $response->assertSee("item_name: 'Lien Waiver Pro'", false);
        $response->assertSee("transaction_id: '".$this->payment->id."'", false);
        $response->assertSee('value: 49.00', false);
        $response->assertSee("gtag('set', 'user_data'", false);
        $response->assertSee('payer@example.com', false);

        // The email is set before the conversion events that use it.
        $html = $response->getContent();
        expect(strpos($html, "gtag('set', 'user_data'"))->toBeLessThan(strpos($html, "gtag('event', 'conversion'"));
    });

    it('sends nothing when the page is revisited without the payment id Stripe appends', function () {
        $this->get(route('lien.waivers.payment-confirmation'))
            ->assertSuccessful()
            ->assertDontSee("gtag('event', 'purchase'", false)
            ->assertDontSee("gtag('set', 'user_data'", false)
            ->assertDontSee('AW-984288380/_vCOCIitwbgZEPyYrNUD', false);
    });
});

describe('sign-up conversion', function () {
    it('hands the Google tag the new user email before the conversion fires', function () {
        $this->post(route('register.store'), [
            'first_name' => 'Nora',
            'last_name' => 'New',
            'email' => 'nora.new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'nora.new@example.com')->firstOrFail();
        $this->get('/portal');

        $business = Business::create(['name' => 'Fresh Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);
        session(['current_business_id' => $business->id]);

        $html = $this->get(route('portal.onboarding'))->assertOk()->getContent();

        expect($html)->toContain("gtag('set', 'user_data'")->toContain('nora.new@example.com');
        expect(strpos($html, "gtag('set', 'user_data'"))->toBeLessThan(strpos($html, 'AW-984288380/XDg5CMWk_7oZEPyYrNUD'));
    });
});

describe('e-sign upsell event', function () {
    it('tells GA4 when a free user asks to sign and meets the upgrade prompt', function () {
        Storage::fake('s3');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $business = Business::factory()->create([
            'onboarding_completed_at' => now(),
            'lien_onboarding_completed_at' => now(),
        ]);
        $business->users()->attach($user, ['role' => 'owner']);
        $this->actingAs($user);
        session(['current_business_id' => $business->id]);

        $project = LienProject::factory()->forBusiness($business)->inState('CO')->create(['wizard_completed_at' => now()]);
        $waiver = LienWaiver::factory()->forProject($project)->generated()->create(['created_by_user_id' => $user->id]);

        $component = Livewire::test(WaiverShow::class, ['waiver' => $waiver])
            ->call('sendForSignature')
            ->assertSet('showUpsellModal', true);

        // $this->js() effects ride back to the browser with the response.
        expect(json_encode($component->effects['xjs'] ?? []))
            ->toContain('esign_upsell_shown')
            ->toContain('waiver_page')
            ->toContain('provide');
    });
});

describe('software landing page', function () {
    it('sells collecting waivers, shows the price, and opens the starter on Collect', function () {
        $this->get('/lp/lien-waiver-software')
            ->assertSuccessful()
            ->assertSee('Collect Lien Waivers Without Chasing Anyone')
            ->assertSee('$49 a month per person')
            ->assertSee('Send your first waiver')
            ->assertSee('noindex', false)
            ->assertDontSee('Free Lien Waiver Form Generator');
    });

    it('counts as a waiver signup, so the user lands in the waiver wizard with follow-up emails', function () {
        $user = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver-software']);

        expect($user->signedUpFromWaivers())->toBeTrue();
        expect($user->signedUpFromLiens())->toBeTrue();
    });

    it('leaves the form landing pages as they were', function () {
        $this->get('/lp/lien-waiver/tx')
            ->assertSuccessful()
            ->assertSee('Free Texas Lien Waiver Form')
            ->assertDontSee('Collect Lien Waivers Without Chasing Anyone');
    });
});

describe('ad tags load in production only', function () {
    it('keeps local and test browsing out of Analytics and Ads, with no-op stand-ins for page events', function () {
        $this->get('/liens/lien-waivers')
            ->assertSuccessful()
            ->assertDontSee('googletagmanager.com', false)
            ->assertDontSee('redditstatic.com', false)
            ->assertDontSee('bzrcdn.openai.com', false)
            ->assertSee('function gtag(){}', false);
    });

    it('loads the real tags in production', function () {
        app()->detectEnvironment(fn () => 'production');

        $this->get('/liens/lien-waivers')
            ->assertSuccessful()
            ->assertSee('googletagmanager.com/gtag/js?id=G-MSVBK7VE6P', false)
            ->assertSee("gtag('config', 'AW-984288380')", false)
            ->assertSee('redditstatic.com', false)
            ->assertDontSee('function gtag(){}', false);
    });

    it('still leaves admin pages untagged in production', function () {
        app()->detectEnvironment(fn () => 'production');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.lien-stats'))
            ->assertSuccessful()
            ->assertDontSee('googletagmanager.com', false);
    });
});
