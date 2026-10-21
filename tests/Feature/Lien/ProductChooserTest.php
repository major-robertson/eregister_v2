<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Documents\LienDocumentRegistry;
use App\Domains\Lien\Engine\DeadlineCalculator;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Enums\FilingStatus;
use App\Domains\Lien\Livewire\FilingWizard;
use App\Domains\Lien\Livewire\LienProductChooser;
use App\Domains\Lien\Livewire\ProjectForm;
use App\Domains\Lien\Livewire\ProjectList;
use App\Domains\Lien\Livewire\ProjectShow;
use App\Domains\Lien\Models\LienFiling;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienStateRule;
use App\Domains\Lien\Seo\LienStatePage;
use App\Domains\Lien\Support\LienProductIntent;
use App\Models\User;
use App\Support\SignupIntent;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/*
 * "What do you need to happen?" (EREG-143): the product chooser on the
 * pricing page and the lien dashboard, its Start flow into sign-up or the
 * project flow, and the prerequisites on the wizard's first step. Today is
 * Tuesday, October 20, 2026.
 */

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-20 12:00:00', 'America/New_York')->utc());
    Storage::fake('s3');
    LienDocumentRegistry::flush();
});

afterEach(fn () => LienDocumentRegistry::flush());

if (! function_exists('chooserStart')) {
    /** The chooser's Start link for a product, with the state when given. */
    function chooserStart(string $product, ?string $state = null): string
    {
        return route('liens.start', array_filter(['product' => $product, 'state' => $state]));
    }

    /** A logged-in customer whose business has finished business and lien onboarding. */
    function chooserCustomer(object $test, array $attributes = []): Business
    {
        $test->user = User::factory()->create();
        $business = Business::factory()->onboarded()->withLienOnboarding()->create($attributes);
        $business->users()->attach($test->user, ['role' => 'owner']);

        $test->actingAs($test->user);
        session(['current_business_id' => $business->id]);

        return $business;
    }

    /** A project in $state with its deadlines; last furnished October 1, so every lien window is open. */
    function chooserProject(Business $business, string $state, ClaimantType $claimant = ClaimantType::Gc): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->create([
            'jobsite_state' => $state,
            'jobsite_county' => 'Test County',
            'claimant_type' => $claimant,
            'property_class' => 'residential',
            'first_furnish_date' => '2026-09-01',
            'last_furnish_date' => '2026-10-01',
            'completion_date' => '2026-10-01',
        ]);

        app(DeadlineCalculator::class)->calculateForProject($project);

        return $project;
    }
}

describe('cards', function () {
    it('shows six needs, with what each product does and does not do', function () {
        Livewire::test(LienProductChooser::class)
            ->assertSet('state', '')
            ->assertSeeInOrder([
                'Protect my right to lien later', 'Preliminary notice',
                'Warn them a lien is coming', 'Notice of intent to lien',
                'Put a lien on the property', 'Mechanics lien',
                'Ask for payment formally', 'Payment demand letter',
                'Remove a lien I filed', 'Lien release',
                'Public or government job', 'Bond claim',
            ])
            ->assertSee('It does not put a lien on the property.')
            ->assertSee('Liens do not attach to public property.')
            ->assertSee(route('contact'))
            ->assertSee(chooserStart('mechanics_lien'))
            ->assertSee('$299')
            ->assertDontSee('Deadline in')
            ->assertDontSee(chooserStart('bond_claim'));
    });

    it('names the state\'s deadline rule, price and turnaround on each card', function () {
        $lien = LienStatePage::forCode('CO')->headlineLienDeadline();

        expect($lien)->not->toBeNull();

        Livewire::test(LienProductChooser::class)
            ->set('state', 'co')
            ->assertSet('state', 'CO')
            ->assertSee('Colorado does not require one to keep lien rights.')
            ->assertSee('Deadline in Colorado: at least 10 days before the lien statement is filed.')
            ->assertSee("Deadline in Colorado: {$lien}.")
            ->assertSee('No deadline. You can send one at any time.')
            ->assertSee('Record it once you have been paid.')
            ->assertSee('Typically 1 to 5 business days after we submit.')
            ->assertSee(chooserStart('mechanics_lien', 'CO'))
            ->assertDontSee('$899');

        $prelim = LienStatePage::forCode('TX')->headlineDeadline('prelim_notice');

        Livewire::test(LienProductChooser::class)
            ->set('state', 'TX')
            ->assertSee("Deadline in Texas: {$prelim}.")
            ->assertSee('Texas does not require one. You can still send one before you file.')
            ->set('state', 'NJ')
            ->assertSee('$899');
    });

    it('starts a customer on their latest project\'s state, else their business address', function () {
        $business = chooserCustomer($this, ['business_address' => ['line1' => '1 Main St', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701']]);

        Livewire::test(LienProductChooser::class)->assertSet('state', 'TX');

        LienProject::factory()->forBusiness($business)->create(['jobsite_state' => 'FL']);

        Livewire::test(LienProductChooser::class)->assertSet('state', 'FL');
    });
});

describe('prerequisites', function () {
    it('names the notice of intent on the lien card where the state requires one', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'CO')
            ->assertSee("Colorado requires a notice of intent at least 10 days before the lien statement is filed. Order it first if you haven't.")
            ->assertSee('Start a notice of intent')
            ->assertSee(chooserStart('noi', 'CO'))
            ->assertDontSee('Send it first');
    });

    it('says some claimants where only some need it, and names the state\'s own preliminary notice', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'MO')
            ->assertSeeInOrder([
                "Missouri requires a notice of intent from some claimants at least 10 days before the lien is filed. Order it first if you haven't.",
                "Missouri requires a Notice to Owner (Original Contractor's Disclosure) to keep lien rights. Send it first if you haven't.",
            ]);
    });

    it('names Florida\'s Notice to Owner', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'FL')
            ->assertSee("Florida requires a Notice to Owner from subcontractors and suppliers to keep lien rights. Send it first if you haven't.")
            ->assertSee('Start a Notice to Owner')
            ->assertSee(chooserStart('prelim_notice', 'FL'))
            ->assertDontSee('Order it first');
    });

    it('names none where the state requires neither', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'NY')
            ->assertDontSee('Order it first')
            ->assertDontSee('Send it first');
    });
});

