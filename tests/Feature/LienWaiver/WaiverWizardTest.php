<?php

use App\Domains\Business\Models\Business;
use App\Domains\Lien\Enums\WaiverStatus;
use App\Domains\Lien\Livewire\Waivers\WaiverWizard;
use App\Domains\Lien\Models\LienContact;
use App\Domains\Lien\Models\LienParty;
use App\Domains\Lien\Models\LienProject;
use App\Domains\Lien\Models\LienWaiver;
use App\Domains\Lien\Waivers\WaiverEntitlements;
use App\Mail\WaiverSignatureInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

if (! function_exists('waiverWizardProject')) {
    /**
     * A wizard-complete project with an owner party; the wizard only accepts
     * completed projects and blocks the details step until the project has a
     * property owner (every waiver form identifies the owner).
     */
    function waiverWizardProject(Business $business, string $state = 'TX', bool $withOwner = true): LienProject
    {
        $project = LienProject::factory()->forBusiness($business)->inState($state)->create([
            'wizard_completed_at' => now(),
        ]);

        if ($withOwner) {
            LienParty::factory()->forProject($project)->asOwner()->create([
                'name' => 'Olive Owner',
                'company_name' => 'Owner Holdings LLC',
            ]);
        }

        return $project;
    }
}

if (! function_exists('waiverWizardSubscribe')) {
    /**
     * Give the business an active Waiver Pro subscription (stub row, no
     * Stripe) with seats assigned to the given members.
     */
    function waiverWizardSubscribe(Business $business, User ...$seatHolders): void
    {
        $business->subscriptions()->create([
            'type' => config('lien_waivers.subscription_type'),
            'stripe_id' => 'stub_'.uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'stub_price',
            'quantity' => max(1, count($seatHolders)),
        ]);

        foreach ($seatHolders as $seatHolder) {
            $business->users()->updateExistingPivot($seatHolder->id, ['lien_waiver_seat_at' => now()]);
        }
    }
}

if (! function_exists('waiverWizardCollectContact')) {
    /** A contact with an email — collect waivers are signed by the contact. */
    function waiverWizardCollectContact(User $user): LienContact
    {
        return LienContact::create([
            'created_by_user_id' => $user->id,
            'company_name' => 'Vendor Concrete LLC',
            'first_name' => 'Vera',
            'last_name' => 'Vendor',
            'email' => 'vera@vendor.test',
        ]);
    }
}

