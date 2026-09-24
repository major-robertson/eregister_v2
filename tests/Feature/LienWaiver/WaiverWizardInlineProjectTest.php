<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\ClaimantType;
use App\Domains\Lien\Livewire\Waivers\WaiverWizard;
use App\Domains\Lien\Models\LienProject;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * The waiver-first path: a signup from the waiver pages has a business
 * profile but no lien onboarding and no project, and the wizard has to get
 * them to a finished waiver without leaving.
 */
beforeEach(function () {
    Storage::fake('s3');

    $this->user = User::factory()->create(['signup_landing_path' => '/lp/lien-waiver/tx']);
    $this->business = Business::factory()->create([
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => null,
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

it('reaches the waiver pages without lien onboarding, while filing pages still require it', function () {
    $this->get(route('lien.waivers.create'))->assertSuccessful();
    $this->get(route('lien.waivers.index'))->assertSuccessful();
    $this->get(route('lien.projects.index'))->assertRedirect(route('lien.onboarding'));
});

it('opens on the project step with the inline form when the starter chose a direction and there are no projects', function () {
    session(['waiver_intent' => ['state' => 'TX', 'direction' => 'provide', 'kind' => 'conditional_progress', 'source' => '/lp/lien-waiver/tx']]);

    Livewire::test(WaiverWizard::class)
        ->assertSet('step', 2)
        ->assertSet('direction', 'provide')
        ->assertSet('creatingProject', true)
        ->assertSet('project_state', 'TX')
        ->assertSet('intentKind', 'conditional_progress')
        ->assertSee('Where is the job?')
        ->assertSee('Your role on this job')
        // The optional fields sit behind one fold so the screen stays short.
        ->assertSee('More details')
        ->assertDontSee('Create a project first');

    // Consumed: a later wizard visit starts clean.
    expect(session('waiver_intent'))->toBeNull();
});

it('creates the project inline, derives the claimant type, and jumps to details when the type was pre-chosen', function () {
    session(['waiver_intent' => ['state' => 'TX', 'direction' => 'provide', 'kind' => 'conditional_progress', 'source' => null]]);

    $component = Livewire::test(WaiverWizard::class)
        ->call('createProject')
        ->assertHasErrors(['project_address1', 'project_city', 'project_property_class', 'project_role'])
        ->set('project_address1', '500 Congress Ave')
        ->set('project_city', 'Austin')
        ->set('project_zip', '78701')
        ->set('project_property_class', 'commercial')
        ->set('project_role', 'subcontractor')
        ->call('createProject')
        ->assertHasNoErrors()
        ->assertSet('creatingProject', false)
        ->assertSet('kind', 'conditional_progress')
        ->assertSet('intentKind', '')
        ->assertSet('step', 4);

    $project = LienProject::query()->sole();
    expect($project->name)->toBe('500 Congress Ave, Austin');
    expect($project->jobsite_state)->toBe('TX');
    expect($project->jobsite_zip)->toBe('78701');
    expect($project->claimant_type)->toBe(ClaimantType::Subcontractor);
    expect($project->provided_type)->toBe('both');
    expect($project->hired_by)->toBe('direct_contractor');
    expect($project->property_class)->toBe('commercial');
    expect($project->wizard_completed_at)->not->toBeNull();
    expect($project->created_by_user_id)->toBe($this->user->id);
    expect($project->business_id)->toBe($this->business->id);
    expect($component->get('projectId'))->toBe($project->public_id);
});

it('stops at the type step when the starter left the waiver type open', function () {
    session(['waiver_intent' => ['state' => 'CA', 'direction' => 'collect', 'kind' => null, 'source' => null]]);

    Livewire::test(WaiverWizard::class)
        ->set('project_name', 'Market Street Lofts')
        ->set('project_address1', '1 Market St')
        ->set('project_city', 'San Francisco')
        ->set('project_property_class', 'residential')
        ->set('project_role', 'gc')
        ->call('createProject')
        ->assertHasNoErrors()
        ->assertSet('step', 3)
        ->assertSet('kind', '');

    $project = LienProject::query()->sole();
    expect($project->name)->toBe('Market Street Lofts');
    expect($project->claimant_type)->toBe(ClaimantType::Gc);
});

it('fills the jobsite from the address autocomplete', function () {
    Livewire::test(WaiverWizard::class)
        ->call('updateProjectAddressFromAutocomplete', [
            'line1' => '1600 Amphitheatre Parkway',
            'city' => 'Mountain View',
            'state' => 'ca',
            'zip' => '94043',
            'county' => 'Santa Clara County',
        ])
        ->assertSet('project_address1', '1600 Amphitheatre Parkway')
        ->assertSet('project_city', 'Mountain View')
        ->assertSet('project_state', 'CA')
        ->assertSet('project_zip', '94043')
        ->assertSet('project_county', 'Santa Clara County');
});

it('lets a business with projects add another one without leaving the wizard', function () {
    LienProject::factory()->forBusiness($this->business)->inState('FL')->create(['wizard_completed_at' => now()]);

    Livewire::test(WaiverWizard::class)
        ->assertSet('creatingProject', false)
        ->call('selectDirection', 'provide')
        ->call('nextStep')
        ->assertSee('Add a new project')
        ->assertDontSee(route('lien.projects.create'))
        ->call('startNewProject')
        ->assertSet('creatingProject', true)
        ->assertSee('Where is the job?')
        ->call('cancelNewProject')
        ->assertSet('creatingProject', false);
});

it('applies the starter waiver type when an existing project is picked', function () {
    $project = LienProject::factory()->forBusiness($this->business)->inState('TX')->create(['wizard_completed_at' => now()]);
    session(['waiver_intent' => ['state' => 'TX', 'direction' => null, 'kind' => 'unconditional_final', 'source' => null]]);

    Livewire::test(WaiverWizard::class)
        ->assertSet('step', 1)
        ->assertSet('creatingProject', false)
        ->call('selectDirection', 'provide')
        ->call('nextStep')
        ->set('projectId', $project->public_id)
        ->assertSet('kind', 'unconditional_final')
        ->assertSet('intentKind', '');
});

it('ignores the starter intent when a project is deep-linked', function () {
    $project = LienProject::factory()->forBusiness($this->business)->inState('TX')->create(['wizard_completed_at' => now()]);
    session(['waiver_intent' => ['state' => 'CA', 'direction' => 'collect', 'kind' => 'conditional_final', 'source' => null]]);

    Livewire::withQueryParams(['project' => $project->public_id])
        ->test(WaiverWizard::class)
        ->assertSet('projectLocked', true)
        ->assertSet('step', 1)
        ->assertSet('direction', '')
        ->assertSet('kind', '')
        ->assertSet('creatingProject', false);

    expect(session('waiver_intent'))->toBeNull();
});
