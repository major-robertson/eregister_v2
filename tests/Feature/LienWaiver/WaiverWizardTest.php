<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Livewire\Waivers\WaiverWizard;
use App\Domains\Lien\Models\LienContact;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Mail\WaiverSignatureInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

if (! function_exists('waiverWizardProject')) {
    /** A wizard-complete project; the waiver wizard only accepts those. */
    function waiverWizardProject(Business $business, string $state = 'TX'): LienProject
    {
        return LienProject::factory()->forBusiness($business)->inState($state)->create([
            'wizard_completed_at' => now(),
        ]);
    }
}

if (! function_exists('waiverWizardSubscribe')) {
    /** Give the business an active Waiver Pro subscription (stub row, no Stripe). */
    function waiverWizardSubscribe(Business $business): void
    {
        $business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'stub_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'stub_price',
            'quantity' => 1,
        ]);
    }
}

if (! function_exists('waiverWizardAtReview')) {
    /** Drive the wizard through steps 1-4 onto the review step. */
    function waiverWizardAtReview(LienProject $project, string $direction = 'provide', string $kind = 'conditional_progress', array $details = [])
    {
        // The amount is required on the details step; default it so tests
        // exercising other behavior still reach review.
        $details = array_merge(['amount' => '1000.00'], $details);

        $component = Livewire::test(WaiverWizard::class)
            ->call('selectDirection', $direction)
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', $kind)
            ->call('nextStep');

        foreach ($details as $property => $value) {
            $component->set($property, $value);
        }

        return $component->call('nextStep')->assertSet('step', 5);
    }
}

beforeEach(function () {
    // Pin mid-month so calendar-month free-save metering can't straddle a boundary.
    $this->travelTo(now()->startOfMonth()->addDays(14)->setTime(12, 0));

    Storage::fake('s3');

    $this->user = User::factory()->create();
    $this->business = Business::factory()->create([
        'onboarding_completed_at' => now(),
        'lien_onboarding_completed_at' => now(),
    ]);
    $this->business->users()->attach($this->user, ['role' => 'owner']);

    $this->actingAs($this->user);
    session(['current_business_id' => $this->business->id]);
});

describe('step progression', function () {
    it('walks direction -> project -> kind -> details -> review with per-step validation', function () {
        $project = waiverWizardProject($this->business);

        Livewire::test(WaiverWizard::class)
            ->assertSet('step', 1)
            // Step 1 blocks until a direction is chosen.
            ->call('nextStep')
            ->assertHasErrors('direction')
            ->assertSet('step', 1)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->assertSet('step', 2)
            // Step 2 blocks until a project is picked.
            ->call('nextStep')
            ->assertHasErrors('projectId')
            ->assertSet('step', 2)
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->assertSet('step', 3)
            // Step 3 blocks until a kind is picked.
            ->call('nextStep')
            ->assertHasErrors('kind')
            ->assertSet('step', 3)
            ->call('selectKind', 'conditional_progress')
            ->assertSet('kind', 'conditional_progress')
            ->call('nextStep')
            ->assertSet('step', 4)
            // Step 4 blocks until a payment amount is entered.
            ->call('nextStep')
            ->assertHasErrors('amount')
            ->assertSet('step', 4)
            ->set('amount', '2500')
            ->call('nextStep')
            ->assertSet('step', 5)
            // Back navigation works; forward jumps via goToStep are blocked.
            ->call('previousStep')
            ->assertSet('step', 4)
            ->call('goToStep', 2)
            ->assertSet('step', 2)
            ->call('goToStep', 5)
            ->assertSet('step', 2);
    });

    it('ignores an unknown direction value', function () {
        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'sideways')
            ->assertSet('direction', '');
    });

    it('rejects a project id that is not a completed project of this business', function () {
        LienProject::factory()->forBusiness($this->business)->inState('TX')->create([
            'wizard_completed_at' => null,
        ]);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', 'NOTAREALPROJECTID0000000000')
            ->call('nextStep')
            ->assertHasErrors('projectId')
            ->assertSet('step', 2);
    });

    it('requires a signer name and email on the details step for collect waivers', function () {
        $project = waiverWizardProject($this->business);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'collect')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->call('nextStep')
            ->assertHasErrors(['signer_name', 'signer_email'])
            ->assertSet('step', 4);
    });

    it('resets the type selection when the project changes', function () {
        $texas = waiverWizardProject($this->business, 'TX');
        $georgia = waiverWizardProject($this->business, 'GA');

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $texas->public_id)
            ->call('nextStep')
            ->call('selectKind', 'unconditional_progress')
            ->assertSet('kind', 'unconditional_progress')
            ->set('projectId', $georgia->public_id)
            ->assertSet('kind', '')
            ->assertSet('paymentType', '')
            ->assertSet('paymentReceived', '');
    });
});