if (! function_exists('waiverWizardAtReview')) {
    /** Drive the wizard through steps 1-4 onto the review step. */
    function waiverWizardAtReview(LienProject $project, string $direction = 'provide', string $kind = 'conditional_progress', array $details = [])
    {
        // Amount, through date, and a counterparty contact are required on
        // the details step; default them so tests exercising other behavior
        // still reach review.
        $details = array_merge([
            'amount' => '1000.00',
            'through_date' => now()->format('Y-m-d'),
        ], $details);

        if (! array_key_exists('contactId', $details)) {
            $contact = LienContact::create([
                'created_by_user_id' => auth()->id(),
                'company_name' => 'Counterparty Builders LLC',
                'email' => 'counterparty@builders.test',
            ]);
            $details['contactId'] = (string) $contact->id;
        }

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
            // Step 4 blocks until the amount, through date, and contact are entered.
            ->call('nextStep')
            ->assertHasErrors(['amount', 'through_date', 'contactId'])
            ->assertSet('step', 4)
            ->set('amount', '2500')
            ->set('through_date', now()->format('Y-m-d'))
            ->set('contactId', (string) LienContact::create([
                'created_by_user_id' => $this->user->id,
                'company_name' => 'Stepwise Counterparty LLC',
            ])->id)
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

    it('requires a contact with an email on the details step for collect waivers', function () {
        $project = waiverWizardProject($this->business);
        $noEmail = LienContact::create([
            'created_by_user_id' => $this->user->id,
            'company_name' => 'No Email LLC',
        ]);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'collect')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->set('amount', '1000')
            ->set('through_date', now()->format('Y-m-d'))
            // The contact signs collect waivers, so one must be picked...
            ->call('nextStep')
            ->assertHasErrors('contactId')
            ->assertSet('step', 4)
            // ...and it needs an email for the signature request to go to.
            ->set('contactId', (string) $noEmail->id)
            ->call('nextStep')
            ->assertHasErrors('contactId')
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

describe('auto-save at review', function () {
    it('persists the waiver on reaching review with the state snapshotted and a generated PDF', function () {
        $project = waiverWizardProject($this->business, 'TX');

        $component = waiverWizardAtReview($project, 'provide', 'conditional_progress', [
            'amount' => '1500.50',
            'through_date' => now()->subDays(10)->format('Y-m-d'),
            'invoice_number' => 'INV-42',
        ]);

        // No save button: reaching review is the save.
        $waiver = LienWaiver::firstOrFail();
        $component->assertSet('savedWaiverId', $waiver->id);

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

    it('updates the same draft when review is re-entered after edits, consuming one slot not two', function () {
        $project = waiverWizardProject($this->business, 'TX');

        $component = waiverWizardAtReview($project, 'provide', 'conditional_progress', [
            'amount' => '1000.00',
        ]);

        expect(LienWaiver::count())->toBe(1);
        $waiverId = LienWaiver::firstOrFail()->id;

        // Back to details, change the amount, return to review.
        $component->call('previousStep')
            ->assertSet('step', 4)
            ->set('amount', '2,500.00')
            ->call('nextStep')
            ->assertSet('step', 5)
            ->assertSet('savedWaiverId', $waiverId);

        expect(LienWaiver::count())->toBe(1);
        expect(LienWaiver::firstOrFail()->amount_cents)->toBe(250000);
    });

    it('reformats the amount with thousands separators and still parses it for the save', function () {
        $project = waiverWizardProject($this->business, 'TX');

        waiverWizardAtReview($project, 'provide', 'conditional_progress', [
            'amount' => '60000',
        ])->assertSet('amount', '60,000.00');

        expect(LienWaiver::firstOrFail()->amount_cents)->toBe(6000000);
    });

    it('stops persisting over the monthly free limit and pitches the upgrade at the actions', function () {
        $project = waiverWizardProject($this->business, 'TX');
        LienWaiver::factory()->count(3)->forProject($project)->create();

        $component = waiverWizardAtReview($project)
            ->assertSet('savedWaiverId', null);

        // Review renders, but nothing new was persisted.
        expect(LienWaiver::count())->toBe(3);

        $component->call('downloadPdf')
            ->assertSet('showUpsellModal', true);

        $component->set('showUpsellModal', false)
            ->call('saveAndSend')
            ->assertSet('showUpsellModal', true)
            ->assertNoRedirect();

        expect(LienWaiver::count())->toBe(3);
    });

    it('still counts voided/trashed saves against the free meter and lifts it for subscribers', function () {
        $project = waiverWizardProject($this->business, 'TX');
        LienWaiver::factory()->count(3)->forProject($project)->create();

        // A deleted waiver already consumed its slot this month.
        LienWaiver::first()->delete();
        expect(WaiverEntitlements::canSaveWaiver($this->business, $this->user))->toBeFalse();

        waiverWizardSubscribe($this->business, $this->user);
        expect(WaiverEntitlements::canSaveWaiver($this->business->refresh(), $this->user))->toBeTrue();
    });
});

describe('send for signature', function () {
    it('sends a free-tier collect waiver: e-sign is included in the free allowance', function () {
        Mail::fake();
        $project = waiverWizardProject($this->business, 'TX');

        $contact = waiverWizardCollectContact($this->user);

        $component = waiverWizardAtReview($project, 'collect', 'conditional_progress', [
            'contactId' => (string) $contact->id,
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

    it('refuses to e-sign in a paper-execution state; the draft stays saved for download', function () {
        Mail::fake();
        $project = waiverWizardProject($this->business, 'GA');

        $contact = waiverWizardCollectContact($this->user);

        waiverWizardAtReview($project, 'collect', 'conditional_progress', [
            'contactId' => (string) $contact->id,
        ])->call('saveAndSend')
            ->assertHasErrors('kind')
            ->assertNoRedirect();

        // The auto-saved draft survives; only the send was refused.
        expect(LienWaiver::count())->toBe(1);
        expect(LienWaiver::firstOrFail()->status)->toBe(WaiverStatus::Generated);
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
            // Needs a company or a name — blank on both errors on company.
            ->call('saveContact')
            ->assertHasErrors('contact_company')
            // A first name alone is enough; no company required.
            ->set('contact_first_name', 'Vera')
            ->set('contact_last_name', 'Vendor')
            ->set('contact_company', 'Vendor Concrete LLC')
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

describe('inline contact editing', function () {
    it('edits the selected contact in place to add the missing email a collect waiver needs', function () {
        $project = waiverWizardProject($this->business, 'TX');
        $noEmail = LienContact::create([
            'created_by_user_id' => $this->user->id,
            'company_name' => 'No Email LLC',
        ]);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'collect')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->set('amount', '1000')
            ->set('through_date', now()->format('Y-m-d'))
            ->set('contactId', (string) $noEmail->id)
            // The inline warning points at the fix...
            ->assertSee('This contact has no email address')
            // ...and continuing is still blocked.
            ->call('nextStep')
            ->assertHasErrors('contactId')
            ->assertSet('step', 4)
            // Edit in place: the modal opens prefilled with the contact.
            ->call('editSelectedContact')
            ->assertSet('showContactModal', true)
            ->assertSet('editingContactId', $noEmail->id)
            ->assertSet('contact_company', 'No Email LLC')
            ->set('contact_email', 'fixed@noemail.test')
            ->call('saveContact')
            ->assertHasNoErrors()
            ->assertSet('showContactModal', false)
            // Still the same selected contact — updated, not duplicated.
            ->assertSet('contactId', (string) $noEmail->id)
            ->call('nextStep')
            ->assertSet('step', 5);

        expect($noEmail->fresh()->email)->toBe('fixed@noemail.test');
        expect(LienContact::count())->toBe(1);
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

describe('property owner requirement', function () {
    it('blocks the details step until the project has an owner party', function () {
        $project = waiverWizardProject($this->business, 'TX', withOwner: false);
        $contact = waiverWizardCollectContact($this->user);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->set('amount', '1000')
            ->set('through_date', now()->format('Y-m-d'))
            ->set('contactId', (string) $contact->id)
            ->call('nextStep')
            ->assertHasErrors('owner')
            ->assertSet('step', 4);
    });

    it('adds the owner party from the inline modal and unblocks the step', function () {
        $project = waiverWizardProject($this->business, 'TX', withOwner: false);
        $contact = waiverWizardCollectContact($this->user);

        $component = Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->set('amount', '1000')
            ->set('through_date', now()->format('Y-m-d'))
            ->set('contactId', (string) $contact->id)
            ->call('openOwnerModal')
            // The owner needs at least a name (person or entity, one field).
            ->call('saveOwner')
            ->assertHasErrors('owner_name')
            ->set('owner_name', 'Sunset Development LLC')
            ->set('owner_city', 'Austin')
            ->set('owner_state', 'tx')
            ->call('saveOwner')
            ->assertHasNoErrors('owner_name')
            ->assertSet('showOwnerModal', false);

        $owner = $project->refresh()->ownerParty();
        expect($owner)->not->toBeNull()
            ->and($owner->name)->toBe('Sunset Development LLC')
            ->and($owner->state)->toBe('TX');

        $component->call('nextStep')->assertSet('step', 5);
    });

    it('edits the existing owner party from the wizard, prefilled', function () {
        $project = waiverWizardProject($this->business, 'TX');

        $component = Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $project->public_id)
            ->call('nextStep')
            ->call('selectKind', 'conditional_progress')
            ->call('nextStep')
            ->call('editOwner')
            // Prefilled from the existing party (displayName: company first).
            ->assertSet('showOwnerModal', true)
            ->assertSet('owner_name', 'Owner Holdings LLC')
            ->set('owner_name', 'New Owner LLC')
            ->call('saveOwner')
            ->assertSet('showOwnerModal', false);

        $owner = $project->refresh()->ownerParty();
        expect($project->parties()->where('role', 'owner')->count())->toBe(1)
            ->and($owner->name)->toBe('New Owner LLC')
            ->and($owner->company_name)->toBeNull()
            ->and($owner->displayName())->toBe('New Owner LLC');
    });
});

describe('legal description requirement', function () {
    it('requires a legal description only for the MO residential unconditional final form', function () {
        $residential = waiverWizardProject($this->business, 'MO');
        $residential->update(['property_class' => 'residential', 'legal_description' => null]);
        $contact = waiverWizardCollectContact($this->user);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $residential->public_id)
            ->call('nextStep')
            ->call('selectKind', 'unconditional_final')
            ->call('nextStep')
            ->assertSet('step', 4)
            ->set('amount', '5000')
            ->set('contactId', (string) $contact->id)
            ->call('nextStep')
            ->assertHasErrors('legal_description')
            ->assertSet('step', 4)
            ->set('legal_description', 'Lot 12, Block 3, Sunset Hills Plat Two, Plat Book 44, Page 7')
            ->call('nextStep')
            ->assertSet('step', 5);
    });

    it('does not require a legal description for MO commercial or other statutory states', function () {
        $commercial = waiverWizardProject($this->business, 'MO');
        $commercial->update(['property_class' => 'commercial', 'legal_description' => null]);
        $contact = waiverWizardCollectContact($this->user);

        Livewire::test(WaiverWizard::class)
            ->call('selectDirection', 'provide')
            ->call('nextStep')
            ->set('projectId', $commercial->public_id)
            ->call('nextStep')
            ->call('selectKind', 'unconditional_final')
            ->call('nextStep')
            ->set('amount', '5000')
            ->set('contactId', (string) $contact->id)
            ->call('nextStep')
            ->assertHasNoErrors('legal_description')
            ->assertSet('step', 5);
    });

    it('stores the legal description on the waiver and backfills a project without one', function () {
        $project = waiverWizardProject($this->business, 'MO');
        $project->update(['property_class' => 'residential', 'legal_description' => null]);

        // Reaching review auto-saves; no explicit save call.
        waiverWizardAtReview($project, 'provide', 'unconditional_final', [
            'legal_description' => 'Lot 12, Block 3, Sunset Hills Plat Two',
        ]);

        $waiver = LienWaiver::query()->latest('id')->first();
        expect($waiver->legal_description)->toBe('Lot 12, Block 3, Sunset Hills Plat Two')
            ->and($project->refresh()->legal_description)->toBe('Lot 12, Block 3, Sunset Hills Plat Two');
    });
});

describe('upload signed copy from review', function () {
    it('stores the executed copy on the auto-saved waiver and marks it signed', function () {
        $project = waiverWizardProject($this->business, 'TX');

        $component = waiverWizardAtReview($project, 'provide', 'conditional_progress');

        $waiver = LienWaiver::firstOrFail();

        $component->set('signedFile', Illuminate\Http\UploadedFile::fake()->createWithContent('signed-waiver.pdf', '%PDF-1.4 wet signed'))
            ->call('uploadSigned')
            ->assertRedirect(route('lien.waivers.show', $waiver));

        $waiver->refresh();
        expect($waiver->status)->toBe(WaiverStatus::Signed);
        expect($waiver->signed_at)->not->toBeNull();
        expect($waiver->getFirstMedia('signed'))->not->toBeNull();
    });

    it('is the execution path in a notary state: send is refused, upload succeeds', function () {
        Mail::fake();
        $project = waiverWizardProject($this->business, 'MS');
        $contact = waiverWizardCollectContact($this->user);

        $component = waiverWizardAtReview($project, 'collect', 'conditional_progress', [
            'contactId' => (string) $contact->id,
        ]);

        // Mississippi's statutory forms are sworn before a notary: no e-sign.
        $component->call('saveAndSend')
            ->assertHasErrors('kind')
            ->assertNoRedirect();
        Mail::assertNothingQueued();

        // The paper path works instead.
        $waiver = LienWaiver::firstOrFail();
        $component->set('signedFile', Illuminate\Http\UploadedFile::fake()->createWithContent('notarized.pdf', '%PDF-1.4 notarized'))
            ->call('uploadSigned')
            ->assertRedirect(route('lien.waivers.show', $waiver));

        expect($waiver->refresh()->status)->toBe(WaiverStatus::Signed);
        expect($waiver->getFirstMedia('signed'))->not->toBeNull();
    });

    it('pitches the upgrade instead when the free allowance ran out', function () {
        $project = waiverWizardProject($this->business, 'TX');
        LienWaiver::factory()->count(3)->forProject($project)->create();

        waiverWizardAtReview($project)
            ->call('uploadSigned')
            ->assertSet('showUpsellModal', true)
            ->assertNoRedirect();

        expect(LienWaiver::count())->toBe(3);
    });
});
