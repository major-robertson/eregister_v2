<?php

use App\Domains\Business\Livewire\OnboardingWizard;
use App\Domains\Business\Models\Business;
use App\Models\User;
use App\Support\SignupIntent;
use Livewire\Livewire;

describe('SignupIntent', function () {
    it('keeps only known products, real states, and slug-shaped intents', function () {
        expect(SignupIntent::normalize([
            'product' => 'sales-tax',
            'intent' => 'sellers-permit',
            'state' => 'tx',
            'source' => 'ads',
        ]))->toBe(['product' => 'sales-tax', 'intent' => 'sellers-permit', 'state' => 'TX', 'source' => 'ads']);

        expect(SignupIntent::normalize([
            'product' => 'crypto',
            'intent' => 'DROP TABLE users; --',
            'state' => 'ZZ',
        ]))->toBe(['product' => null, 'intent' => null, 'state' => null, 'source' => null]);
    });

    it('is stored when the register page is opened with product and state in the query string', function () {
        $this->get('/register?product=sales-tax&intent=sellers-permit&state=ca')
            ->assertOk()
            ->assertSee('Create your account to start your California sales tax registration')
            ->assertSee('Step 1 of 3');

        expect(SignupIntent::get())->toBe(['product' => 'sales-tax', 'intent' => 'sellers-permit', 'state' => 'CA', 'source' => null]);
        expect(session('signup_landing_path'))->toBe('/sales-tax-registration');
    });

    it('names the generator on the register page for a resale certificate visitor', function () {
        $this->get('/register?product=resale-cert')
            ->assertOk()
            ->assertSee('Create your account to generate resale certificates');

        expect(session('signup_landing_path'))->toBe('/resale-certificates');
    });

    it('does not overwrite a landing path captured from the referer', function () {
        $this->withHeader('referer', config('app.url').'/liens/preliminary-notice')
            ->get('/register?product=sales-tax')
            ->assertOk();

        expect(session('signup_landing_path'))->toBe('/liens/preliminary-notice');
        expect(SignupIntent::product())->toBe('sales-tax');
    });

    it('survives a plain reload of the register page', function () {
        $this->get('/register?product=resale-cert&state=NY')->assertOk();
        $this->get('/register')->assertOk()->assertSee('Create your account to generate New York resale certificates');

        expect(SignupIntent::state())->toBe('NY');
    });

    it('shows the plain heading when nothing was passed along', function () {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Create an account')
            ->assertDontSee('Step 1 of 3');
    });

    it('sends a sales tax sign-up to the state selector after business setup even without a landing path', function () {
        $user = User::factory()->create(['signup_landing_path' => null]);
        $business = Business::create(['name' => 'Intent Business', 'legal_name' => 'Intent Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)->withSession(['current_business_id' => $business->id]);
        SignupIntent::store(['product' => 'sales-tax', 'state' => 'TX']);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '100 Test Street')
            ->set('businessAddress.city', 'Austin')
            ->set('businessAddress.state', 'TX')
            ->set('businessAddress.zip', '78701')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('sales-tax.registrations.start'));
    });

    it('sends a resale certificate sign-up to the resale dashboard after business setup', function () {
        $user = User::factory()->create(['signup_landing_path' => '/']);
        $business = Business::create(['name' => 'Resale Business', 'legal_name' => 'Resale Business']);
        $user->businesses()->attach($business->id, ['role' => 'owner']);

        $this->actingAs($user)->withSession(['current_business_id' => $business->id]);
        SignupIntent::store(['product' => 'resale-cert']);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '100 Test Street')
            ->set('businessAddress.city', 'Austin')
            ->set('businessAddress.state', 'TX')
            ->set('businessAddress.zip', '78701')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('resale-cert.dashboard'));
    });

    it('leaves a second business on the dashboard even with a stored intent', function () {
        $user = User::factory()->create(['signup_landing_path' => '/sales-tax-registration']);
        $first = Business::create(['name' => 'First', 'legal_name' => 'First', 'onboarding_completed_at' => now()]);
        $second = Business::create(['name' => 'Second', 'legal_name' => 'Second']);
        $user->businesses()->attach($first->id, ['role' => 'owner']);
        $user->businesses()->attach($second->id, ['role' => 'owner']);

        $this->actingAs($user)->withSession(['current_business_id' => $second->id]);
        SignupIntent::store(['product' => 'sales-tax']);

        Livewire::test(OnboardingWizard::class)
            ->set('businessAddress.line1', '100 Test Street')
            ->set('businessAddress.city', 'Austin')
            ->set('businessAddress.state', 'TX')
            ->set('businessAddress.zip', '78701')
            ->call('complete')
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard'));
    });

    it('links the sales tax page button to register with the product, intent and state', function () {
        $this->get('/sales-tax-registration?intent=sellers-permit&state=ca')
            ->assertOk()
            ->assertSee('/register?product=sales-tax&amp;intent=sellers-permit&amp;state=CA', false);

        $this->get('/resale-certificates?state=ny')
            ->assertOk()
            ->assertSee('/register?product=resale-cert&amp;state=NY', false);
    });
});