describe('state rules on the type step', function () {
    it('shows Georgia disabled kinds greyed out with the state reason and refuses to select them', function () {
        $project = waiverWizardProject($this->business, 'GA');

        $component = Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep');

        $kinds = $component->viewData('kinds');
        expect($kinds['conditional_progress']['enabled'])->toBeTrue();
        expect($kinds['conditional_final']['enabled'])->toBeTrue();
        expect($kinds['unconditional_progress']['enabled'])->toBeFalse();
        expect($kinds['unconditional_final']['enabled'])->toBeFalse();

        $component
            // Disabled Georgia kinds stay visible in the always-shown form picker
            // with the state's explanation...
            ->assertSee('Georgia has no unconditional waiver')
            // ...and clicking one is a no-op...
            ->call('selectKind', 'unconditional_progress')
            ->assertSet('kind', '')
            // ...while an enabled kind selects fine.
            ->call('selectKind', 'conditional_progress')
            ->assertSet('kind', 'conditional_progress');
    });

    it('maps the guided answers onto the four canonical kinds', function () {
        $project = waiverWizardProject($this->business, 'TX');

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->set('paymentType', 'progress')
            ->set('paymentReceived', 'no')
            ->assertSet('kind', 'conditional_progress')
            ->set('paymentReceived', 'yes')
            ->assertSet('kind', 'unconditional_progress')
            ->set('paymentType', 'final')
            ->assertSet('kind', 'unconditional_final')
            ->set('paymentReceived', 'no')
            ->assertSet('kind', 'conditional_final')
            ->assertSet('redirectNotice', null);
    });

    it('redirects a guided answer that lands on a disabled kind to the state equivalent', function () {
        $project = waiverWizardProject($this->business, 'GA');

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->set('paymentType', 'progress')
            ->set('paymentReceived', 'yes') // guided answer = unconditional progress, which GA doesn't use
            ->assertSet('kind', 'conditional_progress')
            ->assertSet('redirectNotice', fn (?string $notice) => $notice !== null
                && str_contains($notice, 'Georgia has no unconditional waiver'));
    });

    it('keeps the guided answers in sync when a kind is picked from the grid', function () {
        $project = waiverWizardProject($this->business, 'TX');

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'unconditional_final')
            ->assertSet('kind', 'unconditional_final')
            ->assertSet('paymentType', 'final')
            ->assertSet('paymentReceived', 'yes');
    });
});

describe('details step prefills', function () {
    it('prefills the signer from the selected contact and seeds the check maker with your business on collect waivers', function () {
        $project = waiverWizardProject($this->business, 'TX');
        $contact = LienContact::create([
            'created_by_user_id' => $this->user->id,
            'company_name' => 'Vendor Concrete LLC',
            'contact_name' => 'Vera Vendor',
            'email' => 'vera@vendor.test',
        ]);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'collect')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            // Arriving on details seeds the expected-check maker with the
            // payer — you, on a collect waiver.
            ->assertSet('check_maker', $this->business->name)
            // Picking the contact seeds who signs (still editable).
            ->set('contactId', (string) $contact->id)
            ->assertSet('signer_name', 'Vera Vendor')
            ->assertSet('signer_email', 'vera@vendor.test');
    });

    it('seeds the check maker from the counterparty on provide conditional waivers and leaves the signer alone', function () {
        $project = waiverWizardProject($this->business, 'TX');
        $contact = LienContact::create([
            'created_by_user_id' => $this->user->id,
            'company_name' => 'Big GC Inc',
            'contact_name' => 'Gary GC',
            'email' => 'gary@biggc.test',
        ]);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            // Provide: the payer is the counterparty, unknown until picked.
            ->assertSet('check_maker', null)
            ->set('contactId', (string) $contact->id)
            ->assertSet('check_maker', 'Big GC Inc')
            // You sign your own provide waiver — the contact never becomes the signer.
            ->assertSet('signer_name', null)
            ->assertSet('signer_email', null);
    });

    it('never overwrites a check maker the user already typed', function () {
        $project = waiverWizardProject($this->business, 'TX');
        $contact = LienContact::create([
            'created_by_user_id' => $this->user->id,
            'company_name' => 'Big GC Inc',
        ]);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->set('check_maker', 'Handwritten Payer LLC')
            ->set('contactId', (string) $contact->id)
            ->assertSet('check_maker', 'Handwritten Payer LLC');
    });
});