describe('states we do not serve', function () {
    it('points a lien in an attorney state to an attorney and keeps the other documents', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'HI')
            ->assertSee("We don't file mechanics liens in Hawaii. We can connect you with a Hawaii attorney who specializes in construction lien filings.")
            ->assertDontSee(chooserStart('mechanics_lien', 'HI'))
            ->assertSee(chooserStart('prelim_notice', 'HI'))
            ->assertSee(chooserStart('lien_release', 'HI'))
            ->set('state', 'MD')
            ->assertSee("We don't file mechanics liens in Maryland.")
            ->assertDontSee('Maryland requires a notice of intent');
    });

    it('says so for every document in a state without lien rules', function () {
        LienStateRule::query()->whereKey('WY')->delete();

        Livewire::test(LienProductChooser::class)
            ->set('state', 'WY')
            ->assertSee("We don't file preliminary notices in Wyoming.")
            ->assertSee("We don't file mechanics liens in Wyoming.")
            ->assertSee("We don't file lien releases in Wyoming.")
            ->assertDontSee(chooserStart('demand_letter', 'WY'))
            ->assertSee(route('contact'));
    });
});

describe('start', function () {
    it('sends a visitor to sign-up with the product and state kept', function () {
        $this->get(chooserStart('mechanics_lien', 'tx'))->assertRedirect(route('register'));

        expect(LienProductIntent::get())->toBe(['product' => 'mechanics_lien', 'state' => 'TX'])
            ->and(SignupIntent::get())->toMatchArray(['product' => 'liens', 'intent' => 'mechanics-lien', 'state' => 'TX']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Create your account to start your Texas mechanics lien');
    });

    it('turns away anything that is not an orderable product', function () {
        $this->get(route('liens.start', ['product' => 'bond_claim']))->assertNotFound();
        $this->get(route('liens.start'))->assertNotFound();
    });

    it('takes a customer with no project to a new project in the chosen state', function () {
        chooserCustomer($this);

        $this->get(chooserStart('noi', 'CO'))->assertRedirect(route('lien.projects.create'));

        Livewire::test(ProjectForm::class)->assertSet('jobsite_state', 'CO');
    });

    it('takes a customer with one project to it, which offers the product first until it starts', function () {
        $business = chooserCustomer($this);
        $project = chooserProject($business, 'KS');
        $deadline = $project->getDeadlineForType('mechanics_lien');

        $this->get(chooserStart('mechanics_lien', 'KS'))->assertRedirect(route('lien.projects.show', $project));

        Livewire::test(ProjectShow::class, ['project' => $project])
            ->assertSee('You picked:')
            ->assertSee('Start your mechanics lien')
            ->call('startFiling', $deadline->id)
            ->assertRedirect(route('lien.filings.start', ['project' => $project, 'deadline' => $deadline]));

        expect(LienProductIntent::get())->toBeNull();

        Livewire::test(ProjectShow::class, ['project' => $project])->assertDontSee('You picked:');
    });

    it('forgets the choice on Not now', function () {
        $business = chooserCustomer($this);
        $project = chooserProject($business, 'KS');
        LienProductIntent::store(['product' => 'demand_letter', 'state' => 'KS']);

        Livewire::test(ProjectShow::class, ['project' => $project])
            ->assertSee('Payment demand letter')
            ->call('dismissChosenProduct')
            ->assertDontSee('You picked:');

        expect(LienProductIntent::get())->toBeNull();
    });

    it('takes a customer with several projects to the list, which says what they picked', function () {
        $business = chooserCustomer($this);
        LienProject::factory()->forBusiness($business)->count(2)->create();

        $this->get(chooserStart('noi', 'CO'))->assertRedirect(route('lien.projects.index'));

        Livewire::test(ProjectList::class)
            ->assertSee('You picked: Notice of intent to lien. Open the project it is for, or add a new project.');
    });
});

describe('pages', function () {
    it('puts the chooser on the pricing page with Livewire loaded', function () {
        app('livewire')->flushState();

        $html = $this->get('/liens/pricing')
            ->assertOk()
            ->assertSee('What do you need to happen?')
            ->assertSee(chooserStart('mechanics_lien'))
            ->assertSee('Choose a state above to compare self-serve and full service there.')
            ->assertSee('Do your prices include county recording fees?')
            ->assertDontSee('State fees included')
            ->getContent();

        expect($html)->toMatch('/livewire(\.min)?\.js/');

        app('livewire')->flushState();
    });

    it('compares self-serve and full service for the chosen document and state', function () {
        Livewire::test(LienProductChooser::class, ['compare' => true])
            ->assertSee('Choose a state above to compare')
            ->set('state', 'CO')
            ->assertSeeInOrder([
                'Self-serve and full service, side by side in Colorado',
                'Who signs it', 'You print it and sign it in front of a notary.', 'You sign it in front of a notary and upload a scan.',
                'County recording fee', 'You pay the county when you record it.', '$43.00, added at checkout.',
                'Typical turnaround', 'Typically 1 to 5 business days after you submit.', 'Typically 1 to 5 business days after we submit.',
            ])
            ->set('document', 'noi')
            ->assertSee('We mail it by certified mail.')
            ->assertDontSee('$43.00, added at checkout.')
            ->set('document', 'unknown')
            ->assertSet('document', 'mechanics_lien')
            ->set('state', 'HI')
            ->assertSeeInOrder(['Self-serve and full service, side by side in Hawaii', "We don't file mechanics liens in Hawaii."])
            ->assertDontSee('Who signs it');
    });

    it('leaves the comparison off the dashboard', function () {
        Livewire::test(LienProductChooser::class)
            ->set('state', 'CO')
            ->assertDontSee('side by side')
            ->assertDontSee('Who signs it');
    });

    it('shows the chooser on the lien dashboard', function () {
        chooserCustomer($this);

        $this->get(route('lien.dashboard'))
            ->assertOk()
            ->assertSee('What do you need to happen?');
    });
});

describe('wizard first step', function () {
    beforeEach(function () {
        $this->business = chooserCustomer($this);
    });

    it('names the notice of intent and links its step on a mechanics lien', function () {
        $project = chooserProject($this->business, 'CO');
        $noi = $project->getDeadlineForType('noi');
        $sentence = "Colorado requires a notice of intent at least 10 days before the lien statement is filed. Order it first if you haven't.";

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $project->getDeadlineForType('mechanics_lien')])
            ->assertSet('step', 1)
            ->assertSee($sentence)
            ->assertSee('Order the notice of intent')
            ->assertSee(route('lien.filings.start', ['project' => $project, 'deadline' => $noi]))
            ->set('project_type_category', 'residential')
            ->call('nextStep')
            ->assertSet('step', 2)
            ->assertDontSee($sentence);
    });

    it('names Florida\'s Notice to Owner for a subcontractor, not for a contractor hired by the owner', function () {
        $sub = chooserProject($this->business, 'FL', ClaimantType::Subcontractor);

        Livewire::test(FilingWizard::class, ['project' => $sub, 'deadline' => $sub->getDeadlineForType('mechanics_lien')])
            ->assertSee("Florida requires a Notice to Owner to keep lien rights. Send it first if you haven't.")
            ->assertSee('Order the Notice to Owner')
            ->assertSee(route('lien.filings.start', ['project' => $sub, 'deadline' => $sub->getDeadlineForType('prelim_notice')]));

        $gc = chooserProject($this->business, 'FL');

        Livewire::test(FilingWizard::class, ['project' => $gc, 'deadline' => $gc->getDeadlineForType('mechanics_lien')])
            ->assertViewHas('prerequisites', [])
            ->assertDontSee('Send it first');
    });

    it('says nothing on other documents or where the state requires neither', function () {
        $colorado = chooserProject($this->business, 'CO');

        Livewire::test(FilingWizard::class, ['project' => $colorado, 'deadline' => $colorado->getDeadlineForType('noi')])
            ->assertViewHas('prerequisites', [])
            ->assertDontSee('Order it first');

        $newYork = chooserProject($this->business, 'NY');

        Livewire::test(FilingWizard::class, ['project' => $newYork, 'deadline' => $newYork->getDeadlineForType('mechanics_lien')])
            ->assertViewHas('prerequisites', []);
    });

    it('drops a prerequisite the project has already ordered', function () {
        $project = chooserProject($this->business, 'CO');
        $noi = $project->getDeadlineForType('noi');

        LienFiling::factory()->forProject($project)->create([
            'document_type_id' => $noi->document_type_id,
            'project_deadline_id' => $noi->id,
            'status' => FilingStatus::Paid,
            'paid_at' => now(),
            'service_level' => 'full_service',
        ]);

        Livewire::test(FilingWizard::class, ['project' => $project, 'deadline' => $project->getDeadlineForType('mechanics_lien')])
            ->assertViewHas('prerequisites', [])
            ->assertDontSee('Order it first');
    });
});