describe('save', function () {
    it('persists the waiver with the state snapshotted from the project and a generated PDF', function () {
        $project = waiverWizardProject($this->business, 'TX');

        $component = waiverWizardAtReview($project, 'provide', 'conditional_progress', [
            'amount' => '1500.50',
            'through_date' => now()->subDays(10)->format('Y-m-d'),
            'invoice_number' => 'INV-42',
        ])->call('save');

        $waiver = LienWaiver::firstOrFail();

        $component->assertRedirect(route('lien.waivers.show', $waiver));

        expect($waiver->business_id)->toBe($this->business->id);
        expect($waiver->project_id)->toBe($project->id);
        expect($waiver->state)->toBe('TX');
        expect($waiver->status)->toBe(WaiverStatus::Generated);
        expect($waiver->amount_cents)->toBe(150050);
        expect($waiver->invoice_number)->toBe('INV-42');
        expect($waiver->template_key)->toBe('documents.lien.waivers.bodies.tx-conditional-progress');
        expect($waiver->render_snapshot_json)->not->toBeNull();
        expect($waiver->generated_at)->not->toBeNull();
        // Provide direction: the current user signs their own waiver.
        expect($waiver->signer_name)->toBe($this->user->name);
        expect($waiver->signer_email)->toBe($this->user->email);
        // PDF stored on the generated media collection (faked S3).
        expect($waiver->getFirstMedia('generated'))->not->toBeNull();

        // The state is frozen at save; editing the project later can't change it.
        $project->update(['jobsite_state' => 'CA']);
        expect($waiver->refresh()->state)->toBe('TX');
    });

    it('blocks the save over the monthly free limit and pitches the upgrade instead', function () {
        $project = waiverWizardProject($this->business, 'TX');
        LienWaiver::factory()->count(4)->forProject($project)->create();

        waiverWizardAtReview($project)
            ->call('save')
            ->assertSet('showUpsellModal', true)
            ->assertNoRedirect();

        expect(LienWaiver::count())->toBe(4);
    });

    it('still counts voided/trashed saves against the free meter and lifts it for subscribers', function () {
        $project = waiverWizardProject($this->business, 'TX');
        LienWaiver::factory()->count(4)->forProject($project)->create();

        // A deleted waiver already consumed its slot this month.
        LienWaiver::first()->delete();
        expect(WaiverEntitlements::canSaveWaiver($this->business))->toBeFalse();

        waiverWizardSubscribe($this->business);
        expect(WaiverEntitlements::canSaveWaiver($this->business->refresh()))->toBeTrue();
    });
});

describe('save and send for signature', function () {
    it('gates send-for-signature behind the subscription with an upsell', function () {
        $project = waiverWizardProject($this->business, 'TX');

        waiverWizardAtReview($project, 'collect', 'conditional_progress', [
            'signer_name' => 'Vera Vendor',
            'signer_email' => 'vera@vendor.test',
        ])->call('saveAndSend')
            ->assertSet('showUpsellModal', true)
            ->assertNoRedirect();

        // Nothing was saved; the gate fires before persisting.
        expect(LienWaiver::count())->toBe(0);
    });

    it('sends a subscribed collect waiver: generates, opens a guest signature request, and emails the signer', function () {
        Mail::fake();
        waiverWizardSubscribe($this->business);
        $project = waiverWizardProject($this->business, 'TX');

        $component = waiverWizardAtReview($project, 'collect', 'conditional_progress', [
            'signer_name' => 'Vera Vendor',
            'signer_email' => 'vera@vendor.test',
            'signer_title' => 'Owner',
        ])->call('saveAndSend');

        $waiver = LienWaiver::firstOrFail();
        $component->assertRedirect(route('lien.waivers.show', $waiver));

        expect($waiver->status)->toBe(WaiverStatus::AwaitingSignature);
        expect($waiver->sent_at)->not->toBeNull();
        expect($waiver->state)->toBe('TX');
        expect($waiver->getFirstMedia('generated'))->not->toBeNull();

        $request = $waiver->latestSignatureRequest();
        expect($request)->not->toBeNull();
        // Collect direction = guest signer: identity is the invited email, not an account.
        expect($request->signer_user_id)->toBeNull();
        expect($request->signer_email_snapshot)->toBe('vera@vendor.test');

        Mail::assertQueued(WaiverSignatureInvitation::class, fn ($mail) => $mail->hasTo('vera@vendor.test'));
    });

    it('refuses to e-sign in a paper-execution state even when subscribed', function () {
        Mail::fake();
        waiverWizardSubscribe($this->business);
        $project = waiverWizardProject($this->business, 'GA');

        waiverWizardAtReview($project, 'collect', 'conditional_progress', [
            'signer_name' => 'Vera Vendor',
            'signer_email' => 'vera@vendor.test',
        ])->call('saveAndSend')
            ->assertHasErrors('kind')
            ->assertNoRedirect();

        expect(LienWaiver::count())->toBe(0);
        Mail::assertNothingQueued();
    });
});

describe('inline contact creation', function () {
    it('creates a business-scoped contact from the modal and selects it', function () {
        $project = waiverWizardProject($this->business, 'TX');

        $component = Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->call('openContactModal')
            ->assertSet('showContactModal', true)
            // Company is required.
            ->call('saveContact')
            ->assertHasErrors('contact_company')
            ->set('contact_company', 'Vendor Concrete LLC')
            ->set('contact_name', 'Vera Vendor')
            ->set('contact_email', 'vera@vendor.test')
            ->set('contact_state', 'tx')
            ->call('saveContact')
            ->assertHasNoErrors()
            ->assertSet('showContactModal', false);

        $contact = LienContact::firstOrFail();
        expect($contact->business_id)->toBe($this->business->id);
        expect($contact->created_by_user_id)->toBe($this->user->id);
        expect($contact->company_name)->toBe('Vendor Concrete LLC');
        expect($contact->state)->toBe('TX'); // uppercased on save

        $component->assertSet('contactId', (string) $contact->id);
    });
});

describe('project deep link', function () {
    it('preselects the project from the ?project= URL parameter', function () {
        $project = waiverWizardProject($this->business, 'TX');

        Livewire::withQueryParams(['project' => $project->public_id])
            ->test(WaiverWizard::class)
            ->assertSet('projectId', $project->public_id);
    });

    it('drops a ?project= value that is unknown or not wizard-complete', function () {
        Livewire::withQueryParams(['project' => 'NOTAREALPROJECTID0000000000'])
            ->test(WaiverWizard::class)
            ->assertSet('projectId', '');

        $incomplete = LienProject::factory()->forBusiness($this->business)->inState('TX')->create([
            'wizard_completed_at' => null,
        ]);

        Livewire::withQueryParams(['project' => $incomplete->public_id])
            ->test(WaiverWizard::class)
            ->assertSet('projectId', '');
    });

    it('locks and skips the project and type steps when ?project= and ?kind= are both set', function () {
        $project = waiverWizardProject($this->business, 'TX');

        Livewire::withQueryParams(['project' => $project->public_id, 'kind' => 'conditional_progress'])
            ->test(WaiverWizard::class)
            ->assertSet('projectId', $project->public_id)
            ->assertSet('kind', 'conditional_progress')
            ->assertSet('projectLocked', true)
            ->assertSet('kindLocked', true)
            ->assertSet('step', 1)
            ->call('selectDirection', 'provide')
            ->call('nextStep')            // step 1 -> skips project (2) and type (3) -> details (4)
            ->assertSet('step', 4)
            ->call('previousStep')        // details (4) -> back past 3 and 2 -> direction (1)
            ->assertSet('step', 1);
    });

    it('locks and skips only the project step for the guided (?project= only) entry', function () {
        $project = waiverWizardProject($this->business, 'TX');

        Livewire::withQueryParams(['project' => $project->public_id])
            ->test(WaiverWizard::class)
            ->assertSet('projectLocked', true)
            ->assertSet('kindLocked', false)
            ->call('selectDirection', 'provide')
            ->call('nextStep')            // step 1 -> skips project (2) -> type step (3) with guided questions
            ->assertSet('step', 3);
    });

    it('ignores a ?kind= that is unavailable in the project state and falls back to the type step', function () {
        // Georgia has no unconditional waivers, so unconditional_progress can't lock.
        $project = waiverWizardProject($this->business, 'GA');

        Livewire::withQueryParams(['project' => $project->public_id, 'kind' => 'unconditional_progress'])
            ->test(WaiverWizard::class)
            ->assertSet('projectLocked', true)
            ->assertSet('kindLocked', false)
            ->assertSet('kind', '')
            ->call('selectDirection', 'provide')
            ->call('nextStep')            // skips project (locked) but not type -> step 3
            ->assertSet('step', 3);
    });
});
